# ================================================================
#           Kharchify - Expense Tracker Setup Script (PowerShell)
# ================================================================

param (
    [switch]$FreshDatabase = $false,
    [switch]$SkipNpm = $false,
    [switch]$StartServer = $false
)

Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "           KHARCHIFY - EXPENSE TRACKER SETUP WIZARD             " -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

# 1. PHP Check
Write-Host "[1/8] Checking PHP environment..." -ForegroundColor Yellow
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    if (Test-Path "C:\xampp\php\php.exe") {
        $env:Path = "C:\xampp\php;" + $env:Path
        Write-Host "      Detected PHP in C:\xampp\php and added to session PATH." -ForegroundColor Gray
    } else {
        Write-Error "PHP is not found in PATH or standard XAMPP directory. Please install PHP 8.2+ or XAMPP."
        exit 1
    }
}
php -v | Select-Object -First 1 | Write-Host -ForegroundColor Green

# 2. Composer Check
Write-Host "`n[2/8] Checking Composer..." -ForegroundColor Yellow
$composerCmd = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composerCmd) {
    Write-Error "Composer is not installed or not in PATH. Please install Composer from https://getcomposer.org/"
    exit 1
}
composer --version | Write-Host -ForegroundColor Green

# 3. Node & NPM Check
Write-Host "`n[3/8] Checking Node.js & NPM..." -ForegroundColor Yellow
$nodeCmd = Get-Command node -ErrorAction SilentlyContinue
$npmCmd = Get-Command npm -ErrorAction SilentlyContinue
if ($nodeCmd -and $npmCmd) {
    Write-Host "      Node.js: $(node -v) | NPM: $(npm -v)" -ForegroundColor Green
} else {
    Write-Host "      [WARNING] Node.js/NPM not found. Frontend assets build will be skipped." -ForegroundColor Yellow
    $SkipNpm = $true
}

# 4. .env File Setup
Write-Host "`n[4/8] Configuring Environment (.env)..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "      Created .env from .env.example" -ForegroundColor Green
    } else {
        Write-Error ".env.example not found."
        exit 1
    }
} else {
    Write-Host "      .env already exists. Preserving current settings." -ForegroundColor Gray
}

# 5. Composer Install
Write-Host "`n[5/8] Installing Composer dependencies..." -ForegroundColor Yellow
composer install --no-interaction --prefer-dist --optimize-autoloader
if ($LASTEXITCODE -ne 0) {
    Write-Error "Composer install encountered an error."
    exit $LASTEXITCODE
}

# 6. Key Generation & Storage Link
Write-Host "`n[6/8] Generating Application Key & Storage Link..." -ForegroundColor Yellow
php artisan key:generate --force
php artisan storage:link

# 7. NPM Dependencies & Build
if (-not $SkipNpm) {
    Write-Host "`n[7/8] Installing NPM packages and compiling assets..." -ForegroundColor Yellow
    npm install --no-audit --no-fund
    npm run build
} else {
    Write-Host "`n[7/8] Skipping NPM build." -ForegroundColor Gray
}

# 8. Database Migrations & Seeds
Write-Host "`n[8/8] Setting up Database..." -ForegroundColor Yellow
if ($FreshDatabase) {
    Write-Host "      Running fresh migration and seeders..." -ForegroundColor Gray
    php artisan migrate:fresh --seed --force
} else {
    Write-Host "      Running standard migrations and seeders..." -ForegroundColor Gray
    php artisan migrate --force
    php artisan db:seed --force
}

# Clear and optimize cache
php artisan optimize:clear

Write-Host "`n================================================================" -ForegroundColor Green
Write-Host "             SETUP COMPLETED SUCCESSFULLY!                      " -ForegroundColor Green
Write-Host "================================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Default Demo Credentials:" -ForegroundColor Cyan
Write-Host "  Email:    test@example.com" -ForegroundColor White
Write-Host "  Password: password" -ForegroundColor White
Write-Host ""
Write-Host "To start the application:" -ForegroundColor Cyan
Write-Host "  - Run 'php artisan serve' or execute 'start.bat'" -ForegroundColor White
Write-Host "  - Open browser at http://127.0.0.1:8000" -ForegroundColor White
Write-Host ""

if ($StartServer) {
    Start-Process "http://127.0.0.1:8000"
    php artisan serve
} else {
    $prompt = Read-Host "Would you like to start the Laravel server now? (Y/N)"
    if ($prompt -match "^[Yy]$") {
        Start-Process "http://127.0.0.1:8000"
        php artisan serve
    }
}
