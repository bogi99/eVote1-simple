#!/bin/bash

# Docker PHP Extensions Test Script
# This script verifies that all required PHP extensions are available

echo "🔍 Testing PHP Extensions in Container..."

# Test if container is running
if ! docker compose ps app | grep -q "running"; then
    echo "⚠️  App container is not running. Starting containers..."
    ./vendor/bin/sail up -d
    sleep 5
fi

echo ""
echo "📋 PHP Version and Extensions Check:"
echo "=================================="

# Check PHP version
echo "🔸 PHP Version:"
./vendor/bin/sail exec app php -v

echo ""
echo "🔸 Required Extensions Status:"

# Check each required extension
extensions=("pdo" "pdo_mysql" "json" "mbstring" "xml" "curl" "zip" "intl")

for ext in "${extensions[@]}"; do
    if ./vendor/bin/sail exec app php -m | grep -q "^$ext$"; then
        echo "✅ $ext - Available"
    else
        echo "❌ $ext - Missing"
    fi
done

echo ""
echo "🔸 Database Connection Test:"
echo "=========================="

# Test database connection
./vendor/bin/sail exec app php -r "
try {
    \$pdo = new PDO('mysql:host=mysql;dbname=evote_simple', 'sail', 'password');
    echo '✅ Database connection successful\n';
} catch (Exception \$e) {
    echo '❌ Database connection failed: ' . \$e->getMessage() . '\n';
}
"

echo ""
echo "🔸 Application Test:"
echo "=================="

# Test if the application responds
if curl -s -f http://localhost/ > /dev/null; then
    echo "✅ Application is responding"
else
    echo "❌ Application is not responding"
fi

echo ""
echo "🎉 PHP Extensions Test Complete!"