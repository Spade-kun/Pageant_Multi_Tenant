<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
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

    public function update(Request $request)
    {
        set_time_limit(0); // Prevent timeout for long-running update
        try {
            $targetVersion = $request->input('version');
            $currentVersion = $this->updater->source()->getVersionInstalled();

            if (empty($targetVersion)) {
                return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Please select a version to update to.');
            }

            // Validate if the selected version exists in releases
            $releases = $this->getReleases();
            $validVersion = collect($releases)->where('version', $targetVersion)->first();

            if (!$validVersion) {
                return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Selected version is not available.');
            }

            $updatePath = storage_path('app/updater');
            if (!file_exists($updatePath)) {
                if (!mkdir($updatePath, 0755, true)) {
                    \Log::error('Failed to create update directory: ' . $updatePath);
                    return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Failed to create update directory. Please check directory permissions.');
                }
            }
            if (!is_writable($updatePath)) {
                \Log::error('Update directory is not writable: ' . $updatePath);
                return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Update directory is not writable. Please check directory permissions.');
            }

            // Get the download URL for the release
            $vendor = config('self-update.repository_types.github.repository_vendor');
            $repo = config('self-update.repository_types.github.repository_name');
            $response = $this->client->get("repos/{$vendor}/{$repo}/releases/tags/v{$targetVersion}");
            $releaseData = json_decode($response->getBody(), true);

            if (!isset($releaseData['zipball_url'])) {
                \Log::error('Could not find download URL for version: ' . $targetVersion);
                return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Could not find download URL for the selected version.');
            }

            $zipUrl = $releaseData['zipball_url'];
            $zipFile = $updatePath . DIRECTORY_SEPARATOR . "release-v{$targetVersion}.zip";

            // Download the zip file
            try {
                $zipResponse = $this->client->get($zipUrl, ['sink' => $zipFile]);
                if (!file_exists($zipFile)) {
                    \Log::error('Failed to download release zip file.');
                    return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Failed to download release zip file.');
                }
                \Log::info('Downloaded release zip to: ' . $zipFile);
            } catch (\Exception $e) {
                \Log::error('Error downloading release zip: ' . $e->getMessage());
                return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Error downloading release zip: ' . $e->getMessage());
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
                \Log::info('Extracted release zip to: ' . $extractPath);
            } else {
                \Log::error('Failed to extract release zip.');
                return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Failed to extract release zip.');
            }

            // Detect the actual code subfolder inside the extracted directory
            $subfolders = array_filter(glob($extractPath . '/*'), 'is_dir');
            if (count($subfolders) === 1) {
                $actualSource = $subfolders[0];
                \Log::info('Detected code subfolder: ' . $actualSource);
            } else {
                $actualSource = $extractPath;
                \Log::warning('Could not uniquely detect code subfolder, using extract path directly.');
            }

            // Backup current app (excluding critical folders/files)
            $rootPath = base_path();
            $backupFile = $updatePath . DIRECTORY_SEPARATOR . "backup-v{$currentVersion}.zip";
            $exclude = [
                '.env', 
                'storage', 
                'vendor', 
                '.git', 
                'node_modules', 
                'public/uploads', 
                'public/storage', 
                '*.log', 
                'storage/logs', 
                'admin-server.log', 
                'tenant-server.log', 
                'laravel.log',
                '.log',  // Adding generic .log extension
                'logs',  // Any logs directory
                'server.log', // Any server log
                'debug.log',  // Any debug log
                'error.log',  // Any error log
                'app.log'     // Any app log
            ];
            $zipBackup = new \ZipArchive();
            if ($zipBackup->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($rootPath, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($files as $file) {
                    $filePath = $file->getRealPath();
                    $relativePath = ltrim(str_replace($rootPath, '', $filePath), DIRECTORY_SEPARATOR);
                    
                    if ($this->shouldSkipFile($filePath, $relativePath, $exclude)) {
                        continue;
                    }
                    
                    if ($file->isDir()) {
                        $zipBackup->addEmptyDir($relativePath);
                    } else {
                        $zipBackup->addFile($filePath, $relativePath);
                    }
                }
                $zipBackup->close();
                \Log::info('Backup created at: ' . $backupFile);
            } else {
                \Log::error('Failed to create backup zip.');
                return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Failed to create backup zip.');
            }

            // Copy extracted files to app root (excluding critical folders/files)
            $this->copyUpdateFiles($actualSource, $rootPath, $exclude);
            \Log::info('Update files copied from ' . $actualSource . ' to ' . $rootPath);

            // Clean up extracted folder
            $this->deleteDirectory($extractPath);
            \Log::info('Cleaned up extracted folder: ' . $extractPath);

            // Update SELF_UPDATER_VERSION_INSTALLED in .env
            $this->updateEnvVersion($targetVersion);
            \Log::info('Updated SELF_UPDATER_VERSION_INSTALLED in .env to ' . $targetVersion);

            // Clear config cache
            \Artisan::call('config:clear');

            // Run composer install --no-dev
            $composerOutput = null;
            $composerReturn = null;
            exec('composer install --no-dev 2>&1', $composerOutput, $composerReturn);
            \Log::info('composer install --no-dev output: ' . print_r($composerOutput, true));
            if ($composerReturn !== 0) {
                return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Update failed during composer install. Check logs for details.');
            }

            // Run php artisan migrate
            try {
                \Artisan::call('migrate', ['--force' => true]);
                \Log::info('php artisan migrate output: ' . \Artisan::output());
            } catch (\Exception $e) {
                \Log::error('php artisan migrate failed: ' . $e->getMessage());
                return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Update failed during migration. Check logs for details.');
            }

            // Redirect to updates page after success
            return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('success', 'System updated to version ' . $targetVersion . ' successfully! Composer and migrations have been run.');
        } catch (\Exception $e) {
            \Log::error('Update failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('tenant.updates.index', ['slug' => session('tenant_slug')])->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // Helper method to determine if a file should be skipped
    protected function shouldSkipFile($filePath, $relativePath, $exclude = [])
    {
        // Skip log files by extension
        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'log') {
            \Log::info("Skipping log file by extension: {$filePath}");
            return true;
        }
        
        // Skip files in use/locked
        if (file_exists($filePath) && !is_readable($filePath)) {
            \Log::info("Skipping unreadable file: {$filePath}");
            return true;
        }
        
        // Skip based on exclude patterns
        foreach ($exclude as $ex) {
            if (stripos($relativePath, $ex) !== false || stripos($filePath, $ex) !== false) {
                \Log::info("Skipping excluded file: {$filePath} (matched pattern: {$ex})");
                return true;
            }
        }
        
        return false;
    }
    
    // Recursively copy files from source to destination, skipping excluded folders/files
    protected function copyUpdateFiles($source, $destination, $exclude = [])
    {
        $dir = opendir($source);
        @mkdir($destination, 0755, true);
        
        while(false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $srcPath = $source . DIRECTORY_SEPARATOR . $file;
                $destPath = $destination . DIRECTORY_SEPARATOR . $file;
                
                // Check if this path should be excluded
                $relativePath = str_replace($destination, '', $destPath);
                
                if ($this->shouldSkipFile($destPath, $relativePath, $exclude)) {
                    continue;
                }
                
                if (is_dir($srcPath)) {
                    // Create directory if it doesn't exist
                    if (!file_exists($destPath)) {
                        mkdir($destPath, 0755, true);
                    }
                    $this->copyUpdateFiles($srcPath, $destPath, $exclude);
                } else {
                    // Ensure the destination directory exists
                    $destDir = dirname($destPath);
                    if (!file_exists($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    
                    // Try to copy the file, but continue if it fails due to file being in use
                    try {
                        // Check if file is writable before attempting to copy
                        if (file_exists($destPath) && !is_writable($destPath)) {
                            \Log::warning("Skipping file that cannot be written to: {$destPath}");
                            continue;
                        }
                        
                        if (copy($srcPath, $destPath)) {
                            chmod($destPath, 0644); // Set file permissions to 644
                        } else {
                            \Log::warning("Failed to copy file, but continuing: {$srcPath} to {$destPath}");
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Exception while copying file, skipping: {$srcPath} to {$destPath} - Error: " . $e->getMessage());
                        continue;
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
        $envContent = preg_replace(
            '/^SELF_UPDATER_VERSION_INSTALLED=.*$/m',
            'SELF_UPDATER_VERSION_INSTALLED=' . $newVersion,
            $envContent
        );
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
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }
} 