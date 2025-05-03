<?php

echo "Starting Pageant Multi-Tenant servers...\n";
echo "Press Ctrl+C to stop all servers.\n\n";

// Windows-specific approach
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Use start command with /B flag to run in the same console
    echo "Starting Tenant Portal (http://127.0.0.1:8000)...\n";
    pclose(popen('start /B php artisan serve --port=8000 > tenant-server.log 2>&1', 'r'));
    
    echo "Starting Admin Portal (http://127.0.0.1:8001)...\n";
    pclose(popen('start /B php artisan serve --port=8001 --host=127.0.0.1 > admin-server.log 2>&1', 'r'));
    
    echo "\nBoth servers are now running!\n";
    echo "Tenant Portal: http://127.0.0.1:8000\n";
    echo "Admin Portal: http://127.0.0.1:8001\n\n";
    
    echo "Press Enter to stop servers...";
    fgets(STDIN);
    
    // Kill the servers when done
    pclose(popen('taskkill /F /FI "WINDOWTITLE eq php artisan serve --port=8000" > nul 2>&1', 'r'));
    pclose(popen('taskkill /F /FI "WINDOWTITLE eq php artisan serve --port=8001" > nul 2>&1', 'r'));
    
    echo "\nServers stopped.\n";
} else {
    // For non-Windows systems (Linux/Mac)
    echo "Starting Tenant Portal (http://127.0.0.1:8000)...\n";
    exec('php artisan serve --port=8000 > tenant-server.log 2>&1 & echo $! > tenant-pid.txt');
    
    echo "Starting Admin Portal (http://127.0.0.1:8001)...\n";
    exec('php artisan serve --port=8001 --host=127.0.0.1 > admin-server.log 2>&1 & echo $! > admin-pid.txt');
    
    echo "\nBoth servers are now running!\n";
    echo "Tenant Portal: http://127.0.0.1:8000\n";
    echo "Admin Portal: http://127.0.0.1:8001\n\n";
    
    echo "Press Enter to stop servers...";
    fgets(STDIN);
    
    // Kill the servers when done
    if (file_exists('tenant-pid.txt')) {
        $pid = trim(file_get_contents('tenant-pid.txt'));
        exec("kill $pid");
        unlink('tenant-pid.txt');
    }
    
    if (file_exists('admin-pid.txt')) {
        $pid = trim(file_get_contents('admin-pid.txt'));
        exec("kill $pid");
        unlink('admin-pid.txt');
    }
    
    echo "\nServers stopped.\n";
} 