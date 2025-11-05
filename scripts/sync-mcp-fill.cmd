@echo off
setlocal
set SCRIPT=%~dp0set-mcp-env.ps1
set DOTENV=%~dp0..\.env
if not exist "%SCRIPT%" (
  echo set-mcp-env.ps1 not found next to this script.
  exit /b 1
)
powershell -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT%" -SyncToSession -AutoFill -DotEnvPath "%DOTENV%"
endlocal

