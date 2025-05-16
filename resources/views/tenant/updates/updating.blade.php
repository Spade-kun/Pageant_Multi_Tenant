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
        </div>
    </div>
    
    <script>
        // Simulate progress
        let progress = 10;
        const progressBar = document.getElementById('progress-bar');
        const successIcon = document.querySelector('.success-icon');
        const updatingContent = document.getElementById('updating-content');
        const completedContent = document.getElementById('completed-content');
        const retryContent = document.getElementById('retry-content');
        const attemptsSpan = document.getElementById('attempts');
        
        // Gradually increase progress
        const progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += 5;
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
            } else {
                clearInterval(progressInterval);
            }
        }, 500);
        
        // Function to check if the server is accessible
        function checkServerAndRedirect() {
            const successUrl = "{{ $successUrl }}";
            let attempts = 1;
            let maxAttempts = 10;
            
            function finalizeAndRedirect() {
                // Complete the progress bar
                progress = 100;
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
                
                // Show completion animation
                setTimeout(() => {
                    updatingContent.style.display = 'none';
                    retryContent.style.display = 'none';
                    completedContent.style.display = 'block';
                    successIcon.style.display = 'inline-block';
                    
                    // Redirect after showing the success message briefly
                    setTimeout(() => {
                        window.location.href = successUrl;
                    }, 1500);
                }, 500);
            }
            
            function attemptRedirect() {
                // Create a test request to check if server is responsive
                fetch(successUrl, { method: 'HEAD' })
                    .then(response => {
                        if (response.ok) {
                            finalizeAndRedirect();
                        } else {
                            retryIfNeeded();
                        }
                    })
                    .catch(error => {
                        retryIfNeeded();
                    });
            }
            
            function retryIfNeeded() {
                if (attempts < maxAttempts) {
                    attempts++;
                    attemptsSpan.textContent = attempts;
                    
                    // Show the retry message after the first attempt
                    if (attempts === 2) {
                        retryContent.style.display = 'block';
                    }
                    
                    // Exponential backoff: wait longer between attempts
                    setTimeout(attemptRedirect, attempts * 1000);
                } else {
                    // After max attempts, just try to redirect directly
                    finalizeAndRedirect();
                }
            }
            
            // Start checking after allowing time for installation
            setTimeout(attemptRedirect, 5000);
        }
        
        // Begin the checking process after a delay
        setTimeout(checkServerAndRedirect, 3000);
    </script>
</body>
</html> 