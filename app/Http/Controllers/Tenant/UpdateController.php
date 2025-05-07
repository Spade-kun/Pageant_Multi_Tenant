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
        
        // Disable logging for update process to avoid filling logs
        $originalLogLevel = config('app.log_level');
        config(['app.log_level' => 'emergency']);

        try {
            $validated = $request->validated();
            $targetVersion = $validated['version'];
            $currentVersion = $this->updater->source()->getVersionInstalled();

            // Validate if the selected version exists in releases
            $releases = $this->getReleases();
            $validVersion = collect($releases)->where('version', $targetVersion)->first();

            // Get redirect URL from the request, or use the default route
            $redirectUrl = $request->input('redirect_url') ?? route('tenant.updates.index', ['slug' => session('tenant_slug')]);

            if (!$validVersion) {
                return redirect()->to($redirectUrl)
                    ->with('error', 'Selected version is not available.');
            }

            $updatePath = storage_path('app/updater');
            if (!file_exists($updatePath)) {
                if (!mkdir($updatePath, 0755, true)) {
                    \Log::error('Failed to create update directory: ' . $updatePath);
                    return redirect()->to($redirectUrl)
                        ->with('error', 'Failed to create update directory. Please check directory permissions.');
                }
            }
            
            if (!is_writable($updatePath)) {
                \Log::error('Update directory is not writable: ' . $updatePath);
                return redirect()->to($redirectUrl)
                    ->with('error', 'Update directory is not writable. Please check directory permissions.');
            }

            // Get the download URL for the release
            $vendor = config('self-update.repository_types.github.repository_vendor');
            $repo = config('self-update.repository_types.github.repository_name');
            $response = $this->client->get("repos/{$vendor}/{$repo}/releases/tags/v{$targetVersion}");
            $releaseData = json_decode($response->getBody(), true);

            if (!isset($releaseData['zipball_url'])) {
                \Log::error('Could not find download URL for version: ' . $targetVersion);
                return redirect()->to($redirectUrl)
                    ->with('error', 'Could not find download URL for the selected version.');
            }

            $zipUrl = $releaseData['zipball_url'];
            $zipFile = $updatePath . DIRECTORY_SEPARATOR . "release-v{$targetVersion}.zip";

            // Download the zip file
            try {
                $zipResponse = $this->client->get($zipUrl, ['sink' => $zipFile]);
                if (!file_exists($zipFile)) {
                    \Log::error('Failed to download release zip file.');
                    return redirect()->to($redirectUrl)
                        ->with('error', 'Failed to download release zip file.');
                }
            } catch (\Exception $e) {
                \Log::error('Error downloading release zip: ' . $e->getMessage());
                return redirect()->to($redirectUrl)
                    ->with('error', 'Error downloading release zip: ' . $e->getMessage());
            }

            // Extract the zip file
            $extractPath = $updatePath . DIRECTORY_SEPARATOR . "extracted-v{$targetVersion}";
            if (!file_exists($extractPath)) {
                mkdir($extractPath, 0755, true);
            }
            
            $zip = new \ZipArchive();
            if ($zip->open($zipFile) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
            } else {
                \Log::error('Failed to extract release zip.');
                return redirect()->to($redirectUrl)
                    ->with('error', 'Failed to extract release zip.');
            }

            // Detect the actual code subfolder inside the extracted directory
            $subfolders = array_filter(glob($extractPath . '/*'), 'is_dir');
            if (count($subfolders) === 1) {
                $actualSource = $subfolders[0];
            } else {
                $actualSource = $extractPath;
            }

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
                    $relativePath = ltrim(str_replace($rootPath, '', $filePath), DIRECTORY_SEPARATOR);
                    
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
                            $zipBackup->addFile($filePath, $relativePath);
                        }
                    }
                }
                
                $zipBackup->close();
            } else {
                \Log::error('Failed to create backup zip.');
                return redirect()->to($redirectUrl)
                    ->with('error', 'Failed to create backup zip.');
            }

            // Copy extracted files to app root (excluding critical folders/files)
            $this->copyUpdateFiles($actualSource, $rootPath, $exclude);

            // Clean up extracted folder
            $this->deleteDirectory($extractPath);
            
            // Update SELF_UPDATER_VERSION_INSTALLED in .env
            $this->updateEnvVersion($targetVersion);

            // Clear caches
            \Artisan::call('config:clear');
            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');

            // Run composer install --no-dev
            $composerOutput = null;
            $composerReturn = null;
            exec('composer install --no-dev 2>&1', $composerOutput, $composerReturn);
            
            if ($composerReturn !== 0) {
                return redirect()->to($redirectUrl)
                    ->with('error', 'Update failed during composer install. Check logs for details.');
            }

            // Run php artisan migrate
            try {
                \Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                \Log::error('php artisan migrate failed: ' . $e->getMessage());
                return redirect()->to($redirectUrl)
                    ->with('error', 'Update failed during migration. Check logs for details.');
            }

            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            // Redirect to the specified URL after success
            return redirect()->to($redirectUrl)
                ->with('success', 'System updated to version ' . $targetVersion . ' successfully! Composer and migrations have been run.');
        } catch (\Exception $e) {
            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            \Log::error('Update failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Get redirect URL from the request, or use the default route
            $redirectUrl = $request->input('redirect_url') ?? route('tenant.updates.index', ['slug' => session('tenant_slug')]);
            
            return redirect()->to($redirectUrl)
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // Recursively copy files from source to destination, skipping excluded folders/files
    protected function copyUpdateFiles($source, $destination, $exclude = [])
    {
        if (!is_dir($source)) {
            \Log::info("Source directory not found: {$source}");
            return;
        }
        
        $dir = opendir($source);
        if (!file_exists($destination)) {
            @mkdir($destination, 0755, true);
            \Log::info("Created destination directory: {$destination}");
        }
        
        while(false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $srcPath = $source . DIRECTORY_SEPARATOR . $file;
                $destPath = $destination . DIRECTORY_SEPARATOR . $file;
                
                // Check if this path should be excluded
                $relativePath = ltrim(str_replace($destination, '', $destPath), DIRECTORY_SEPARATOR);
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
                    \Log::info("Skipping excluded file: {$destPath}");
                    continue;
                }
                
                if (is_dir($srcPath)) {
                    // Create directory if it doesn't exist
                    if (!file_exists($destPath)) {
                        mkdir($destPath, 0755, true);
                        \Log::info("Created directory: {$destPath}");
                    }
                    
                    // Recursively copy directory contents 
                    $this->copyUpdateFiles($srcPath, $destPath, $exclude);
                } else {
                    // Make sure destination directory exists
                    $destDir = dirname($destPath);
                    if (!file_exists($destDir)) {
                        mkdir($destDir, 0755, true);
                        \Log::info("Created parent directory: {$destDir}");
                    }
                    
                    // If destination file exists and is read-only or in use, try to make it writable
                    if (file_exists($destPath)) {
                        @chmod($destPath, 0644);
                        
                        // If file can't be written to, attempt to delete it first
                        if (!is_writable($destPath)) {
                            @unlink($destPath);
                            \Log::info("Removed write-protected file: {$destPath}");
                        }
                    }
                    
                    // ALWAYS copy files, regardless of timestamp
                    if (@copy($srcPath, $destPath)) {
                        @chmod($destPath, 0644);
                        \Log::info("Copied file: {$srcPath} → {$destPath}");
                    } else {
                        \Log::warning("Failed to copy file: {$srcPath} → {$destPath}");
                    }
                }
            }
        }
        
        closedir($dir);
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

    /**
     * Clean up stale project files that might interfere with updates
     */
    protected function cleanupProjectFiles()
    {
        \Log::info("Starting pre-update cleanup...");
        
        // Directories that should be cleaned up
        $directoriesToClean = [
            base_path('bootstrap/cache'),
            storage_path('framework/cache'),
            storage_path('framework/views'),
        ];
        
        foreach ($directoriesToClean as $directory) {
            if (is_dir($directory)) {
                $this->cleanDirectory($directory);
                \Log::info("Cleaned directory: {$directory}");
            }
        }
        
        // Specific files to remove
        $filesToRemove = [
            base_path('bootstrap/cache/config.php'),
            base_path('bootstrap/cache/routes.php'),
            base_path('bootstrap/cache/services.php'),
        ];
        
        foreach ($filesToRemove as $file) {
            if (file_exists($file)) {
                @unlink($file);
                \Log::info("Removed file: {$file}");
            }
        }
        
        \Log::info("Pre-update cleanup completed");
    }

    /**
     * Clean a directory without removing the directory itself
     */
    protected function cleanDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }
        
        $files = new \FilesystemIterator($directory);
        
        foreach ($files as $file) {
            if ($file->isDir() && !$file->isLink()) {
                $this->deleteDirectory($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }
    }
} 