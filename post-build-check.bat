@echo off
REM Post-build Asset Validation Script
REM This script runs after npm build to validate all assets are correct
REM 
REM Author: AI Code Assistant
REM Date: May 28, 2025

echo ===================================================
echo             Post-Build Asset Validation
echo ===================================================
echo.

REM Run asset validation
echo Running asset validation...
php validate-assets.php
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Asset validation failed!
    echo Please check the missing assets and fix before deployment.
    exit /b 1
)

echo.
echo Checking for 404 errors in logs...
php monitor-asset-404s.php

echo.
echo Asset validation complete - All assets validated successfully!
echo ===================================================
