<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Successful - Redirecting</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .success-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 20px;
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
            transition: width 1s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <i class="fas fa-check-circle success-icon"></i>
            <h2 class="mb-3">Update Successful!</h2>
            <p class="lead mb-4">Your system has been updated to version <strong>{{ $version }}</strong></p>
            
            <div class="progress">
                <div id="redirect-progress" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            
            <p class="mt-3 mb-5">Redirecting to the success page...</p>
            
            <div class="text-center mb-3">
                <a href="{{ route('tenant.updates.success', ['slug' => $slug]) }}" class="btn btn-success btn-lg">
                    <i class="fas fa-arrow-right"></i> Go to Success Page
                </a>
            </div>
            
            <div class="mt-4">
                <a href="{{ route('tenant.updates.index', ['slug' => $slug]) }}" class="btn btn-link">Return to Updates Page</a>
            </div>
        </div>
    </div>

    <script>
        // Start the redirect timer
        let progress = 0;
        const progressBar = document.getElementById('redirect-progress');
        const successUrl = "{{ route('tenant.updates.success', ['slug' => $slug]) }}";
        
        // Increase progress every 50ms
        const progressInterval = setInterval(() => {
            progress += 1;
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            
            if (progress >= 100) {
                clearInterval(progressInterval);
                // Redirect when progress reaches 100%
                window.location.href = successUrl;
            }
        }, 50); // Will reach 100% in 5 seconds
        
        // Function to check if the server is accessible
        function checkServerAccessibility() {
            fetch(successUrl, { 
                method: 'HEAD',
                cache: 'no-store',
                headers: { 'Cache-Control': 'no-cache' }
            })
            .then(response => {
                if (response.ok) {
                    // If server is accessible, speed up the redirect
                    progress = Math.max(progress, 80);
                }
            })
            .catch(error => {
                console.error("Error checking server:", error);
                // If there's an error, slow down the redirect
                clearInterval(progressInterval);
                progressInterval = setInterval(() => {
                    progress += 0.5;
                    progressBar.style.width = progress + '%';
                    progressBar.setAttribute('aria-valuenow', progress);
                    
                    if (progress >= 100) {
                        clearInterval(progressInterval);
                        window.location.href = successUrl;
                    }
                }, 100); // Slower progress
            });
        }
        
        // Check server accessibility after 1 second
        setTimeout(checkServerAccessibility, 1000);
    </script>
</body>
</html> 