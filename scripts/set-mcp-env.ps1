param(
    [switch]$OnlyMissing = $true,
    [switch]$Force,
    [switch]$SyncToSession,
    [switch]$Quiet,
    [switch]$AutoFill,
    [string]$DotEnvPath
)

Write-Host "Configure MCP API keys as user environment variables" -ForegroundColor Cyan
Write-Host "Leave any prompt blank to skip that service." -ForegroundColor DarkGray

$vars = @(
    @{ Name = 'OPENAI_API_KEY';       Prompt = 'OpenAI API key (sk-... or sk-proj-...)' },
    @{ Name = 'ANTHROPIC_API_KEY';    Prompt = 'Anthropic API key (sk-ant-...)' },
    @{ Name = 'ANTHROPIC_MODEL';      Prompt = 'Anthropic model (e.g., claude-sonnet-4-20250514) [optional]' },
    @{ Name = 'PERPLEXITY_API_KEY';   Prompt = 'Perplexity API key' },
    @{ Name = 'OPENROUTER_API_KEY';   Prompt = 'OpenRouter API key (sk-or-...)' },
    @{ Name = 'GOOGLE_API_KEY';       Prompt = 'Google API key' },
    @{ Name = 'XAI_API_KEY';          Prompt = 'xAI API key' },
    @{ Name = 'MISTRAL_API_KEY';      Prompt = 'Mistral API key' },
    @{ Name = 'AZURE_OPENAI_API_KEY'; Prompt = 'Azure OpenAI API key' },
    @{ Name = 'OLLAMA_API_KEY';       Prompt = 'Ollama API key (optional for local setups)' },
    @{ Name = 'DB_HOST';              Prompt = 'Laws Agent DB host (e.g., db.example.com)' },
    @{ Name = 'DB_USER';              Prompt = 'Laws Agent DB user' },
    @{ Name = 'DB_PASS';              Prompt = 'Laws Agent DB password' },
    @{ Name = 'DB_NAME';              Prompt = 'Laws Agent DB name' }
)

# Fast-path: sync any existing User-scope values into this session (optionally autofill) without prompts
if ($SyncToSession) {
    if (-not $Quiet) { Write-Host ">>> MCP env sync start" -ForegroundColor Cyan }

    # Optional: preload .env values if AutoFill is requested
    $dotenvMap = @{}
    if ($AutoFill) {
        if (-not $DotEnvPath -or [string]::IsNullOrWhiteSpace($DotEnvPath)) {
            try {
                $scriptDir = Split-Path -Parent $PSCommandPath
                $repoRoot = Split-Path -Parent $scriptDir
                $DotEnvPath = Join-Path $repoRoot '.env'
            } catch {}
        }
        if ($DotEnvPath -and (Test-Path -LiteralPath $DotEnvPath)) {
            try {
                Get-Content -LiteralPath $DotEnvPath -ErrorAction Stop | ForEach-Object {
                    $line = $_.Trim()
                    if ($line -eq '' -or $line.StartsWith('#')) { return }
                    $m = [System.Text.RegularExpressions.Regex]::Match($line, '^[ \t]*([A-Za-z_][A-Za-z0-9_]*)[ \t]*=[ \t]*(.*)$')
                    if ($m.Success) {
                        $name = $m.Groups[1].Value
                        $val = $m.Groups[2].Value.Trim()
                        # Strip surrounding quotes using char codes to avoid quoting issues
                        $dq = [char]34
                        $sq = [char]39
                        if ($val.Length -ge 2 -and (
                            (($val.StartsWith($dq)) -and ($val.EndsWith($dq))) -or
                            (($val.StartsWith($sq)) -and ($val.EndsWith($sq)))
                        )) {
                            $val = $val.Substring(1, $val.Length - 2)
                        }
                        $dotenvMap[$name] = $val
                    }
                }
                if (-not $Quiet) { Write-Host "Loaded .env entries from $DotEnvPath" -ForegroundColor DarkGray }
            } catch {
                if (-not $Quiet) { Write-Warning ("Failed reading {0}: {1}" -f $DotEnvPath, $_.Exception.Message) }
            }
        } else {
            if (-not $Quiet) { Write-Host ".env not found for AutoFill: $DotEnvPath" -ForegroundColor DarkGray }
        }
    }

    $synced = @()
    $missing = 0
    $filledFromMachine = 0
    $filledFromDotEnv = 0
    foreach ($v in $vars) {
        $userVal = [Environment]::GetEnvironmentVariable($v.Name, 'User')
        if ([string]::IsNullOrWhiteSpace($userVal) -and $AutoFill) {
            # Try Machine scope first
            try { $machineVal = [Environment]::GetEnvironmentVariable($v.Name, 'Machine') } catch { $machineVal = $null }
            if (-not [string]::IsNullOrWhiteSpace($machineVal)) {
                try {
                    & setx $v.Name $machineVal | Out-Null
                    $userVal = $machineVal
                    $filledFromMachine++
                    if (-not $Quiet) { Write-Host "Filled from Machine: $($v.Name)" -ForegroundColor DarkGray }
                } catch {
                    if (-not $Quiet) { Write-Warning "Failed persisting $($v.Name) from Machine: $($_.Exception.Message)" }
                }
            }
            # Then try .env map
            if ([string]::IsNullOrWhiteSpace($userVal) -and $dotenvMap.ContainsKey($v.Name)) {
                $val = $dotenvMap[$v.Name]
                if (-not [string]::IsNullOrWhiteSpace($val)) {
                    try {
                        & setx $v.Name $val | Out-Null
                        $userVal = $val
                        $filledFromDotEnv++
                        if (-not $Quiet) { Write-Host "Filled from .env: $($v.Name)" -ForegroundColor DarkGray }
                    } catch {
                        if (-not $Quiet) { Write-Warning "Failed persisting $($v.Name) from .env: $($_.Exception.Message)" }
                    }
                }
            }
        }

        if (-not [string]::IsNullOrWhiteSpace($userVal)) {
            try {
                Set-Item -Path "Env:$($v.Name)" -Value $userVal -ErrorAction Stop
                $synced += $v.Name
            } catch {
                if (-not $Quiet) { Write-Warning "Failed to load $($v.Name) into session: $($_.Exception.Message)" }
            }
        } else {
            $missing++
            if (-not $Quiet) { Write-Host "Missing at User scope: $($v.Name)" -ForegroundColor DarkGray }
        }
    }

    if ($synced.Count -gt 0) {
        Write-Host "Loaded into current session:" -ForegroundColor Green
        $synced | ForEach-Object { Write-Host "  - $_" }
    } else {
        Write-Host "Nothing to load; no User-scope values found." -ForegroundColor Yellow
    }
    if (-not $Quiet) { Write-Host ">>> MCP env sync done ($($synced.Count) loaded, $missing missing, $filledFromMachine from Machine, $filledFromDotEnv from .env)" -ForegroundColor Cyan }
    return
}

$setNames = @()
foreach ($v in $vars) {
    $existingUser = [Environment]::GetEnvironmentVariable($v.Name, 'User')
    $existingProc = [Environment]::GetEnvironmentVariable($v.Name, 'Process')

    if ($OnlyMissing -and -not $Force -and -not [string]::IsNullOrWhiteSpace($existingUser)) {
        Write-Host "Skipping $($v.Name) (already set)" -ForegroundColor DarkGray
        # Ensure current session has it as well
        if ([string]::IsNullOrWhiteSpace($existingProc)) {
            Set-Item -Path "Env:$($v.Name)" -Value $existingUser -ErrorAction SilentlyContinue
        }
        continue
    }

    $val = Read-Host "Enter $($v.Prompt)"
    if ([string]::IsNullOrWhiteSpace($val)) { continue }

    try {
        # Persist for future sessions
        & setx $v.Name $val | Out-Null
        # Also set for current session
        Set-Item -Path "Env:$($v.Name)" -Value $val
        $setNames += $v.Name
    } catch {
        Write-Warning "Failed setting $($v.Name): $($_.Exception.Message)"
    }
}

if ($setNames.Count -gt 0) {
    Write-Host "`nSet variables:" -ForegroundColor Green
    $setNames | ForEach-Object { Write-Host "  - $_" }
    Write-Host "`nNote: Restart your IDE/terminal for changes to apply everywhere." -ForegroundColor Yellow
    Write-Host "Current session already has these values available." -ForegroundColor Yellow
} else {
    Write-Host "No variables set." -ForegroundColor Yellow
}

Write-Host "`nTest examples (optional):" -ForegroundColor DarkGray
Write-Host "  [bool]([string]::IsNullOrEmpty($env:OPENAI_API_KEY)) # should be False if set" -ForegroundColor DarkGray
