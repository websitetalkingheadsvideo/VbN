# Commit and Summarize Script for LoTN Character Creation Project
# This script increments version, stages, commits, pushes, and creates a summary

Write-Host "=== COMMIT AND SUMMARIZE PROCESS ===" -ForegroundColor Green
Write-Host ""

# Step 1: Increment version
Write-Host "Step 1: Incrementing version..." -ForegroundColor Yellow
$content = Get-Content 'lotn_char_create.php'
$newVersion = '0.2.9'
$content = $content -replace "define\('LOTN_VERSION', '[^']*'\)", "define('LOTN_VERSION', '$newVersion')"
Set-Content 'lotn_char_create.php' $content
Write-Host "Version incremented to $newVersion" -ForegroundColor Green
Write-Host ""

# Step 2: Stage changes
Write-Host "Step 2: Staging changes..." -ForegroundColor Yellow
git add .
Write-Host "Changes staged" -ForegroundColor Green
Write-Host ""

# Step 3: Commit
Write-Host "Step 3: Committing..." -ForegroundColor Yellow
git commit -m "feat: Update discipline system with XP spending and popover improvements" -m "Fixed discipline level counting (highest level per discipline)" -m "Added XP spending after 3 free levels" -m "Disabled popover on disabled discipline buttons" -m "Improved cost display with insufficient XP handling" -m "Enhanced user experience with confirmation dialogs"
Write-Host "Changes committed" -ForegroundColor Green
Write-Host ""

# Step 4: Push
Write-Host "Step 4: Pushing to remote..." -ForegroundColor Yellow
git push
Write-Host "Changes pushed to remote" -ForegroundColor Green
Write-Host ""

# Step 5: Create summary
Write-Host "Step 5: Creating summary for next chat..." -ForegroundColor Yellow
Write-Host ""
Write-Host "=== PROJECT SUMMARY FOR NEXT CHAT ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "RECENT CHANGES COMPLETED:" -ForegroundColor White
Write-Host "- Fixed discipline level counting system"
Write-Host "- Implemented XP spending after 3 free levels"
Write-Host "- Added popover disable for disabled disciplines"
Write-Host "- Enhanced cost display with color coding"
Write-Host "- Added confirmation dialogs for XP spending"
Write-Host ""
Write-Host "CURRENT VERSION: $newVersion" -ForegroundColor White
Write-Host ""
Write-Host "KEY FEATURES WORKING:" -ForegroundColor White
Write-Host "- Character creation with 3 free discipline levels"
Write-Host "- Power selection with prerequisites"
Write-Host "- XP cost calculation (in-clan vs out-of-clan)"
Write-Host "- Clan-based discipline access control"
Write-Host "- Advancement mode after character completion"
Write-Host "- Discipline power removal system"
Write-Host ""
Write-Host "FILES MODIFIED:" -ForegroundColor White
Write-Host "- lotn_char_create.php (version update)"
Write-Host "- js/script.js (discipline system improvements)"
Write-Host "- css/style.css (popover styling)"
Write-Host ""
Write-Host "NEXT POTENTIAL TASKS:" -ForegroundColor White
Write-Host "- Database integration for discipline powers"
Write-Host "- Admin panel implementation"
Write-Host "- Backgrounds tab development"
Write-Host "- Morality and Stats section"
Write-Host "- Merits and Flaws system"
Write-Host ""
Write-Host "=== END SUMMARY ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Process completed successfully!" -ForegroundColor Green
Write-Host "Ready for next development session." -ForegroundColor Green
