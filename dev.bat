@echo off
title Kharchify - Development Server (Server + Vite)
echo Starting Kharchify Development Environment...

:: Add XAMPP PHP to PATH if needed
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    )
)

echo Starting PHP Artisan Server on http://127.0.0.1:8000 and Vite Dev Server...
timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000
npx concurrently -k -n "LARAVEL,VITE" -c "blue,magenta" "php artisan serve" "npm run dev"
pause
