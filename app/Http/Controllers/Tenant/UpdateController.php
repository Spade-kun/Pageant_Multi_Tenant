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
                'tenant-server.log',  // Specific log file
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
                            if ($filePath && file_exists($filePath)) {
                                $zipBackup->addFile($filePath, $relativePath);
                            }
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
                $this->logDebug("Clearing application caches", $debugMode);
                \Artisan::call('config:clear');
                \Artisan::call('cache:clear');
                \Artisan::call('view:clear');
                \Artisan::call('route:clear');
                $this->logDebug("Application caches cleared successfully", $debugMode);
            } catch (\Exception $e) {
                $this->logDebug("Error clearing caches: " . $e->getMessage(), $debugMode, 'warning');
            }

            // Run composer install --no-dev
            $composerOutput = null;
            $composerReturn = null;
            
            try {
                $this->logDebug("Running composer install", $debugMode);
                exec('composer install --no-dev 2>&1', $composerOutput, $composerReturn);
                if ($composerReturn !== 0) {
                    $this->logDebug("Composer install returned non-zero exit code: " . $composerReturn, $debugMode, 'warning');
                    $this->logDebug("Composer output: " . implode("\n", $composerOutput), $debugMode, 'warning');
                } else {
                    $this->logDebug("Composer install completed successfully", $debugMode);
                }
            } catch (\Exception $e) {
                $this->logDebug("Error running composer: " . $e->getMessage(), $debugMode, 'warning');
            }
            
            // Run php artisan migrate for central database
            try {
                $this->logDebug("Running migrations for central database", $debugMode);
                $migrateOutput = \Artisan::call('migrate', ['--force' => true]);
                $this->logDebug("Central database migrations completed with exit code: {$migrateOutput}", $debugMode);
            } catch (\Exception $e) {
                $this->logDebug("Central database migration error: " . $e->getMessage(), $debugMode, 'error');
            }
            
            // Run migrations for all tenant databases
            try {
                $this->logDebug("Running migrations for all tenant databases", $debugMode);
                $tenantMigrateOutput = \Artisan::call('migrate:all-tenants', ['--force' => true]);
                $this->logDebug("Tenant migrations completed with exit code: {$tenantMigrateOutput}", $debugMode);
                
                if ($tenantMigrateOutput !== 0) {
                    $this->logDebug("Some tenant migrations failed. Check the log for details.", $debugMode, 'warning');
                }
            } catch (\Exception $e) {
                $this->logDebug("Tenant migrations error: " . $e->getMessage(), $debugMode, 'error');
            }

            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            // Store the update details in the session for the success page
            session()->flash('update_success', true);
            session()->flash('update_version', $targetVersion);
            session()->flash('migration_status', 'Central database and tenant databases have been migrated.');
            
            // Get the slug for direct redirect
            $slug = $this->getSlug();
            
            // Use a direct URL to bypass the update route
            $successUrl = url('/' . $slug . '/updates/success');
            return redirect($successUrl);
        } catch (\Exception $e) {
            // Restore original log level
            config(['app.log_level' => $originalLogLevel]);
            
            \Log::error('Update failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // Recursively copy files from source to destination, skipping excluded folders/files
    protected function copyUpdateFiles($source, $destination, $exclude = [])
    {
        $this->logDebug("Copying files from {$source} to {$destination}", true);
        
        if (!is_dir($source)) {
            $this->logDebug("Source directory does not exist: {$source}", true, 'error');
            return false;
        }

        if (!is_dir($destination)) {
            if (!mkdir($destination, 0755, true)) {
                $this->logDebug("Failed to create destination directory: {$destination}", true, 'error');
                return false;
            }
        }
        
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..' && !in_array($file, $exclude)) {
                $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
                $destPath = $destination . DIRECTORY_SEPARATOR . $file;
                
                // Skip log files
                if ($this->isLogFile($sourcePath)) {
                    $this->logDebug("Skipping log file: {$file}", true);
                    continue;
                }
                
                if (is_dir($sourcePath)) {
                    $this->copyUpdateFiles($sourcePath, $destPath, $exclude);
                } else {
                    // Special handling for artisan file
                    if ($file === 'artisan') {
                        $this->logDebug("Copying artisan file", true);
                        if (!copy($sourcePath, $destPath)) {
                            $this->logDebug("Failed to copy artisan file", true, 'error');
                            return false;
                        }
                        // Make artisan file executable
                        chmod($destPath, 0755);
                        $this->logDebug("Made artisan file executable", true);
                    }
                    // Special handling for run-servers.php
                    else if ($file === 'run-servers.php') {
                        $this->logDebug("Copying run-servers.php", true);
                        if (!copy($sourcePath, $destPath)) {
                            $this->logDebug("Failed to copy run-servers.php", true, 'error');
                            return false;
                        }
                        // Make run-servers.php executable
                        chmod($destPath, 0755);
                        $this->logDebug("Made run-servers.php executable", true);
                    }
                    // Regular file copy
                    else {
                        if (!copy($sourcePath, $destPath)) {
                            $this->logDebug("Failed to copy file: {$file}", true, 'error');
                            return false;
                        }
                    }
                }
            }
        }
        closedir($dir);
        return true;
    }
    
    /**
     * Check if a file is a log file that should be skipped during copy
     *
     * @param string $filePath The path to the file to check
     * @return bool Whether the file is a log file
     */
    protected function isLogFile($filePath)
    {
        // Check file extension
        if (preg_match('/\.log$/i', $filePath)) {
            return true;
        }
        
        // Check for common log file names
        $logFileNames = [
            'laravel.log',
            'admin-server.log',
            'tenant-server.log',
            'php_errors.log',
            'error.log',
            'access.log',
            'debug.log'
        ];
        
        $fileName = basename($filePath);
        if (in_array($fileName, $logFileNames)) {
            return true;
        }
        
        // Check if file is in a logs directory
        if (strpos($filePath, DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR) !== false) {
            return true;
        }
        
        // Check if file is in storage/logs
        if (strpos($filePath, DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs') !== false) {
            return true;
        }
        
        return false;
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

    /**
     * Display the update success page
     * 
     * @return \Illuminate\View\View
     */
    public function success()
    {
        try {
            // If we have update information in the session, use it
            if (session()->has('update_success')) {
                $version = session('update_version');
                $migrationStatus = session('migration_status');
            } else {
                // Otherwise, just show a generic success message
                // Get the current version from the updater
                $version = $this->updater->source()->getVersionInstalled();
                $migrationStatus = 'No detailed migration information available.';
            }
            
            return view('tenant.updates.success', [
                'version' => $version,
                'migrationStatus' => $migrationStatus,
                'slug' => $this->getSlug()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error displaying update success page: ' . $e->getMessage());
            return redirect()->route('tenant.updates.index', ['slug' => $this->getSlug()])
                ->with('error', 'Error displaying success page: ' . $e->getMessage());
        }
    }
} 