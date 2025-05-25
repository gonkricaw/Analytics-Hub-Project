#!/bin/bash

# Indonet Analytics Hub - Phase 1 Integration Test Script
echo "==============================================="
echo "Indonet Analytics Hub - Phase 1 Integration Tests"
echo "==============================================="

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    if [ $2 -eq 0 ]; then
        echo -e "${GREEN}✓ $1${NC}"
    else
        echo -e "${RED}✗ $1${NC}"
    fi
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Test counter
PASSED=0
FAILED=0

echo ""
print_info "Starting Phase 1 Integration Tests..."
echo ""

# 1. Check if Laravel is properly installed
print_info "Testing Laravel Installation..."
php artisan --version > /dev/null 2>&1
print_status "Laravel installation" $?
if [ $? -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

# 2. Check database connection
print_info "Testing Database Connection..."
php artisan migrate:status > /dev/null 2>&1
print_status "Database connection" $?
if [ $? -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

# 3. Run backend tests
print_info "Running Backend Tests..."
php artisan test --quiet > /dev/null 2>&1
print_status "Backend tests" $?
if [ $? -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

# 4. Check if frontend dependencies are installed
print_info "Testing Frontend Dependencies..."
npm list vue > /dev/null 2>&1
print_status "Frontend dependencies" $?
if [ $? -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

# 5. Build frontend for production
print_info "Testing Frontend Build..."
npm run build > /dev/null 2>&1
print_status "Frontend build" $?
if [ $? -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

# 6. Check API endpoints accessibility
print_info "Testing API Endpoints..."
php artisan serve --quiet &
SERVER_PID=$!
sleep 3

# Test CSRF endpoint
curl -s http://localhost:8000/sanctum/csrf-cookie > /dev/null 2>&1
CSRF_STATUS=$?

# Stop server
kill $SERVER_PID > /dev/null 2>&1

print_status "API endpoints" $CSRF_STATUS
if [ $CSRF_STATUS -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

# 7. Check required files exist
print_info "Checking Required Files..."
FILES=(
    "app/Http/Controllers/Api/AuthController.php"
    "app/Models/User.php"
    "resources/js/composables/useAuth.js"
    "resources/js/pages/login.vue"
    "resources/js/components/TermsModal.vue"
    "resources/js/components/ErrorBoundary.vue"
    "database/migrations"
    "tests/Feature/AuthenticationTest.php"
)

ALL_FILES_EXIST=0
for file in "${FILES[@]}"; do
    if [ -e "$file" ]; then
        continue
    else
        ALL_FILES_EXIST=1
        break
    fi
done

print_status "Required files present" $ALL_FILES_EXIST
if [ $ALL_FILES_EXIST -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

# 8. Check security configurations
print_info "Checking Security Configurations..."
grep -q "SANCTUM_STATEFUL_DOMAINS" .env > /dev/null 2>&1
SECURITY_STATUS=$?

print_status "Security configurations" $SECURITY_STATUS
if [ $SECURITY_STATUS -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

# 9. Check email configuration
print_info "Checking Email Configuration..."
grep -q "MAIL_MAILER" .env > /dev/null 2>&1
EMAIL_STATUS=$?

print_status "Email configuration" $EMAIL_STATUS
if [ $EMAIL_STATUS -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

# 10. Validate environment setup
print_info "Validating Environment Setup..."
ENV_VALID=0

# Check if required environment variables exist
if [ ! -f .env ]; then
    ENV_VALID=1
fi

print_status "Environment setup" $ENV_VALID
if [ $ENV_VALID -eq 0 ]; then ((PASSED++)); else ((FAILED++)); fi

echo ""
echo "==============================================="
echo "Integration Test Results"
echo "==============================================="
echo -e "Tests Passed: ${GREEN}$PASSED${NC}"
echo -e "Tests Failed: ${RED}$FAILED${NC}"
echo -e "Total Tests: $((PASSED + FAILED))"

if [ $FAILED -eq 0 ]; then
    echo ""
    echo -e "${GREEN}🎉 All integration tests passed!${NC}"
    echo -e "${GREEN}Phase 1 implementation is complete and ready for production.${NC}"
    echo ""
    echo "Next Steps:"
    echo "1. Deploy to staging environment"
    echo "2. Perform user acceptance testing"
    echo "3. Plan Phase 2 features (RBAC)"
    echo "4. Set up monitoring and logging"
    exit 0
else
    echo ""
    echo -e "${RED}❌ Some integration tests failed.${NC}"
    echo -e "${YELLOW}Please review the failed tests and fix any issues before proceeding.${NC}"
    exit 1
fi
