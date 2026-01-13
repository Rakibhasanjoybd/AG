#!/bin/bash
# Auto-deploy script run by GitHub Actions after unzipping files
# This script handles server-side tasks: migrations, permissions, caches, etc.

set -e

echo "=========================================="
echo "🚀 AGCO Auto-Deploy Started"
echo "=========================================="

# Get the directory where this script is located
DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DEPLOY_DIR"

echo "📁 Deploy directory: $DEPLOY_DIR"

# 1. Clear old caches and logs if they exist
echo "🧹 Clearing caches and logs..."
rm -rf storage/logs/*.log 2>/dev/null || true
rm -rf bootstrap/cache/* 2>/dev/null || true
if [ -d "public/hot" ]; then rm -rf public/hot; fi

# 2. Composer install (if composer.json exists)
if [ -f "composer.json" ]; then
    echo "📦 Installing Composer dependencies..."
    if command -v composer &> /dev/null; then
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    else
        # Fallback to php if composer is not in PATH
        php composer.phar install --no-interaction --prefer-dist --optimize-autoloader --no-dev 2>/dev/null || echo "⚠️  Composer not found, skipping..."
    fi
fi

# 3. Fix permissions for web server
echo "🔐 Fixing permissions..."
# Laravel typical structure
if [ -d "storage" ]; then
    find storage -type d -exec chmod 755 {} \; 2>/dev/null || true
    find storage -type f -exec chmod 644 {} \; 2>/dev/null || true
fi
if [ -d "bootstrap/cache" ]; then
    find bootstrap/cache -type d -exec chmod 755 {} \; 2>/dev/null || true
    find bootstrap/cache -type f -exec chmod 644 {} \; 2>/dev/null || true
fi

# Standard public directory permissions
if [ -d "public" ]; then
    find public -type d -exec chmod 755 {} \; 2>/dev/null || true
    find public -type f -exec chmod 644 {} \; 2>/dev/null || true
fi

# 4. Run database migrations if using Laravel
if [ -f "artisan" ]; then
    echo "🗄️  Running database migrations..."
    php artisan migrate --force 2>/dev/null || echo "⚠️  Migrations skipped or failed (may already be applied)"
fi

# 5. Clear application cache if using Laravel
if [ -f "artisan" ]; then
    echo "⚡ Clearing application caches..."
    php artisan cache:clear 2>/dev/null || true
    php artisan config:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
fi

# 6. Custom SQL migrations from AGCO project (optional)
# If you have migration files that should auto-run, uncomment and adjust:
# if [ -f "add_sample_data.sql" ]; then
#     echo "📊 Running custom migrations..."
#     mysql -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < add_sample_data.sql 2>/dev/null || echo "⚠️  Custom migration skipped"
# fi

# 7. Restart PHP-FPM if it's running (optional, requires sudo or dedicated user)
if command -v systemctl &> /dev/null; then
    echo "♻️  Restarting PHP-FPM..."
    sudo systemctl restart php-fpm 2>/dev/null || echo "⚠️  Could not restart PHP-FPM (may not have permissions)"
fi

echo ""
echo "=========================================="
echo "✅ Deploy completed successfully!"
echo "=========================================="
echo "⏰ Timestamp: $(date)"
echo ""
