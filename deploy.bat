@echo off
echo.
echo ===========================================
echo    EURO TAXI SYSTEM - SMART DEPLOYER
echo ===========================================
echo.

echo [1/2] Pushing to GitHub (origin/main)...
git add .
git commit -m "Deployment Update: %date% %time%"
git push origin main

echo.
echo [2/2] Syncing to Hostinger Server...
node deployer.cjs

echo.
echo ===========================================
echo    DEPLOYMENT COMPLETE! Check your site.
echo ===========================================
pause
