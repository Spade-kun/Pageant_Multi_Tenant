@echo off
start cmd /k "php artisan serve --port=8000"
start cmd /k "php artisan serve --port=8001 --host=127.0.0.1"
echo Both portals are now running:
echo Tenant Portal: http://127.0.0.1:8000
echo Admin Portal: http://127.0.0.1:8001 