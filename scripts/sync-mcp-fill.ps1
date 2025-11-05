param()
$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$root = Split-Path -Parent $here
$dotenv = Join-Path $root '.env'
$script = Join-Path $here 'set-mcp-env.ps1'
if (-not (Test-Path $script)) { Write-Error "set-mcp-env.ps1 not found near $here"; exit 1 }
& $script -SyncToSession -AutoFill -DotEnvPath $dotenv

