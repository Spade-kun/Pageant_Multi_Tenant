<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update in Progress</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .update-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        .progress {
            height: 20px;
            margin: 20px 0;
        }
        .progress-bar {
            transition: width 2s;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 20px;
            display: none;
        }
        .manual-link {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-top: 20px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="update-container">
            <h2 class="mb-4">System Update in Progress</h2>
            
            <div id="updating-content">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="lead mb-4">Your system is being updated to version <strong>{{ $targetVersion }}</strong></p>
                
                <div class="progress">
                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                
                <div class="mt-3 mb-3">
                    <span class="badge badge-info" id="status-text">Starting update process...</span>
                </div>
                
                <p class="text-muted mt-3">
                    <small>Please do not close this window while the update is in progress.</small>
                </p>
                
                <div class="alert alert-info mt-4" role="alert">
                    <i class="fas fa-info-circle"></i> The application server may restart during this process.
                </div>
            </div>
            
            <div id="completed-content" style="display: none;">
                <i class="fas fa-check-circle success-icon"></i>
                <h3 class="text-success mb-3">Update completed successfully!</h3>
                <p>Redirecting to the success page...</p>
            </div>
            
            <div id="retry-content" style="display: none;">
                <div class="alert alert-warning mt-4" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> Server is restarting. We'll redirect you once it's back online.
                </div>
                <div class="text-center mt-3">
                    <p>Attempt <span id="attempts">1</span> of 10</p>
                </div>
            </div>
            
            <div id="manual-redirect" style="display: none;">
                <div class="alert alert-warning mt-4" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> The server is restarting as part of the update process.
                </div>
                <p>This is normal when system files are updated. You can:</p>
                <ol class="text-left">
                    <li class="mb-2">Try <strong>refreshing this page</strong> - the update should be complete</li>
                    <li class="mb-2">Click the link below to go to the success page:</li>
                </ol>
                <div class="manual-link">
                    <a href="{{ route('tenant.updates.success', ['slug' => $slug, 'version' => $targetVersion]) }}" id="manual-success-link">{{ route('tenant.updates.success', ['slug' => $slug]) }}</a>
                </div>
                <p class="mt-3">Or return to the updates page:</p>
                <div class="manual-link">
                    <a href="{{ route('tenant.updates.index', ['slug' => $slug]) }}" id="manual-updates-link">{{ route('tenant.updates.index', ['slug' => $slug]) }}</a>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary" id="try-again-btn">Try Again</button>
                </div>
                <hr>
                <div class="mt-4">
                    <p>If links don't work, use this form to navigate to the success page:</p>
                    <form action="{{ route('tenant.updates.success', ['slug' => $slug]) }}" method="GET">
                        <input type="hidden" name="version" value="{{ $targetVersion }}">
                        <input type="hidden" name="manual_redirect" value="1">
                        <button type="submit" class="btn btn-success">Go to Success Page</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Store important values
        const successUrl = "{{ route('tenant.updates.success', ['slug' => $slug]) }}";
        const updatesUrl = "{{ route('tenant.updates.index', ['slug' => $slug]) }}";
        
        // Setup page elements
        const progressBar = document.getElementById('progress-bar');
        const successIcon = document.querySelector('.success-icon');
        const updatingContent = document.getElementById('updating-content');
        const completedContent = document.getElementById('completed-content');
        const retryContent = document.getElementById('retry-content');
        const manualRedirect = document.getElementById('manual-redirect');
        const attemptsSpan = document.getElementById('attempts');
        const statusText = document.getElementById('status-text');
        const tryAgainBtn = document.getElementById('try-again-btn');
        
        // Setup local logging to help with debugging
        function logToLocalStorage(message) {
            try {
                if (window.localStorage) {
                    const timestamp = new Date().toISOString();
                    const logKey = 'update_log_{{ $targetVersion }}';
                    let logs = JSON.parse(localStorage.getItem(logKey) || '[]');
                    logs.push(`${timestamp}: ${message}`);
                    localStorage.setItem(logKey, JSON.stringify(logs.slice(-100))); // Keep only last 100 entries
                    console.log(`[UPDATE LOG] ${message}`);
                }
            } catch (e) {
                // Silently fail if localStorage is not available
                console.log(`[UPDATE LOG ERROR] ${e.message}`);
            }
        }
        
        // Log initial state
        logToLocalStorage(`Update process started for version ${successUrl}`);
        
        // Progress simulation
        let progress = 10;
        let progressSteps = [
            { progress: 20, message: "Downloading update package..." },
            { progress: 30, message: "Extracting files..." },
            { progress: 40, message: "Creating system backup..." },
            { progress: 50, message: "Installing new files..." },
            { progress: 60, message: "Updating {{ $updatedFiles ?? 'many' }} files..." },
            { progress: 70, message: "Running database migrations..." },
            { progress: 80, message: "Applying migrations: {{ $migrationStatus ?? 'in progress' }}" },
            { progress: 90, message: "Finalizing update..." }
        ];
        
        let currentStep = 0;
        
        // Simulate the update progress
        const progressInterval = setInterval(() => {
            if (currentStep < progressSteps.length) {
                progress = progressSteps[currentStep].progress;
                const message = progressSteps[currentStep].message;
                statusText.textContent = message;
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
                logToLocalStorage(`Progress: ${progress}%, ${message}`);
                currentStep++;
            } else {
                clearInterval(progressInterval);
            }
        }, 1500);
        
        // Function to check if the server is accessible
        function checkServerAndRedirect() {
            let attempts = 1;
            let maxAttempts = 10;
            
            function finalizeAndRedirect() {
                // Complete the progress bar
                progress = 100;
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
                statusText.textContent = "Update completed!";
                logToLocalStorage("Update process completed, preparing to redirect");
                
                // Show completion animation
                setTimeout(() => {
                    updatingContent.style.display = 'none';
                    retryContent.style.display = 'none';
                    completedContent.style.display = 'block';
                    successIcon.style.display = 'inline-block';
                    
                    // Try to redirect after showing the success message briefly
                    setTimeout(() => {
                        try {
                            logToLocalStorage("Redirecting to success page: " + successUrl);
                            
                            // Create and submit a form to navigate to the success page
                            // This approach works better with some browsers than window.location
                            const form = document.createElement('form');
                            form.method = 'GET';
                            form.action = successUrl;
                            
                            // Add hidden input for version
                            const versionInput = document.createElement('input');
                            versionInput.type = 'hidden';
                            versionInput.name = 'version';
                            versionInput.value = '{{ $targetVersion }}';
                            form.appendChild(versionInput);
                            
                            document.body.appendChild(form);
                            form.submit();
                        } catch (e) {
                            logToLocalStorage("Redirect failed: " + e.message);
                            // Show manual redirect options if automatic redirect fails
                            completedContent.style.display = 'none';
                            manualRedirect.style.display = 'block';
                        }
                    }, 1500);
                }, 500);
            }
            
            function attemptRedirect() {
                logToLocalStorage(`Attempt ${attempts} of ${maxAttempts} to check server availability`);
                
                // Try to fetch the success page to see if server is responsive
                fetch(successUrl, { 
                    method: 'HEAD',
                    cache: 'no-store', // Prevent caching
                    headers: { 'Cache-Control': 'no-cache' }
                })
                .then(response => {
                    logToLocalStorage(`Server responded with status: ${response.status}`);
                    if (response.ok) {
                        finalizeAndRedirect();
                    } else {
                        retryIfNeeded();
                    }
                })
                .catch(error => {
                    logToLocalStorage(`Error checking server: ${error.message}`);
                    console.error("Error checking server:", error);
                    retryIfNeeded();
                });
            }
            
            function retryIfNeeded() {
                if (attempts < maxAttempts) {
                    attempts++;
                    attemptsSpan.textContent = attempts;
                    logToLocalStorage(`Will retry in ${attempts} seconds (attempt ${attempts} of ${maxAttempts})`);
                    
                    // Show the retry message after the first attempt
                    if (attempts === 2) {
                        retryContent.style.display = 'block';
                    }
                    
                    // Update status text
                    statusText.textContent = "Waiting for server to respond...";
                    
                    // Exponential backoff: wait longer between attempts
                    setTimeout(attemptRedirect, attempts * 1000);
                } else {
                    // After max attempts, show manual options
                    logToLocalStorage(`Maximum attempts (${maxAttempts}) reached. Showing manual options.`);
                    updatingContent.style.display = 'none';
                    retryContent.style.display = 'none';
                    manualRedirect.style.display = 'block';
                }
            }
            
            // Start checking after allowing time for installation
            setTimeout(attemptRedirect, 5000);
        }
        
        // Try again button handler
        if (tryAgainBtn) {
            tryAgainBtn.addEventListener('click', function() {
                logToLocalStorage("Try again button clicked");
                manualRedirect.style.display = 'none';
                updatingContent.style.display = 'block';
                statusText.textContent = "Checking server status...";
                checkServerAndRedirect();
            });
        }
        
        // Begin the checking process after showing progress
        setTimeout(checkServerAndRedirect, 10000);
        
        // Add a fallback to show manual redirect options after a long timeout
        setTimeout(() => {
            // If we're still showing the progress or retry content after 2 minutes,
            // show the manual options
            if (completedContent.style.display === 'none' && manualRedirect.style.display === 'none') {
                logToLocalStorage("Timeout reached. Showing manual options.");
                updatingContent.style.display = 'none';
                retryContent.style.display = 'none';
                manualRedirect.style.display = 'block';
            }
        }, 120000); // 2 minutes
    </script>
</body>
</html> 