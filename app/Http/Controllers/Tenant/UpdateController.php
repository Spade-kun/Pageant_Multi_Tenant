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
            if (file_exists($potentialRoot . '/artisan') || 
                file_exists($potentialRoot . '/composer.json') || 
                file_exists($potentialRoot . '/package.json')) {
                return $potentialRoot;
            }
        }
        
        // Check if the root of extract has the application files
        if (file_exists($extractPath . '/artisan') || 
            file_exists($extractPath . '/composer.json') || 
            file_exists($extractPath . '/package.json')) {
            return $extractPath;
        }
        
        // If we can't identify a typical structure, default to the extraction root
        return $extractPath;
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

    public function update(Request $request)
    {
        // Prevent timeout for long-running update
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        // Disable logging for update process to avoid filling logs
        $originalLogLevel = config('app.log_level');
        config(['app.log_level' => 'emergency']);

        try {
            // Get the version from the request
            $targetVersion = $request->input('version');
            $slug = $this->getSlug();
            
            if (empty($targetVersion)) {
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'No version was specified for the update.');
            }
            
            $currentVersion = $this->updater->source()->getVersionInstalled();

            // Validate if the selected version exists in releases
            $releases = $this->getReleases();
            $validVersion = collect($releases)->where('version', $targetVersion)->first();

            if (!$validVersion) {
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'Selected version is not available.');
            }

            $updatePath = storage_path('app/updater');
            if (!file_exists($updatePath)) {
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
            
            // Store success message in session and redirect to the success page
            session()->flash('update_success', 'System updated to version ' . $targetVersion . ' successfully! Composer and migrations have been run.');
            
            // Redirect to success page after completion
            return redirect()->route('tenant.updates.update', ['slug' => $slug]);
        } catch (\Exception $e) {
            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            \Log::error('Update failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('tenant.updates.index', ['slug' => $slug])
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // Recursively copy files from source to destination, skipping excluded folders/files
    protected function copyUpdateFiles($source, $destination, $exclude = [])
    {
        if (!is_dir($source)) {
            return;
        }
        
        $dir = opendir($source);
        if (!$dir) {
            \Log::warning("Failed to open directory: {$source}");
            return;
        }
        
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }
        
        while(false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $srcPath = $source . DIRECTORY_SEPARATOR . $file;
                $destPath = $destination . DIRECTORY_SEPARATOR . $file;
                
                // Skip invalid paths
                if (empty($srcPath) || empty($destPath)) {
                    continue;
                }
                
                // Check if this path should be excluded
                $relativePath = ltrim(str_replace($destination, '', $destPath), DIRECTORY_SEPARATOR);
                if (empty($relativePath)) {
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
                    continue;
                }
                
                if (is_dir($srcPath)) {
                    // Create directory if it doesn't exist
                    if (!file_exists($destPath)) {
                        mkdir($destPath, 0755, true);
                    }
                    
                    // Recursively copy directory contents 
                    $this->copyUpdateFiles($srcPath, $destPath, $exclude);
                } else {
                    // Make sure destination directory exists
                    $destDir = dirname($destPath);
                    if (!file_exists($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    
                    // Always force overwrite the file - this is critical for updates
                    // First delete existing file if exists
                    if (file_exists($destPath)) {
                        @unlink($destPath);
                    }
                    
                    // Then copy the new file
                    try {
                        @copy($srcPath, $destPath);
                        
                        if (file_exists($destPath)) {
                            chmod($destPath, 0644);
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Failed to copy file: {$srcPath} to {$destPath} - Error: " . $e->getMessage());
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
} 