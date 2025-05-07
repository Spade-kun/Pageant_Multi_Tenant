<?php

// Define paths
$backupFile = __DIR__ . '/storage/app/updater/backup-v1.0.3.zip';
$extractPath = __DIR__ . '/storage/app/updater/restore-v1.0.3';
$rootPath = __DIR__;

// Create extract directory if it doesn't exist
if (!file_exists($extractPath)) {
    mkdir($extractPath, 0755, true);
}

// Extract the backup
$zip = new ZipArchive();
if ($zip->open($backupFile) === TRUE) {
    $zip->extractTo($extractPath);
    $zip->close();
    echo "Backup extracted successfully to: $extractPath\n";
} else {
    die("Failed to extract backup file\n");
}

// Copy files from extracted backup to root directory
function copyDirectory($source, $destination) {
    if (!file_exists($destination)) {
        mkdir($destination, 0755, true);
    }

    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcPath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;

            if (is_dir($srcPath)) {
                copyDirectory($srcPath, $destPath);
            } else {
                // Skip if destination file is in use
                if (file_exists($destPath) && !is_writable($destPath)) {
                    echo "Skipping file in use: $destPath\n";
                    continue;
                }
                
                if (copy($srcPath, $destPath)) {
                    chmod($destPath, 0644);
                    echo "Copied: $destPath\n";
                } else {
                    echo "Failed to copy: $destPath\n";
                }
            }
        }
    }
    closedir($dir);
}

// Copy files from extracted backup to root
copyDirectory($extractPath, $rootPath);

// Update .env version
$envPath = $rootPath . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    $envContent = preg_replace(
        '/^SELF_UPDATER_VERSION_INSTALLED=.*$/m',
        'SELF_UPDATER_VERSION_INSTALLED=1.0.3',
        $envContent
    );
    file_put_contents($envPath, $envContent);
    echo "Updated .env version to 1.0.3\n";
}

// Clean up
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
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

deleteDirectory($extractPath);
echo "Cleanup completed\n";
echo "Restore to v1.0.3 completed successfully!\n"; 