@echo off
echo ========================================
echo VbN Character Creator - XAMPP Setup
echo ========================================
echo.

echo [1/4] Starting XAMPP Control Panel...
start "" "C:\xampp\xampp-control.exe"
echo.
echo Please start Apache and MySQL in the XAMPP Control Panel
echo Press any key when both services are running (green status)...
pause >nul

echo.
echo [2/4] Testing database connection...
C:\xampp\php\php.exe test_db_connection.php
echo.

echo [3/4] Opening setup test page...
start "" "http://vbn.talkingheads.video/test_xampp_setup.php"
echo.

echo [4/4] Opening main application...
start "" "http://vbn.talkingheads.video/lotn_char_create.php"
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Your application should now be accessible at:
echo - Main App: http://vbn.talkingheads.video/lotn_char_create.php
echo - Setup Test: http://vbn.talkingheads.video/test_xampp_setup.php
echo - Database: vdb5.pit.pair.com
echo.
echo If you see any errors, check the XAMPP Control Panel
echo to ensure Apache and MySQL are both running.
echo.
pause

