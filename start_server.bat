@echo off
REM Start PHP Development Server for StudySmart Platform

echo Starting StudySmart PHP Server...
echo.
echo Server will be available at: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.

cd /d "%~dp0"

REM Check if PHP is installed
php -v >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP 8.0 or higher
    pause
    exit /b 1
)

REM Start the server with router support
php -S localhost:8000 serve_router.php
