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

    public function update(Request $request, $slug)
    {
        try {
            // Get the latest release
            $release = $this->getLatestRelease();
            
            if (!$release) {
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->with('error', 'No updates available.');
            }

            // Download and extract the release
            $zipPath = $this->downloadRelease($release['zipball_url']);
            $extractPath = $this->extractRelease($zipPath);

            // Perform the update
            $this->performUpdate($extractPath);

            // Clean up
            $this->cleanup($zipPath, $extractPath);

            // Redirect to success page
            return redirect()->route('tenant.updates.success', [
                'slug' => $slug,
                'version' => $release['tag_name']
            ]);

        } catch (\Exception $e) {
            \Log::error('Update failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()->route('tenant.updates.index', ['slug' => $slug])
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function success(Request $request, $slug)
    {
        $version = $request->query('version', 'latest');
        
        return view('tenant.updates.success', [
            'slug' => $slug,
            'version' => $version
        ]);
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

    /**
     * Get the latest release from GitHub
     * 
     * @return array|null
     */
    protected function getLatestRelease()
    {
        try {
            $vendor = config('self-update.repository_types.github.repository_vendor');
            $repo = config('self-update.repository_types.github.repository_name');
            $token = config('self-update.repository_types.github.private_access_token');

            if (empty($token)) {
                \Log::warning('GitHub access token is not configured. Please set SELF_UPDATER_GITHUB_PRIVATE_ACCESS_TOKEN in your .env file.');
                return null;
            }

            $response = $this->client->get("repos/{$vendor}/{$repo}/releases/latest");
            $release = json_decode($response->getBody(), true);

            if (empty($release)) {
                \Log::info('No latest release found for the repository.');
                return null;
            }

            return [
                'version' => ltrim($release['tag_name'], 'v'),
                'released_at' => date('Y-m-d H:i:s', strtotime($release['published_at'])),
                'description' => $release['body'],
                'author' => $release['author']['login'],
                'zipball_url' => $release['zipball_url'],
                'assets' => collect($release['assets'])->map(function ($asset) {
                    return [
                        'name' => $asset['name'],
                        'size' => $asset['size'],
                        'download_url' => $asset['browser_download_url']
                    ];
                })->toArray()
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching latest release: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Download a release from GitHub
     * 
     * @param string $url The URL to download from
     * @return string The path to the downloaded file
     */
    protected function downloadRelease($url)
    {
        try {
            // First get the download URL from the API
            $response = $this->client->get($url);
            $release = json_decode($response->getBody(), true);
            
            if (empty($release['zipball_url'])) {
                throw new \Exception('No download URL found in the release');
            }

            // Now download the actual zip file
            $downloadResponse = $this->client->get($release['zipball_url'], [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'token ' . config('self-update.repository_types.github.private_access_token')
                ],
                'allow_redirects' => true
            ]);

            $tempPath = storage_path('app/updates');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $zipPath = $tempPath . '/update.zip';
            file_put_contents($zipPath, $downloadResponse->getBody());

            return $zipPath;
        } catch (\Exception $e) {
            \Log::error('Error downloading release: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract a downloaded release
     * 
     * @param string $zipPath The path to the zip file
     * @return string The path where the files were extracted
     */
    protected function extractRelease($zipPath)
    {
        try {
            $tempPath = storage_path('app/updates/extracted');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($tempPath);
                $zip->close();
                return $tempPath;
            }

            throw new \Exception('Failed to extract zip file');
        } catch (\Exception $e) {
            \Log::error('Error extracting release: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Perform the actual update
     * 
     * @param string $extractPath The path where the files were extracted
     */
    protected function performUpdate($extractPath)
    {
        try {
            // Find the actual application root in the extracted files
            $sourceRoot = $this->detectSourceCodeRoot($extractPath);
            
            // Define files and directories to exclude from the update
            $exclude = [
                '.env',
                '.git',
                'storage',
                'vendor',
                'node_modules',
                'public/uploads',
                'public/storage'
            ];

            // Copy the files
            $this->copyUpdateFiles($sourceRoot, base_path(), $exclude);

            // Run composer update
            $this->runComposerUpdate();

            // Run migrations
            $this->runMigrations();

            // Clear caches
            $this->clearCaches();
        } catch (\Exception $e) {
            \Log::error('Error performing update: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Run composer update
     */
    protected function runComposerUpdate()
    {
        try {
            $command = 'composer update --no-interaction --no-dev --optimize-autoloader';
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Composer update failed with code: ' . $returnCode);
            }
        } catch (\Exception $e) {
            \Log::error('Error running composer update: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Run database migrations
     */
    protected function runMigrations()
    {
        try {
            \Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            \Log::error('Error running migrations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clear application caches
     */
    protected function clearCaches()
    {
        try {
            \Artisan::call('config:clear');
            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');
        } catch (\Exception $e) {
            \Log::error('Error clearing caches: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clean up temporary files
     * 
     * @param string $zipPath The path to the zip file
     * @param string $extractPath The path where files were extracted
     */
    protected function cleanup($zipPath, $extractPath)
    {
        try {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            if (file_exists($extractPath)) {
                $this->deleteDirectory($extractPath);
            }
        } catch (\Exception $e) {
            \Log::error('Error cleaning up: ' . $e->getMessage());
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