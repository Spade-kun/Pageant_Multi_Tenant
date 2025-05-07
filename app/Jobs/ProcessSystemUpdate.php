<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use Codedge\Updater\UpdaterManager;
use GuzzleHttp\Client;

class ProcessSystemUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The timeout for the job in seconds.
     *
     * @var int
     */
    public $timeout = 1800; // 30 minutes
    
    /**
     * The number of times to attempt the job.
     *
     * @var int
     */
    public $tries = 1;

    protected $targetVersion;
    protected $slug;
    protected $jobId;
    protected $userEmail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($targetVersion, $slug, $jobId, $userEmail)
    {
        $this->targetVersion = $targetVersion;
        $this->slug = $slug;
        $this->jobId = $jobId;
        $this->userEmail = $userEmail;
        
        // Initialize the job status in cache
        $this->updateStatus('in_progress', 'Starting update process...', 1, 7);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Set PHP configuration
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        // Disable most logging during update
        $originalLogLevel = config('app.log_level');
        config(['app.log_level' => 'warning']);
        
        try {
            // Setup updater
            $token = config('self-update.repository_types.github.private_access_token');
            $client = new Client([
                'base_uri' => 'https://api.github.com/',
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Pageant-Multi-Tenant-Updater',
                    'Authorization' => $token ? "token {$token}" : null
                ]
            ]);
            
            // Set up tenant database connection
            $this->setTenantConnection($this->slug);
            
            // Check current version
            $updater = app(UpdaterManager::class);
            $currentVersion = $updater->source()->getVersionInstalled();
            
            Log::info('Starting system update', [
                'from_version' => $currentVersion,
                'to_version' => $this->targetVersion,
                'tenant' => $this->slug,
                'initiated_by' => $this->userEmail,
                'job_id' => $this->jobId
            ]);
            
            // Step 1: Download update package
            $this->updateStatus('in_progress', 'Downloading update package...', 1, 7);
            
            // Validate if the selected version exists in releases
            $releases = $this->getReleases($client);
            $validVersion = collect($releases)->where('version', $this->targetVersion)->first();

            if (!$validVersion) {
                throw new \Exception('Selected version is not available: ' . $this->targetVersion);
            }
            
            $updatePath = storage_path('app/updater');
            if (!file_exists($updatePath)) {
                if (!mkdir($updatePath, 0755, true)) {
                    throw new \Exception('Failed to create update directory: ' . $updatePath);
                }
            }
            
            // Check if there's a release asset zip file 
            $zipUrl = null;
            if (!empty($validVersion['assets'])) {
                foreach ($validVersion['assets'] as $asset) {
                    if (preg_match('/\.zip$/', $asset['name'])) {
                        $zipUrl = $asset['download_url'];
                        Log::info('Found release asset ZIP: ' . $asset['name']);
                        break;
                    }
                }
            }
            
            // If no ZIP asset found, use GitHub source code ZIP
            if (!$zipUrl) {
                $vendor = config('self-update.repository_types.github.repository_vendor');
                $repo = config('self-update.repository_types.github.repository_name');
                $response = $client->get("repos/{$vendor}/{$repo}/releases/tags/v{$this->targetVersion}");
                $releaseData = json_decode($response->getBody(), true);

                if (!isset($releaseData['zipball_url'])) {
                    throw new \Exception('Could not find download URL for version: ' . $this->targetVersion);
                }

                $zipUrl = $releaseData['zipball_url'];
            }
            
            $zipFile = $updatePath . DIRECTORY_SEPARATOR . "release-v{$this->targetVersion}.zip";
            
            // Download the zip file
            $client->get($zipUrl, ['sink' => $zipFile]);
            if (!file_exists($zipFile)) {
                throw new \Exception('Failed to download release zip file.');
            }
            
            Log::info('Downloaded update package', ['zip_file' => $zipFile, 'size' => filesize($zipFile)]);
            
            // Step 2: Extract files
            $this->updateStatus('in_progress', 'Extracting files...', 2, 7);
            
            $extractPath = $updatePath . DIRECTORY_SEPARATOR . "extracted-v{$this->targetVersion}";
            if (!file_exists($extractPath)) {
                if (!mkdir($extractPath, 0755, true)) {
                    throw new \Exception('Failed to create extract directory: ' . $extractPath);
                }
            }
            
            $zip = new \ZipArchive();
            $zipOpenResult = $zip->open($zipFile);
            
            if ($zipOpenResult !== TRUE) {
                throw new \Exception("Failed to open zip file. Error code: " . $zipOpenResult);
            }
            
            if (!$zip->extractTo($extractPath)) {
                throw new \Exception("ZipArchive failed to extract files");
            }
            
            $zip->close();
            
            Log::info('Extracted files', ['extract_path' => $extractPath]);
            
            // Detect the actual code subfolder inside the extracted directory
            $actualSource = $this->detectSourceCodeRoot($extractPath);
            Log::info('Detected source code root', ['path' => $actualSource]);
            
            // Step 3: Create backup
            $this->updateStatus('in_progress', 'Creating backup of current application...', 3, 7);
            
            // Define which files and folders to exclude from updates and backups
            $exclude = [
                '.env',
                'storage',
                'vendor',
                '.git',
                'node_modules',
                'public/uploads',
                'public/storage',
                '*.log',  // Exclude all log files
                'storage/logs',  // Exclude logs directory
                'bootstrap/cache',  // Exclude cache directory
                'admin-server.log',  // Specific log file
                'laravel.log',  // Laravel log file
                '.env.backup',
                '.DS_Store',
                'phpunit.xml'
            ];
            
            // Backup current app (excluding critical folders/files)
            $rootPath = base_path();
            $backupFile = $updatePath . DIRECTORY_SEPARATOR . "backup-v{$currentVersion}.zip";
            
            $zipBackup = new \ZipArchive();
            if ($zipBackup->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception('Failed to create backup zip.');
            }
            
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            $backupFileCount = 0;
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                if (empty($filePath)) {
                    continue; // Skip files with empty paths
                }
                
                $relativePath = ltrim(str_replace($rootPath, '', $filePath), DIRECTORY_SEPARATOR);
                if (empty($relativePath)) {
                    continue; // Skip empty relative paths
                }
                
                $skip = false;
                foreach ($exclude as $ex) {
                    // Handle wildcard patterns
                    if (strpos($ex, '*') !== false) {
                        $pattern = str_replace('*', '.*', $ex);
                        if (preg_match('/' . $pattern . '/', $relativePath)) {
                            $skip = true;
                            break;
                        }
                    } else if (stripos($relativePath, $ex) === 0) {
                        $skip = true;
                        break;
                    }
                }
                
                if (!$skip) {
                    if ($file->isDir()) {
                        $zipBackup->addEmptyDir($relativePath);
                    } else {
                        try {
                            $zipBackup->addFile($filePath, $relativePath);
                            $backupFileCount++;
                        } catch (\Exception $e) {
                            Log::warning("Failed to add file to backup: {$filePath} - Error: " . $e->getMessage());
                        }
                    }
                }
            }
            
            $zipBackup->close();
            Log::info('Created backup', ['backup_file' => $backupFile, 'files_backed_up' => $backupFileCount]);
            
            // Step 4: Copy new files
            $this->updateStatus('in_progress', 'Copying new files to application...', 4, 7);
            $this->copyUpdateFiles($actualSource, $rootPath, $exclude);
            
            // Clean up extracted folder
            $this->deleteDirectory($extractPath);
            
            // Update SELF_UPDATER_VERSION_INSTALLED in .env
            $this->updateEnvVersion($this->targetVersion);
            
            // Make sure artisan is executable
            $artisanPath = base_path('artisan');
            if (file_exists($artisanPath) && !is_executable($artisanPath)) {
                chmod($artisanPath, 0755);
                Log::info('Made artisan executable');
            }
            
            // Step 5: Run composer install
            $this->updateStatus('in_progress', 'Running composer install...', 5, 7);
            
            $composerOutput = [];
            $composerReturn = 0;
            exec('composer install --no-dev 2>&1', $composerOutput, $composerReturn);
            
            if ($composerReturn !== 0) {
                Log::error('Composer install failed', ['output' => implode("\n", $composerOutput)]);
                throw new \Exception('Failed to run composer install. Error code: ' . $composerReturn);
            }
            
            Log::info('Composer install completed successfully');
            
            // Step 6: Run migrations
            $this->updateStatus('in_progress', 'Running database migrations...', 6, 7);
            
            $migrationOutput = '';
            try {
                $migrationOutput = \Artisan::call('migrate', ['--force' => true]);
                Log::info('Database migrations completed successfully', ['output' => $migrationOutput]);
            } catch (\Exception $e) {
                Log::error('Database migrations failed', ['error' => $e->getMessage()]);
                throw new \Exception('Failed to run database migrations: ' . $e->getMessage());
            }
            
            // Step 7: Finalize update
            $this->updateStatus('in_progress', 'Finalizing update...', 7, 7);
            
            // Clear caches
            \Artisan::call('config:clear');
            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');
            
            Log::info('System update completed successfully', [
                'from_version' => $currentVersion,
                'to_version' => $this->targetVersion
            ]);
            
            // Mark the job as completed
            $this->updateStatus('completed', 'Update completed successfully!', 7, 7, true);
            
        } catch (\Exception $e) {
            Log::error('System update failed', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            // Try to recover
            try {
                Log::info('Attempting recovery after failed update');
                
                // Try to run composer install
                exec('composer install --no-dev 2>&1');
                
                // Try to run migrations
                \Artisan::call('migrate', ['--force' => true]);
                
                // Clear caches
                \Artisan::call('config:clear');
                \Artisan::call('cache:clear');
                \Artisan::call('view:clear');
                \Artisan::call('route:clear');
            } catch (\Exception $recoveryEx) {
                Log::error('Recovery attempt failed', ['error' => $recoveryEx->getMessage()]);
            }
            
            // Mark the job as failed
            $this->updateStatus('failed', 'Update failed: ' . $e->getMessage(), 0, 7, false);
            
            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            throw $e;
        }
        
        // Restore original log level
        config(['app.log_level' => $originalLogLevel]);
    }
    
    /**
     * Update the job status in the cache
     */
    protected function updateStatus($status, $message, $currentStep, $totalSteps, $success = null)
    {
        $cacheKey = 'system-update-' . $this->jobId;
        $cacheData = [
            'status' => $status,
            'message' => $message,
            'current_step' => $currentStep,
            'total_steps' => $totalSteps,
            'updated_at' => now()->toDateTimeString()
        ];
        
        if ($success !== null) {
            $cacheData['success'] = $success;
        }
        
        // Store status in cache for 2 hours
        Cache::put($cacheKey, $cacheData, 120 * 60);
        
        // Log the status update
        Log::info('Update status changed', $cacheData);
    }
    
    /**
     * Set up the tenant database connection
     */
    protected function setTenantConnection($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
        
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }
    
    /**
     * Detect the root directory of the application code in the extracted zip
     */
    protected function detectSourceCodeRoot($extractPath)
    {
        // First check for common GitHub source code repository pattern
        // (single subfolder with all content)
        $subfolders = array_filter(glob($extractPath . '/*'), 'is_dir');
        
        if (count($subfolders) === 1) {
            // Check if this folder has typical application files
            $potentialRoot = $subfolders[0];
            if (file_exists($potentialRoot . '/artisan') && 
                file_exists($potentialRoot . '/composer.json')) {
                Log::info('Detected Laravel app root in subfolder: ' . $potentialRoot);
                return $potentialRoot;
            }
        }
        
        // Check if the root of extract has the application files
        if (file_exists($extractPath . '/artisan') && 
            file_exists($extractPath . '/composer.json')) {
            Log::info('Detected Laravel app root in extract path: ' . $extractPath);
            return $extractPath;
        }
        
        // Try finding artisan file by recursively searching
        $artisanFiles = $this->findFilesByNameRecursive($extractPath, 'artisan');
        if (count($artisanFiles) > 0) {
            $artisanFile = $artisanFiles[0];
            $possibleRoot = dirname($artisanFile);
            Log::info('Found artisan file, using directory as app root: ' . $possibleRoot);
            return $possibleRoot;
        }
        
        // If we can't identify a typical structure, default to the extraction root
        Log::warning('Could not detect app root, using extract path: ' . $extractPath);
        return $extractPath;
    }
    
    /**
     * Find all files with a specific name in a directory recursively
     */
    protected function findFilesByNameRecursive($directory, $filename)
    {
        $result = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getBasename() === $filename) {
                $result[] = $file->getRealPath();
            }
        }
        
        return $result;
    }
    
    /**
     * Recursively copy files from source to destination, skipping excluded folders/files
     */
    protected function copyUpdateFiles($source, $destination, $exclude = [])
    {
        Log::info("Starting copy from $source to $destination");
        
        if (!is_dir($source)) {
            Log::error("Source directory does not exist: $source");
            return;
        }
        
        $dir = opendir($source);
        if (!$dir) {
            Log::error("Failed to open directory: $source");
            return;
        }
        
        if (!file_exists($destination)) {
            Log::info("Creating destination directory: $destination");
            mkdir($destination, 0755, true);
        }
        
        $fileCount = 0;
        $dirCount = 0;
        $skipCount = 0;
        $errorCount = 0;
        
        while(false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $srcPath = $source . DIRECTORY_SEPARATOR . $file;
                $destPath = $destination . DIRECTORY_SEPARATOR . $file;
                
                // Skip invalid paths
                if (empty($srcPath) || empty($destPath)) {
                    Log::warning("Skipping invalid path for file: $file");
                    $skipCount++;
                    continue;
                }
                
                // Check if this path should be excluded
                $relativePath = ltrim(str_replace($destination, '', $destPath), DIRECTORY_SEPARATOR);
                if (empty($relativePath)) {
                    $skipCount++;
                    continue;
                }
                
                $skip = false;
                foreach ($exclude as $ex) {
                    // Handle wildcard patterns
                    if (strpos($ex, '*') !== false) {
                        $pattern = str_replace('*', '.*', $ex);
                        if (preg_match('/' . $pattern . '/', $file) || preg_match('/' . $pattern . '/', $relativePath)) {
                            $skip = true;
                            break;
                        }
                    } else if (stripos($relativePath, $ex) === 0 || stripos($file, $ex) === 0) {
                        $skip = true;
                        break;
                    }
                }
                
                if ($skip) {
                    Log::debug("Skipping excluded path: $relativePath");
                    $skipCount++;
                    continue;
                }
                
                if (is_dir($srcPath)) {
                    $dirCount++;
                    // Create directory if it doesn't exist
                    if (!file_exists($destPath)) {
                        Log::debug("Creating directory: $destPath");
                        mkdir($destPath, 0755, true);
                    }
                    
                    // Recursively copy directory contents 
                    $this->copyUpdateFiles($srcPath, $destPath, $exclude);
                } else {
                    $fileCount++;
                    // Make sure destination directory exists
                    $destDir = dirname($destPath);
                    if (!file_exists($destDir)) {
                        Log::debug("Creating parent directory: $destDir");
                        mkdir($destDir, 0755, true);
                    }
                    
                    // Always force overwrite the file - this is critical for updates
                    // First delete existing file if exists
                    if (file_exists($destPath)) {
                        @unlink($destPath);
                    }
                    
                    // Then copy the new file
                    try {
                        if (!@copy($srcPath, $destPath)) {
                            Log::warning("Failed to copy file: $srcPath to $destPath");
                            $errorCount++;
                        } else {
                            // Set appropriate permissions for the copied file
                            if (file_exists($destPath)) {
                                if (pathinfo($destPath, PATHINFO_BASENAME) === 'artisan') {
                                    Log::info("Making artisan executable: $destPath");
                                    @chmod($destPath, 0755);
                                } else {
                                    @chmod($destPath, 0644);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Exception copying file: $srcPath to $destPath - Error: " . $e->getMessage());
                        $errorCount++;
                    }
                }
            }
        }
        
        closedir($dir);
        Log::info("Completed copying files from $source to $destination", [
            'files_copied' => $fileCount,
            'directories_created' => $dirCount,
            'files_skipped' => $skipCount,
            'errors' => $errorCount
        ]);
    }
    
    /**
     * Update the SELF_UPDATER_VERSION_INSTALLED value in the .env file
     */
    protected function updateEnvVersion($newVersion)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            Log::error('.env file not found at: ' . $envPath);
            return false;
        }

        $envContent = file_get_contents($envPath);
        $pattern = '/^SELF_UPDATER_VERSION_INSTALLED=.*$/m';
        
        if (preg_match($pattern, $envContent)) {
            // Update existing value
            $envContent = preg_replace(
                $pattern,
                'SELF_UPDATER_VERSION_INSTALLED=' . $newVersion,
                $envContent
            );
        } else {
            // Add value if it doesn't exist
            $envContent .= PHP_EOL . 'SELF_UPDATER_VERSION_INSTALLED=' . $newVersion . PHP_EOL;
        }
        
        file_put_contents($envPath, $envContent);
        Log::info('Updated environment version to: ' . $newVersion);
        return true;
    }
    
    /**
     * Delete a directory and its contents
     */
    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
        
        @rmdir($dir);
        Log::info('Deleted directory: ' . $dir);
    }
    
    /**
     * Get all available releases
     */
    protected function getReleases($client)
    {
        try {
            $vendor = config('self-update.repository_types.github.repository_vendor');
            $repo = config('self-update.repository_types.github.repository_name');

            $response = $client->get("repos/{$vendor}/{$repo}/releases");
            $releases = json_decode($response->getBody(), true);

            if (empty($releases)) {
                Log::info('No releases found for the repository.');
                return [];
            }

            return collect($releases)->map(function ($release) {
                return [
                    'version' => ltrim($release['tag_name'], 'v'),
                    'released_at' => date('Y-m-d H:i:s', strtotime($release['published_at'])),
                    'description' => $release['body'],
                    'author' => $release['author']['login'],
                    'assets' => collect($release['assets'])->map(function ($asset) {
                        return [
                            'name' => $asset['name'],
                            'size' => $asset['size'],
                            'download_url' => $asset['browser_download_url']
                        ];
                    })->toArray()
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching releases: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('System update job failed', [
            'job_id' => $this->jobId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        $this->updateStatus('failed', 'Update failed: ' . $exception->getMessage(), 0, 7, false);
    }
} 