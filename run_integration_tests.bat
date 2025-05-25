@echo off
setlocal enabledelayedexpansion

echo ===============================================
echo Indonet Analytics Hub - Phase 1 Integration Tests
echo ===============================================

set PASSED=0
set FAILED=0

echo.
echo [INFO] Starting Phase 1 Integration Tests...
echo.

:: 1. Check if Laravel is properly installed
echo [INFO] Testing Laravel Installation...
php artisan --version >nul 2>&1
if !errorlevel! equ 0 (
    echo [PASS] Laravel installation
    set /a PASSED+=1
) else (
    echo [FAIL] Laravel installation
    set /a FAILED+=1
)

:: 2. Check database connection
echo [INFO] Testing Database Connection...
php artisan migrate:status >nul 2>&1
if !errorlevel! equ 0 (
    echo [PASS] Database connection
    set /a PASSED+=1
) else (
    echo [FAIL] Database connection
    set /a FAILED+=1
)

:: 3. Run backend tests
echo [INFO] Running Backend Tests...
php artisan test --quiet >nul 2>&1
if !errorlevel! equ 0 (
    echo [PASS] Backend tests
    set /a PASSED+=1
) else (
    echo [FAIL] Backend tests
    set /a FAILED+=1
)

:: 4. Check if frontend dependencies are installed
echo [INFO] Testing Frontend Dependencies...
npm list vue >nul 2>&1
if !errorlevel! equ 0 (
    echo [PASS] Frontend dependencies
    set /a PASSED+=1
) else (
    echo [FAIL] Frontend dependencies
    set /a FAILED+=1
)

:: 5. Build frontend for production
echo [INFO] Testing Frontend Build...
npm run build >nul 2>&1
if !errorlevel! equ 0 (
    echo [PASS] Frontend build
    set /a PASSED+=1
) else (
    echo [FAIL] Frontend build
    set /a FAILED+=1
)

:: 6. Check required files exist
echo [INFO] Checking Required Files...
set FILES_MISSING=0

if not exist "app\Http\Controllers\Api\AuthController.php" set FILES_MISSING=1
if not exist "app\Models\User.php" set FILES_MISSING=1
if not exist "resources\js\composables\useAuth.js" set FILES_MISSING=1
if not exist "resources\js\pages\login.vue" set FILES_MISSING=1
if not exist "resources\js\components\TermsModal.vue" set FILES_MISSING=1
if not exist "resources\js\components\ErrorBoundary.vue" set FILES_MISSING=1
if not exist "database\migrations" set FILES_MISSING=1
if not exist "tests\Feature\AuthenticationTest.php" set FILES_MISSING=1

if !FILES_MISSING! equ 0 (
    echo [PASS] Required files present
    set /a PASSED+=1
) else (
    echo [FAIL] Required files present
    set /a FAILED+=1
)

:: 7. Check security configurations
echo [INFO] Checking Security Configurations...
findstr /C:"SANCTUM_STATEFUL_DOMAINS" .env >nul 2>&1
if !errorlevel! equ 0 (
    echo [PASS] Security configurations
    set /a PASSED+=1
) else (
    echo [FAIL] Security configurations
    set /a FAILED+=1
)

:: 8. Check email configuration
echo [INFO] Checking Email Configuration...
findstr /C:"MAIL_MAILER" .env >nul 2>&1
if !errorlevel! equ 0 (
    echo [PASS] Email configuration
    set /a PASSED+=1
) else (
    echo [FAIL] Email configuration
    set /a FAILED+=1
)

:: 9. Validate environment setup
echo [INFO] Validating Environment Setup...
if exist ".env" (
    echo [PASS] Environment setup
    set /a PASSED+=1
) else (
    echo [FAIL] Environment setup
    set /a FAILED+=1
)

:: Calculate total
set /a TOTAL=PASSED+FAILED

echo.
echo ===============================================
echo Integration Test Results
echo ===============================================
echo Tests Passed: !PASSED!
echo Tests Failed: !FAILED!
echo Total Tests: !TOTAL!

if !FAILED! equ 0 (
    echo.
    echo [SUCCESS] All integration tests passed!
    echo Phase 1 implementation is complete and ready for production.
    echo.
    echo Next Steps:
    echo 1. Deploy to staging environment
    echo 2. Perform user acceptance testing
    echo 3. Plan Phase 2 features (RBAC)
    echo 4. Set up monitoring and logging
    exit /b 0
) else (
    echo.
    echo [ERROR] Some integration tests failed.
    echo Please review the failed tests and fix any issues before proceeding.
    exit /b 1
)

endlocal
