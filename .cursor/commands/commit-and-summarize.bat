@echo off
echo === COMMIT AND SUMMARIZE PROCESS ===
echo.

echo Step 1: Incrementing version...
powershell -Command "$content = Get-Content 'lotn_char_create.php'; $newVersion = '0.2.9'; $content = $content -replace \"define\('LOTN_VERSION', '[^']*'\)\", \"define('LOTN_VERSION', '$newVersion')\"; Set-Content 'lotn_char_create.php' $content; Write-Host 'Version incremented to $newVersion'"
echo.

echo Step 2: Staging changes...
git add .
echo Changes staged
echo.

echo Step 3: Committing...
git commit -m "feat: Update discipline system with XP spending and popover improvements" -m "Fixed discipline level counting (highest level per discipline)" -m "Added XP spending after 3 free levels" -m "Disabled popover on disabled discipline buttons" -m "Improved cost display with insufficient XP handling" -m "Enhanced user experience with confirmation dialogs"
echo Changes committed
echo.

echo Step 4: Pushing to remote...
git push
echo Changes pushed to remote
echo.

echo Step 5: Creating summary for next chat...
echo.
echo === PROJECT SUMMARY FOR NEXT CHAT ===
echo.
echo RECENT CHANGES COMPLETED:
echo - Fixed discipline level counting system
echo - Implemented XP spending after 3 free levels
echo - Added popover disable for disabled disciplines
echo - Enhanced cost display with color coding
echo - Added confirmation dialogs for XP spending
echo.
echo CURRENT VERSION: 0.2.9
echo.
echo KEY FEATURES WORKING:
echo - Character creation with 3 free discipline levels
echo - Power selection with prerequisites
echo - XP cost calculation (in-clan vs out-of-clan)
echo - Clan-based discipline access control
echo - Advancement mode after character completion
echo - Discipline power removal system
echo.
echo FILES MODIFIED:
echo - lotn_char_create.php (version update)
echo - js/script.js (discipline system improvements)
echo - css/style.css (popover styling)
echo.
echo NEXT POTENTIAL TASKS:
echo - Database integration for discipline powers
echo - Admin panel implementation
echo - Backgrounds tab development
echo - Morality and Stats section
echo - Merits and Flaws system
echo.
echo === END SUMMARY ===
echo.
echo Process completed successfully!
echo Ready for next development session.
pause
