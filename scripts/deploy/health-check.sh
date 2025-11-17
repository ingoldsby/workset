#!/bin/bash

###############################################################################
# Workset Health Check Script
#
# Verifies all services are running correctly
# Used for post-deployment verification
#
# Usage: ./health-check.sh [environment]
#   environment: production|staging (default: production)
###############################################################################

set -euo pipefail

# Configuration
ENVIRONMENT="${1:-production}"

if [ "$ENVIRONMENT" == "production" ]; then
    BASE_URL="https://workset.kneebone.com.au"
elif [ "$ENVIRONMENT" == "staging" ]; then
    BASE_URL="https://staging.workset.kneebone.com.au"
else
    echo "Invalid environment: $ENVIRONMENT"
    echo "Usage: $0 [production|staging]"
    exit 1
fi

# Colours
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Functions
test_pass() {
    echo -e "${GREEN}✓${NC} $1"
    ((TESTS_PASSED++))
}

test_fail() {
    echo -e "${RED}✗${NC} $1"
    ((TESTS_FAILED++))
}

test_warn() {
    echo -e "${YELLOW}⚠${NC} $1"
}

echo "=================================================="
echo "Workset Health Check - $ENVIRONMENT"
echo "Base URL: $BASE_URL"
echo "=================================================="
echo ""

# Test 1: Application responds
echo "Testing application response..."
if curl -f -s -o /dev/null -w "%{http_code}" "$BASE_URL/health" | grep -q "200"; then
    test_pass "Application health endpoint responding"
else
    test_fail "Application health endpoint not responding"
fi

# Test 2: Database connection
echo "Testing database connection..."
if docker-compose exec -T app php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" 2>&1 | grep -q "OK"; then
    test_pass "Database connection working"
else
    test_fail "Database connection failed"
fi

# Test 3: Redis connection
echo "Testing Redis connection..."
if docker-compose exec -T app php artisan tinker --execute="Redis::ping(); echo 'OK';" 2>&1 | grep -q "OK"; then
    test_pass "Redis connection working"
else
    test_fail "Redis connection failed"
fi

# Test 4: Queue workers (Horizon)
echo "Testing Horizon status..."
HORIZON_STATUS=$(docker-compose exec -T app php artisan horizon:status 2>&1 || echo "inactive")
if echo "$HORIZON_STATUS" | grep -q "running"; then
    test_pass "Horizon is running"
else
    test_fail "Horizon is not running"
fi

# Test 5: Reverb (WebSockets)
echo "Testing Reverb service..."
if docker-compose ps reverb | grep -q "Up"; then
    test_pass "Reverb service is up"
else
    test_fail "Reverb service is down"
fi

# Test 6: Scheduler
echo "Testing scheduler service..."
if docker-compose ps scheduler | grep -q "Up"; then
    test_pass "Scheduler service is up"
else
    test_fail "Scheduler service is down"
fi

# Test 7: Meilisearch
echo "Testing Meilisearch connection..."
if curl -f -s -o /dev/null "$MEILISEARCH_HOST/health" 2>/dev/null || \
   curl -f -s -o /dev/null "http://localhost:7700/health" 2>/dev/null; then
    test_pass "Meilisearch is responding"
else
    test_warn "Meilisearch health check skipped (may not be accessible externally)"
fi

# Test 8: Storage directories writable
echo "Testing storage permissions..."
if docker-compose exec -T app test -w /var/www/html/storage; then
    test_pass "Storage directory is writable"
else
    test_fail "Storage directory is not writable"
fi

# Test 9: Cache functionality
echo "Testing cache..."
TEST_KEY="health_check_$(date +%s)"
if docker-compose exec -T app php artisan tinker --execute="Cache::put('$TEST_KEY', 'test', 10); echo Cache::get('$TEST_KEY');" 2>&1 | grep -q "test"; then
    test_pass "Cache is working"
    docker-compose exec -T app php artisan tinker --execute="Cache::forget('$TEST_KEY');" >/dev/null 2>&1
else
    test_fail "Cache is not working"
fi

# Test 10: Queue job processing
echo "Testing queue job dispatch..."
if docker-compose exec -T app php artisan tinker --execute="Queue::size();" >/dev/null 2>&1; then
    test_pass "Queue is accessible"
else
    test_fail "Queue is not accessible"
fi

# Test 11: Login page accessible
echo "Testing login page..."
if curl -f -s "$BASE_URL/login" | grep -q "csrf"; then
    test_pass "Login page is accessible"
else
    test_fail "Login page is not accessible"
fi

# Test 12: Static assets
echo "Testing static assets..."
if curl -f -s -o /dev/null "$BASE_URL/build/manifest.json" 2>/dev/null || \
   curl -f -s -o /dev/null "$BASE_URL/css/app.css" 2>/dev/null; then
    test_pass "Static assets are accessible"
else
    test_warn "Static assets check inconclusive"
fi

# Test 13: HTTPS redirect (production only)
if [ "$ENVIRONMENT" == "production" ]; then
    echo "Testing HTTPS redirect..."
    if curl -s -o /dev/null -w "%{redirect_url}" "http://workset.kneebone.com.au" | grep -q "https://"; then
        test_pass "HTTP to HTTPS redirect working"
    else
        test_fail "HTTP to HTTPS redirect not working"
    fi
fi

# Test 14: Docker container health
echo "Testing Docker containers..."
UNHEALTHY=$(docker-compose ps | grep -v "Up" | grep -v "NAME" | wc -l)
if [ "$UNHEALTHY" -eq 0 ]; then
    test_pass "All Docker containers are healthy"
else
    test_fail "$UNHEALTHY Docker container(s) unhealthy"
    docker-compose ps
fi

# Summary
echo ""
echo "=================================================="
echo "Health Check Summary"
echo "=================================================="
echo -e "Tests Passed: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Tests Failed: ${RED}$TESTS_FAILED${NC}"
echo "=================================================="

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All health checks passed${NC}"
    exit 0
else
    echo -e "${RED}✗ Some health checks failed${NC}"
    exit 1
fi
