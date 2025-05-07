<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateSystemRequest;
use Codedge\Updater\UpdaterManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Codedge\Updater\Models\Release;

class UpdateController extends Controller
{
    protected $updater;
    protected $client;

    public function __construct(UpdaterManager $updater)
    {
        $this->updater = $updater;
        $token = config('self-update.repository_types.github.private_access_token');
        
        $this->client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Pageant-Multi-Tenant-Updater',
                'Authorization' => $token ? "token {$token}" : null
            ]
        ]);
    }

    /**
     * Get the tenant slug from the request
     * 
     * @return string
     */
    protected function getSlug()
    {
        if (request()->route('slug')) {
            return request()->route('slug');
        }
        
        return session('tenant_slug');
    }

    /**
     * Detect the root directory of the application code in the extracted zip
     * 
     * @param string $extractPath The path where the zip was extracted
     * @return string The path to the actual application code root
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
                \Log::info('Detected Laravel app root in subfolder: ' . $potentialRoot);
                return $potentialRoot;
            }
        }
        
        // Check if the root of extract has the application files
        if (file_exists($extractPath . '/artisan') && 
            file_exists($extractPath . '/composer.json')) {
            \Log::info('Detected Laravel app root in extract path: ' . $extractPath);
            return $extractPath;
        }
        
        // Try finding artisan file by recursively searching
        $artisanFiles = $this->findFilesByNameRecursive($extractPath, 'artisan');
        if (count($artisanFiles) > 0) {
            $artisanFile = $artisanFiles[0];
            $possibleRoot = dirname($artisanFile);
            \Log::info('Found artisan file, using directory as app root: ' . $possibleRoot);
            return $possibleRoot;
        }
        
        // If we can't identify a typical structure, default to the extraction root
        \Log::warning('Could not detect app root, using extract path: ' . $extractPath);
        return $extractPath;
    }
    
    /**
     * Find all files with a specific name in a directory recursively
     * 
     * @param string $directory Directory to search in
     * @param string $filename Filename to search for
     * @return array Found file paths
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

    public function index()
    {
        try {
            $isNewVersionAvailable = $this->updater->source()->isNewVersionAvailable();
            $currentVersion = $this->updater->source()->getVersionInstalled();
            $newVersion = null;
            $releases = $this->getReleases();
            
            if ($isNewVersionAvailable) {
                $newVersion = $this->updater->source()->getVersionAvailable();
            }

            return view('tenant.updates.index', compact('isNewVersionAvailable', 'currentVersion', 'newVersion', 'releases'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error checking for updates: ' . $e->getMessage());
        }
    }

    public function check()
    {
        try {
            $isNewVersionAvailable = $this->updater->source()->isNewVersionAvailable();
            $currentVersion = $this->updater->source()->getVersionInstalled();
            $newVersion = null;
            $releases = $this->getReleases();
            
            if ($isNewVersionAvailable) {
                $newVersion = $this->updater->source()->getVersionAvailable();
            }

            return response()->json([
                'hasUpdate' => $isNewVersionAvailable,
                'currentVersion' => $currentVersion,
                'newVersion' => $newVersion,
                'releases' => $releases
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateSystemRequest $request)
    {
        // Prevent timeout for long-running update
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        // Get the slug for routing
        $slug = $this->getSlug();
        
        // Log the update attempt
        \Log::info('Starting system update process', ['version' => $request->input('version'), 'initiated_by' => auth()->guard('tenant')->user()->email]);
        
        // Disable logging for update process to avoid filling logs
        $originalLogLevel = config('app.log_level');
        config(['app.log_level' => 'emergency']);

        try {
            // Get the version from the request
            $targetVersion = $request->input('version');
            
            if (empty($targetVersion)) {
                \Log::error('Update failed: No version specified');
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'No version was specified for the update.');
            }
            
            $currentVersion = $this->updater->source()->getVersionInstalled();
            \Log::info('Current version: ' . $currentVersion . ', Target version: ' . $targetVersion);

            // Validate if the selected version exists in releases
            $releases = $this->getReleases();
            $validVersion = collect($releases)->where('version', $targetVersion)->first();

            if (!$validVersion) {
                \Log::error('Update failed: Invalid version selected', ['requested_version' => $targetVersion]);
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'Selected version is not available.');
            }

            $updatePath = storage_path('app/updater');
            if (!file_exists($updatePath)) {
                \Log::info('Creating update directory: ' . $updatePath);
                if (!mkdir($updatePath, 0755, true)) {
                    \Log::error('Failed to create update directory: ' . $updatePath);
                    return redirect()->route('tenant.updates.index', ['slug' => $slug])
                        ->with('error', 'Failed to create update directory. Please check directory permissions.');
                }
            }
            
            if (!is_writable($updatePath)) {
                \Log::error('Update directory is not writable: ' . $updatePath);
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'Update directory is not writable. Please check directory permissions.');
            }
            
            // Check if there's a release asset zip file 
            $zipUrl = null;
            if (!empty($validVersion['assets'])) {
                foreach ($validVersion['assets'] as $asset) {
                    if (preg_match('/\.zip$/', $asset['name'])) {
                        $zipUrl = $asset['download_url'];
                        \Log::info('Found release asset ZIP: ' . $asset['name']);
                        break;
                    }
                }
            }
            
            // If no ZIP asset found, use GitHub source code ZIP
            if (!$zipUrl) {
                // Get the download URL for the release source code
                $vendor = config('self-update.repository_types.github.repository_vendor');
                $repo = config('self-update.repository_types.github.repository_name');
                $response = $this->client->get("repos/{$vendor}/{$repo}/releases/tags/v{$targetVersion}");
                $releaseData = json_decode($response->getBody(), true);

                if (!isset($releaseData['zipball_url'])) {
                    \Log::error('Could not find download URL for version: ' . $targetVersion);
                    return redirect()->route('tenant.updates.index', ['slug' => $slug])
                        ->with('error', 'Could not find download URL for the selected version.');
                }

                $zipUrl = $releaseData['zipball_url'];
            }
            
            $zipFile = $updatePath . DIRECTORY_SEPARATOR . "release-v{$targetVersion}.zip";

            // Download the zip file
            try {
                $zipResponse = $this->client->get($zipUrl, ['sink' => $zipFile]);
                if (!file_exists($zipFile)) {
                    \Log::error('Failed to download release zip file.');
                    return redirect()->route('tenant.updates.index', ['slug' => $slug])
                        ->with('error', 'Failed to download release zip file.');
                }
            } catch (\Exception $e) {
                \Log::error('Error downloading release zip: ' . $e->getMessage());
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'Error downloading release zip: ' . $e->getMessage());
            }

            // Extract the zip file
            $extractPath = $updatePath . DIRECTORY_SEPARATOR . "extracted-v{$targetVersion}";
            if (!file_exists($extractPath)) {
                if (!mkdir($extractPath, 0755, true)) {
                    \Log::error('Failed to create extract directory: ' . $extractPath);
                    return redirect()->route('tenant.updates.index', ['slug' => $slug])
                        ->with('error', 'Failed to create extract directory. Please check directory permissions.');
                }
            }
            
            try {
                $zip = new \ZipArchive();
                $zipOpenResult = $zip->open($zipFile);
                
                if ($zipOpenResult === TRUE) {
                    if (!$zip->extractTo($extractPath)) {
                        throw new \Exception("ZipArchive failed to extract files");
                    }
                    $zip->close();
                } else {
                    throw new \Exception("Failed to open zip file. Error code: " . $zipOpenResult);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to extract release zip: ' . $e->getMessage());
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'Failed to extract release zip: ' . $e->getMessage());
            }

            // Detect the actual code subfolder inside the extracted directory
            $actualSource = $this->detectSourceCodeRoot($extractPath);

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
            if ($zipBackup->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($rootPath, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
                
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
                            } catch (\Exception $e) {
                                \Log::warning("Failed to add file to backup: {$filePath} - Error: " . $e->getMessage());
                            }
                        }
                    }
                }
                
                $zipBackup->close();
            } else {
                \Log::error('Failed to create backup zip.');
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'Failed to create backup zip.');
            }

            // Copy extracted files to app root (excluding critical folders/files)
            $this->copyUpdateFiles($actualSource, $rootPath, $exclude);

            // Clean up extracted folder
            $this->deleteDirectory($extractPath);
            
            // Update SELF_UPDATER_VERSION_INSTALLED in .env
            $this->updateEnvVersion($targetVersion);

            // Make sure artisan is executable
            $artisanPath = base_path('artisan');
            if (file_exists($artisanPath) && !is_executable($artisanPath)) {
                \Log::info('Making artisan executable');
                chmod($artisanPath, 0755);
            }

            // Clear caches
            \Artisan::call('config:clear');
            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');

            // Run composer install --no-dev
            $composerOutput = null;
            $composerReturn = null;
            exec('composer install --no-dev 2>&1', $composerOutput, $composerReturn);
            
            if ($composerReturn !== 0) {
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'Update failed during composer install. Check logs for details.');
            }

            // Run php artisan migrate
            try {
                \Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                \Log::error('php artisan migrate failed: ' . $e->getMessage());
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'Update failed during migration. Check logs for details.');
            }

            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            // Redirect to updates page after success
            return redirect()->route('tenant.updates.index', ['slug' => $slug])
                ->with('success', 'System updated to version ' . $targetVersion . ' successfully! Composer and migrations have been run.');
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Update failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Try running composer and migrations anyway to ensure the application is in a usable state
            try {
                \Log::info('Attempting to run composer install after error');
                exec('composer install --no-dev 2>&1', $composerOutput, $composerReturn);
                
                if ($composerReturn !== 0) {
                    \Log::error('Composer install failed after error');
                } else {
                    \Log::info('Composer install succeeded after error');
                }
                
                \Log::info('Attempting to run migrations after error');
                \Artisan::call('migrate', ['--force' => true]);
                \Log::info('Migrations run after error');
            } catch (\Exception $innerEx) {
                \Log::error('Failed to run composer/migrations after update error: ' . $innerEx->getMessage());
            }
            
            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // Recursively copy files from source to destination, skipping excluded folders/files
    protected function copyUpdateFiles($source, $destination, $exclude = [])
    {
        \Log::info("Starting copy from $source to $destination");
        
        if (!is_dir($source)) {
            \Log::error("Source directory does not exist: $source");
            return;
        }
        
        $dir = opendir($source);
        if (!$dir) {
            \Log::error("Failed to open directory: $source");
            return;
        }
        
        if (!file_exists($destination)) {
            \Log::info("Creating destination directory: $destination");
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
                    \Log::warning("Skipping invalid path for file: $file");
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
                    \Log::debug("Skipping excluded path: $relativePath");
                    $skipCount++;
                    continue;
                }
                
                if (is_dir($srcPath)) {
                    $dirCount++;
                    // Create directory if it doesn't exist
                    if (!file_exists($destPath)) {
                        \Log::debug("Creating directory: $destPath");
                        mkdir($destPath, 0755, true);
                    }
                    
                    // Recursively copy directory contents 
                    $this->copyUpdateFiles($srcPath, $destPath, $exclude);
                } else {
                    $fileCount++;
                    // Make sure destination directory exists
                    $destDir = dirname($destPath);
                    if (!file_exists($destDir)) {
                        \Log::debug("Creating parent directory: $destDir");
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
                            \Log::warning("Failed to copy file: $srcPath to $destPath");
                            $errorCount++;
                        } else {
                            // Set appropriate permissions for the copied file
                        if (file_exists($destPath)) {
                                if (pathinfo($destPath, PATHINFO_BASENAME) === 'artisan') {
                                    \Log::info("Making artisan executable: $destPath");
                                    @chmod($destPath, 0755);
                                } else {
                                    @chmod($destPath, 0644);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Exception copying file: $srcPath to $destPath - Error: " . $e->getMessage());
                        $errorCount++;
                    }
                }
            }
        }
        
        closedir($dir);
        \Log::info("Completed copying files from $source to $destination", [
            'files_copied' => $fileCount,
            'directories_created' => $dirCount,
            'files_skipped' => $skipCount,
            'errors' => $errorCount
        ]);
    }

    protected function getReleases()
    {
        try {
            $vendor = config('self-update.repository_types.github.repository_vendor');
            $repo = config('self-update.repository_types.github.repository_name');
            $token = config('self-update.repository_types.github.private_access_token');

            if (empty($token)) {
                \Log::warning('GitHub access token is not configured. Please set SELF_UPDATER_GITHUB_PRIVATE_ACCESS_TOKEN in your .env file.');
                return [];
            }

            $response = $this->client->get("repos/{$vendor}/{$repo}/releases");
            $releases = json_decode($response->getBody(), true);

            if (empty($releases)) {
                \Log::info('No releases found for the repository.');
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
            \Log::error('Error fetching releases: ' . $e->getMessage());
            return [];
        }
    }

    // Update the SELF_UPDATER_VERSION_INSTALLED value in the .env file
    protected function updateEnvVersion($newVersion)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            \Log::error('.env file not found at: ' . $envPath);
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
        return true;
    }

    // Delete a directory and its contents
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
    }
} 