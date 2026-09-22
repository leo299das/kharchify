@echo off
setlocal enabledelayedexpansion
title Kharchify - Expense Tracker Project Setup

echo ================================================================
echo           KHARCHIFY - EXPENSE TRACKER SETUP WIZARD
echo ================================================================
echo.

:: 1. Check PHP
echo [1/8] Checking PHP environment...
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
        echo       Found PHP in C:\xampp\php
    ) else (
        echo [ERROR] PHP is not installed or not in your PATH.
        echo         Please install PHP or XAMPP and add PHP to your PATH.
        pause
        exit /b 1
    )
)
php -v | findstr /i "PHP"
echo.

:: 2. Check Composer
echo [2/8] Checking Composer...
where composer >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Composer is not found in PATH.
    echo         Please install Composer from https://getcomposer.org/
    pause
    exit /b 1
)
call composer --version
echo.

:: 3. Check Node & NPM
echo [3/8] Checking Node.js and NPM...
where npm >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [WARNING] Node.js / NPM not found in PATH. Frontend assets might not build.
    echo           Please install Node.js from https://nodejs.org/ if needed.
) else (
    call node -v
    call npm -v
)
echo.

:: 4. Setup .env file
echo [4/8] Configuring Environment (.env)...
if not exist ".env" (
    if exist ".env.example" (
        copy .env.example .env >nul
        echo       Created .env from .env.example
    ) else (
        echo [ERROR] .env.example file not found.
        pause
        exit /b 1
    )
) else (
    echo       .env already exists. Keeping current configuration.
)
echo.

:: 5. Install PHP Dependencies
echo [5/8] Installing Composer dependencies (this may take a minute)...
call composer install --no-interaction --prefer-dist --optimize-autoloader
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Composer install failed. Please check error output above.
    pause
    exit /b 1
)
echo.

:: 6. Generate Application Key & Storage Link
echo [6/8] Generating Application Key & Storage Link...
call php artisan key:generate --force
call php artisan storage:link
echo.

:: 7. Install NPM Dependencies & Build Assets
echo [7/8] Installing NPM packages and building frontend assets...
where npm >nul 2>nul
if %ERRORLEVEL% equ 0 (
    call npm install --no-audit --no-fund
    call npm run build
) else (
    echo       Skipping NPM build because npm was not found.
)
echo.

:: 8. Database Setup and Migrations
echo [8/8] Setting up Database...
echo       Running migrations and default seeders...
call php artisan migrate --force
if %ERRORLEVEL% neq 0 (
    echo [WARNING] Standard migration failed. If database doesn't exist yet,
    echo           make sure your database (e.g. 'kharchify' in MySQL) is created in phpMyAdmin/XAMPP.
    echo           You can also run: php artisan migrate:fresh --seed
) else (
    call php artisan db:seed --force
)
echo.

:: Clear and cache configurations
call php artisan optimize:clear

echo ================================================================
echo             SETUP COMPLETED SUCCESSFULLY!
echo ================================================================
echo.
echo Default Demo Credentials:
echo   Email:    test@example.com
echo   Password: password
echo.
echo To start the application, you can:
echo   1. Double click 'start.bat'
echo   2. Or run: php artisan serve
echo.

set /p START_NOW="Would you like to start the server now? (Y/N): "
if /i "%START_NOW%"=="Y" (
    echo Starting server at http://127.0.0.1:8000 ...
    start http://127.0.0.1:8000
    call php artisan serve
) else (
    echo You can start the server anytime with start.bat or 'php artisan serve'.
    pause
)
