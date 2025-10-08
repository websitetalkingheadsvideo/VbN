@echo off
echo ========================================
echo Copying VbN to XAMPP htdocs
echo ========================================
echo.

echo [1/3] Creating VbN directory in htdocs...
if not exist "C:\xampp\htdocs\VbN" mkdir "C:\xampp\htdocs\VbN"

echo [2/3] Copying project files...
xcopy /E /I /Y "C:\Users\paris\VbN\*" "C:\xampp\htdocs\VbN\"

echo [3/3] Copy complete!
echo.
echo Your project is now available at:
echo http://localhost/VbN/
echo.
echo Next steps:
echo 1. Start XAMPP services (Apache + MySQL)
echo 2. Run setup_xampp.sql in phpMyAdmin
echo 3. Test at http://localhost/VbN/test_xampp_setup.php
echo.
pause

