@echo off
REM Batch file to manage Django development server

setlocal

if "%1"=="start" (
    echo Starting Django development server...
    python start_server.py %2 %3 %4 %5
) else if "%1"=="stop" (
    echo Stopping Django development server...
    python stop_server.py
) else if "%1"=="restart" (
    echo Restarting Django development server...
    python stop_server.py
    timeout /t 2 /nobreak >nul
    python start_server.py %2 %3 %4 %5
) else (
    echo Usage: %0 [start ^| stop ^| restart] [port]
    echo   start   - Start the Django development server
    echo   stop    - Stop any running Django development server processes
    echo   restart - Stop and restart the Django development server
    echo   port    - Optional port number (e.g., 8080)
    echo.
    echo Example: %0 start 8080
)

endlocal