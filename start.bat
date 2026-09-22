@echo off
title Kharchify - Starting Application
echo Starting Kharchify Expense Tracker...
echo Opening http://127.0.0.1:8000 in your browser...

:: Add XAMPP PHP to PATH if needed
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    )
)

timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000
php artisan serve --port=8000
pause
