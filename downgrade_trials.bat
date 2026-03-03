@echo off
REM Script to downgrade expired trial subscriptions to free plan
REM Schedule this to run daily via Windows Task Scheduler
REM 
REM To schedule: 
REM 1. Open Task Scheduler
REM 2. Create Basic Task
REM 3. Set trigger to Daily
REM 4. Set action to "Start a program"
REM 5. Browse to this file

cd /d "%~dp0"
php downgrade_trials.php
pause
