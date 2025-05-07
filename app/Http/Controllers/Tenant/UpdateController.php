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

    /**
     * Handle the system update request
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($request)
    {
        // Prevent timeout for long-running update
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        // Enable detailed logging for debugging
        $debugMode = true;
        
        // Disable logging for update process to avoid filling logs
        $originalLogLevel = config('app.log_level');
        if (!$debugMode) {
            config(['app.log_level' => 'emergency']);
        }
        
        // Log the start of the update process
        $this->logDebug('Starting update process', $debugMode);

        try {
            // Get version from request
            $targetVersion = $request->input('version');
            
            if (empty($targetVersion)) {
                return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                    ->with('error', 'No version was specified for the update.');
            }
            
            $currentVersion = $this->updater->source()->getVersionInstalled();
            $this->logDebug("Updating from version {$currentVersion} to {$targetVersion}", $debugMode);

            // Validate if the selected version exists in releases
            $releases = $this->getReleases();
            $validVersion = collect($releases)->where('version', $targetVersion)->first();

            if (!$validVersion) {
                return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                    ->with('error', 'Selected version is not available.');
            }

            $updatePath = storage_path('app/updater');
            if (!file_exists($updatePath)) {
                if (!mkdir($updatePath, 0755, true)) {
                    $this->logDebug("Failed to create update directory: {$updatePath}", $debugMode, 'error');
                    return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                        ->with('error', 'Failed to create update directory. Please check directory permissions.');
                }
            }
            
            if (!is_writable($updatePath)) {
                \Log::error('Update directory is not writable: ' . $updatePath);
                return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                    ->with('error', 'Update directory is not writable. Please check directory permissions.');
            }

            // Get the download URL for the release
            $vendor = config('self-update.repository_types.github.repository_vendor');
            $repo = config('self-update.repository_types.github.repository_name');
            $response = $this->client->get("repos/{$vendor}/{$repo}/releases/tags/v{$targetVersion}");
            $releaseData = json_decode($response->getBody(), true);

            if (!isset($releaseData['zipball_url'])) {
                \Log::error('Could not find download URL for version: ' . $targetVersion);
                return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                    ->with('error', 'Could not find download URL for the selected version.');
            }

            $zipUrl = $releaseData['zipball_url'];
            $zipFile = $updatePath . DIRECTORY_SEPARATOR . "release-v{$targetVersion}.zip";

            // Check if there's a release asset zip file
            $assetZipUrl = null;
            if (!empty($validVersion['assets'])) {
                foreach ($validVersion['assets'] as $asset) {
                    if (preg_match('/\.zip$/', $asset['name'])) {
                        $assetZipUrl = $asset['download_url'];
                        $this->logDebug("Found asset zip file: {$asset['name']}", $debugMode);
                        break;
                    }
                }
            }

            // Download the zip file
            try {
                $this->logDebug("Downloading zip from: {$zipUrl}", $debugMode);
                $zipResponse = $this->client->get($zipUrl, ['sink' => $zipFile]);
                if (!file_exists($zipFile)) {
                    $this->logDebug("Failed to download zip file to: {$zipFile}", $debugMode, 'error');
                    return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                        ->with('error', 'Failed to download release zip file.');
                } else {
                    $this->logDebug("Successfully downloaded zip file: {$zipFile} (size: " . filesize($zipFile) . " bytes)", $debugMode);
                }
            } catch (\Exception $e) {
                $this->logDebug("Error downloading zip: " . $e->getMessage(), $debugMode, 'error');
                return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                    ->with('error', 'Error downloading release zip: ' . $e->getMessage());
            }

            // Extract the zip file
            $extractPath = $updatePath . DIRECTORY_SEPARATOR . "extracted-v{$targetVersion}";
            if (!file_exists($extractPath)) {
                mkdir($extractPath, 0755, true);
            }
            
            $zip = new \ZipArchive();
            $zipResult = $zip->open($zipFile);
            $this->logDebug("Zip open result: " . ($zipResult === TRUE ? 'SUCCESS' : 'FAILED (code: ' . $zipResult . ')'), $debugMode);
            
            if ($zipResult === TRUE) {
                $this->logDebug("Extracting zip to: {$extractPath}", $debugMode);
                $zip->extractTo($extractPath);
                $zip->close();
                $this->logDebug("Extraction completed. Number of files: " . count(glob($extractPath . '/*')), $debugMode);
            } else {
                $this->logDebug("Failed to extract zip file", $debugMode, 'error');
                return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                    ->with('error', 'Failed to extract release zip.');
            }

            // Detect the actual code directory from the extracted zip
            $this->logDebug("Detecting source code root", $debugMode);
            $actualSource = $this->detectSourceCodeRoot($extractPath);
            $this->logDebug("Source code root detected: {$actualSource}", $debugMode);

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
                return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                    ->with('error', 'Failed to create backup zip.');
            }

            // Copy extracted files to app root (excluding critical folders/files)
            $this->logDebug("Copying files from {$actualSource} to {$rootPath}", $debugMode);
            $this->copyUpdateFiles($actualSource, $rootPath, $exclude);
            $this->logDebug("File copying completed", $debugMode);

            // Clean up extracted folder
            $this->deleteDirectory($extractPath);
            
            // Update SELF_UPDATER_VERSION_INSTALLED in .env
            $this->updateEnvVersion($targetVersion);

            // Clear caches
            try {
                \Artisan::call('config:clear');
                \Artisan::call('cache:clear');
                \Artisan::call('view:clear');
                \Artisan::call('route:clear');
                \Log::info('Cleared application caches successfully.');
            } catch (\Exception $e) {
                \Log::warning('Error clearing caches: ' . $e->getMessage());
            }

            // Run composer install --no-dev
            $composerOutput = null;
            $composerReturn = null;
            
            try {
                exec('composer install --no-dev 2>&1', $composerOutput, $composerReturn);
                if ($composerReturn !== 0) {
                    \Log::warning('Composer install returned non-zero exit code: ' . $composerReturn);
                    \Log::warning('Composer output: ' . implode("\n", $composerOutput));
                } else {
                    \Log::info('Composer install completed successfully.');
                }
            } catch (\Exception $e) {
                \Log::warning('Error running composer: ' . $e->getMessage());
            }
            
            // Run php artisan migrate
            try {
                \Artisan::call('migrate', ['--force' => true]);
                \Log::info('Database migrations completed successfully.');
            } catch (\Exception $e) {
                \Log::warning('Migration error: ' . $e->getMessage());
            }

            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            // Redirect to updates page after success
            return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                ->with('success', 'System updated to version ' . $targetVersion . ' successfully! Composer and migrations have been run.');
        } catch (\Exception $e) {
            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            \Log::error('Update failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // Recursively copy files from source to destination, skipping excluded folders/files
    protected function copyUpdateFiles($source, $destination, $exclude = [])
    {
        $debugMode = true;
        
        if (!is_dir($source)) {
            $this->logDebug("Source is not a directory: {$source}", $debugMode, 'warning');
            return;
        }
        
        $dir = opendir($source);
        if (!file_exists($destination)) {
            $this->logDebug("Creating destination directory: {$destination}", $debugMode);
            @mkdir($destination, 0755, true);
        }
        
        $copyCount = 0;
        $skipCount = 0;
        $errorCount = 0;
        
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
                    $this->logDebug("Skipping excluded path: {$relativePath}", $debugMode);
                    $skipCount++;
                    continue;
                }
                
                if (is_dir($srcPath)) {
                    // Create directory if it doesn't exist
                    if (!file_exists($destPath)) {
                        $this->logDebug("Creating directory: {$relativePath}", $debugMode);
                        @mkdir($destPath, 0755, true);
                    }
                    
                    // Recursively copy directory contents 
                    $this->copyUpdateFiles($srcPath, $destPath, $exclude);
                } else {
                    // Make sure destination directory exists
                    $destDir = dirname($destPath);
                    if (!file_exists($destDir)) {
                        $this->logDebug("Creating parent directory: {$destDir}", $debugMode);
                        @mkdir($destDir, 0755, true);
                    }
                    
                    // Force overwrite files - important for updates!
                    // First try to remove the existing file if it exists
                    if (file_exists($destPath)) {
                        $this->logDebug("Removing existing file: {$relativePath}", $debugMode);
                        @unlink($destPath);
                    }
                    
                    // Now copy the new file
                    $this->logDebug("Copying file: {$relativePath}", $debugMode);
                    if (@copy($srcPath, $destPath)) {
                        if (file_exists($destPath)) {
                            @chmod($destPath, 0644);
                            $this->logDebug("Successfully updated file: {$relativePath}", $debugMode);
                            $copyCount++;
                        }
                    } else {
                        $this->logDebug("Failed to update file: {$relativePath}", $debugMode, 'warning');
                        $errorCount++;
                    }
                }
            }
        }
        
        closedir($dir);
        $this->logDebug("Copy operation completed. {$copyCount} files copied, {$skipCount} files skipped, {$errorCount} errors", $debugMode);
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
     * Log debug information if debug mode is enabled
     *
     * @param string $message The message to log
     * @param bool $debugMode Whether debug mode is enabled
     * @param string $level The log level (info, error, warning)
     * @return void
     */
    protected function logDebug($message, $debugMode = false, $level = 'info')
    {
        if ($debugMode) {
            switch ($level) {
                case 'error':
                    \Log::error('[UPDATE SYSTEM] ' . $message);
                    break;
                case 'warning':
                    \Log::warning('[UPDATE SYSTEM] ' . $message);
                    break;
                default:
                    \Log::info('[UPDATE SYSTEM] ' . $message);
                    break;
            }
        }
    }

    /**
     * Detect the root directory of the application code in the extracted zip
     * 
     * @param string $extractPath The path where the zip was extracted
     * @return string The path to the actual application code root
     */
    protected function detectSourceCodeRoot($extractPath)
    {
        // Log the contents of the extract path for debugging
        $debugMode = true;
        $this->logDebug("Extract path contents: " . print_r(glob($extractPath . '/*'), true), $debugMode);
        
        // First check for common GitHub source code repository pattern
        // (single subfolder with all content)
        $subfolders = array_filter(glob($extractPath . '/*'), 'is_dir');
        $this->logDebug("Found " . count($subfolders) . " subdirectories", $debugMode);
        
        if (count($subfolders) === 1) {
            // Check if this folder has typical application files
            $potentialRoot = $subfolders[0];
            $this->logDebug("Checking single subfolder: " . $potentialRoot, $debugMode);
            
            if (file_exists($potentialRoot . '/artisan') || 
                file_exists($potentialRoot . '/composer.json') || 
                file_exists($potentialRoot . '/package.json')) {
                $this->logDebug("Found Laravel app files in subfolder - using as root", $debugMode);
                return $potentialRoot;
            }
        }
        
        // Check if the root of extract has the application files
        $this->logDebug("Checking extract root for app files", $debugMode);
        if (file_exists($extractPath . '/artisan') || 
            file_exists($extractPath . '/composer.json') || 
            file_exists($extractPath . '/package.json')) {
            $this->logDebug("Found Laravel app files in extract root - using as root", $debugMode);
            return $extractPath;
        }
        
        // Check for folders that might contain the application 
        // (common in manually packaged zips)
        foreach ($subfolders as $subfolder) {
            $this->logDebug("Examining subfolder: " . basename($subfolder), $debugMode);
            
            if (basename($subfolder) === 'app' || basename($subfolder) === 'src') {
                // Look at the parent of this folder
                $this->logDebug("Found 'app' or 'src' folder directly in extract - using extract root", $debugMode);
                return $extractPath;
            }
            
            // Check one level deeper
            $subsubfolders = array_filter(glob($subfolder . '/*'), 'is_dir');
            foreach ($subsubfolders as $subsubfolder) {
                if (basename($subsubfolder) === 'app' || basename($subsubfolder) === 'src') {
                    $this->logDebug("Found 'app' or 'src' folder in " . basename($subfolder) . " - using as root", $debugMode);
                    return $subfolder;
                }
            }
        }
        
        // If we can't identify a typical structure, default to the extraction root
        $this->logDebug("No standard structure detected - defaulting to extract root", $debugMode);
        return $extractPath;
    }
} 