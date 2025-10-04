@echo off
echo Starting LOTN Character Creator Development Environment...
echo.

echo [1/3] Starting XAMPP services...
echo Please make sure XAMPP Control Panel is running and MySQL is started
echo Press any key when MySQL is running...
pause >nul

echo.
echo [2/3] Setting up Python environment...
if not exist "venv" (
    echo Creating Python virtual environment...
    python -m venv venv
)

echo Activating virtual environment...
call venv\Scripts\activate.bat

echo Installing Python dependencies...
pip install -r requirements.txt

echo.
echo [3/3] Starting Python API server...
echo Python API will run on http://localhost:5000
echo PHP application should be accessible via XAMPP at http://localhost/VbN/
echo.
echo Press Ctrl+C to stop the Python server
echo.

python python_api.py
