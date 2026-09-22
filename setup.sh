#!/usr/bin/env bash
# ================================================================
#           Kharchify - Expense Tracker Setup Script (Bash)
# ================================================================

set -e

echo "================================================================"
echo "           KHARCHIFY - EXPENSE TRACKER SETUP WIZARD             "
echo "================================================================"
echo ""

# 1. PHP Check
echo "[1/8] Checking PHP environment..."
if ! command -v php &> /dev/null; then
    echo "[ERROR] PHP is not installed or not in your PATH."
    exit 1
fi
php -v | head -n 1
echo ""

# 2. Composer Check
echo "[2/8] Checking Composer..."
if ! command -v composer &> /dev/null; then
    echo "[ERROR] Composer is not installed or not in your PATH."
    exit 1
fi
composer --version
echo ""

# 3. Node & NPM Check
echo "[3/8] Checking Node.js and NPM..."
if command -v npm &> /dev/null; then
    echo "      Node.js: $(node -v) | NPM: $(npm -v)"
else
    echo "      [WARNING] npm not found. Skipping frontend assets build."
fi
echo ""

# 4. .env File Setup
echo "[4/8] Configuring Environment (.env)..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "      Created .env from .env.example"
    else
        echo "[ERROR] .env.example file not found."
        exit 1
    fi
else
    echo "      .env already exists. Preserving current settings."
fi
echo ""

# 5. Install Composer dependencies
echo "[5/8] Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader
echo ""

# 6. Generate Application Key & Storage Link
echo "[6/8] Generating Application Key & Storage Link..."
php artisan key:generate --force
php artisan storage:link || true
echo ""

# 7. NPM Dependencies & Build
if command -v npm &> /dev/null; then
    echo "[7/8] Installing NPM packages and building frontend assets..."
    npm install --no-audit --no-fund
    npm run build
    echo ""
else
    echo "[7/8] Skipping NPM build."
    echo ""
fi

# 8. Database Migrations & Seeders
echo "[8/8] Setting up Database..."
php artisan migrate --force
php artisan db:seed --force
echo ""

# Clear & optimize caches
php artisan optimize:clear

echo "================================================================"
echo "             SETUP COMPLETED SUCCESSFULLY!                      "
echo "================================================================"
echo ""
echo "Default Demo Credentials:"
echo "  Email:    test@example.com"
echo "  Password: password"
echo ""
echo "To start the application:"
echo "  php artisan serve"
echo "  and open http://127.0.0.1:8000"
echo ""
