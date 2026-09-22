<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kharchify') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <!-- PWA & Mobile Meta Tags -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#059669">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Kharchify">
        <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
            /* Modern Animated Background */
            .guest-bg {
                background-color: #f8fafc;
                background-image: 
                    radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                    radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.1) 0px, transparent 50%),
                    radial-gradient(at 100% 100%, rgba(245, 158, 11, 0.1) 0px, transparent 50%),
                    radial-gradient(at 0% 100%, rgba(99, 102, 241, 0.1) 0px, transparent 50%);
                background-attachment: fixed;
            }

            /* Logo Animation */
            @keyframes bounceIn {
                0% { opacity: 0; transform: scale(0.3); }
                50% { opacity: 1; transform: scale(1.05); }
                70% { transform: scale(0.9); }
                100% { transform: scale(1); }
            }
            .animate-logo { animation: bounceIn 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased guest-bg">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            
            <div class="animate-logo mb-6">
                <a href="/" class="flex flex-col items-center group transition transform hover:scale-105">
                    <x-application-logo size="large" />
                </a>
            </div>

            <div class="w-full sm:max-w-lg mt-2">
                {{ $slot }}
            </div>

            <div class="mt-8 text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em]">
                Secure & Smart Expense Tracking
            </div>
        </div>

        <!-- Register Service Worker for PWA -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((reg) => console.log('Kharchify SW active:', reg.scope))
                        .catch((err) => console.warn('Kharchify SW failed:', err));
                });
            }
        </script>
    </body>
</html>