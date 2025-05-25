#!/bin/bash

# RBAC Testing Suite Runner
# This script runs all RBAC-related tests for the Indonet Analytics Hub

echo "=================================================="
echo "    RBAC Testing Suite - Indonet Analytics Hub"
echo "=================================================="

# Set the working directory
cd "$(dirname "$0")"

echo ""
echo "Setting up test database..."
php artisan migrate:fresh --seed --quiet

echo ""
echo "Running Unit Tests..."
echo "----------------------"

echo "Testing Permission Model..."
php artisan test tests/Unit/PermissionTest.php --compact

echo ""
echo "Testing Role Model..."
php artisan test tests/Unit/RoleTest.php --compact

echo ""
echo "Testing User Role Relationships..."
php artisan test tests/Unit/UserRoleTest.php --compact

echo ""
echo "Testing Permission Policy..."
php artisan test tests/Unit/PermissionPolicyTest.php --compact

echo ""
echo "Testing Role Policy..."
php artisan test tests/Unit/RolePolicyTest.php --compact

echo ""
echo "Running Feature Tests..."
echo "------------------------"

echo "Testing Permission API Controller..."
php artisan test tests/Feature/PermissionControllerTest.php --compact

echo ""
echo "Testing Role API Controller..."
php artisan test tests/Feature/RoleControllerTest.php --compact

echo ""
echo "Testing User Role API Controller..."
php artisan test tests/Feature/UserRoleControllerTest.php --compact

echo ""
echo "Running All RBAC Tests Together..."
echo "----------------------------------"
php artisan test --testsuite=Unit --filter="Permission|Role|UserRole" --compact
php artisan test --testsuite=Feature --filter="Permission|Role|UserRole" --compact

echo ""
echo "=================================================="
echo "              Test Suite Complete"
echo "=================================================="
echo ""
echo "Test Coverage Areas:"
echo "• Permission CRUD operations"
echo "• Role management with permission assignment"
echo "• User role assignment and management"
echo "• Authorization policies for all operations"
echo "• API endpoint security and validation"
echo "• Model relationships and constraints"
echo "• Database integrity and soft deletes"
echo ""
echo "Manual Testing Required:"
echo "• Frontend Vue.js components functionality"
echo "• UI interactions and user experience"
echo "• Integration with existing authentication"
echo "• Performance with large datasets"
echo ""
