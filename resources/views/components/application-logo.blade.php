@props(['theme' => 'dark', 'size' => 'default'])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 select-none shrink-0']) }}>
    <!-- Geometric Growth Icon Mark -->
    <div class="shrink-0 flex items-center justify-center">
        <svg viewBox="0 0 100 85" fill="none" xmlns="http://www.w3.org/2000/svg" class="{{ $size === 'large' ? 'h-9 w-auto sm:h-11' : ($size === 'small' ? 'h-6 w-auto' : 'h-7 w-auto sm:h-8') }}" style="display: block;">
            <defs>
                <linearGradient id="logo-g-left" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#047857" />
                    <stop offset="100%" stop-color="#059669" />
                </linearGradient>
                <linearGradient id="logo-g-mid" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#059669" />
                    <stop offset="100%" stop-color="#10b981" />
                </linearGradient>
                <linearGradient id="logo-g-arrow" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#0d9488" />
                    <stop offset="50%" stop-color="#10b981" />
                    <stop offset="100%" stop-color="#34d399" />
                </linearGradient>
                <linearGradient id="logo-g-dark" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#065f46" />
                    <stop offset="100%" stop-color="#022c22" />
                </linearGradient>
            </defs>
            <g>
                <path d="M 6 72 L 24 72 L 40 38 L 22 38 Z" fill="url(#logo-g-left)" />
                <path d="M 28 72 L 46 72 L 72 20 L 54 20 Z" fill="url(#logo-g-mid)" />
                <path d="M 50 72 L 74 72 L 62 48 Z" fill="url(#logo-g-dark)" />
                <path d="M 43 32 L 80 32 L 72 6 Z" fill="url(#logo-g-arrow)" />
                <path d="M 39 50 L 58 50 L 70 24 L 51 24 Z" fill="url(#logo-g-arrow)" opacity="0.95" />
            </g>
        </svg>
    </div>

    <!-- Bold Wordmark -->
    <span class="font-black tracking-tight uppercase leading-none {{ $theme === 'white' ? 'text-white' : 'text-slate-900' }} {{ $size === 'large' ? 'text-2xl sm:text-3xl' : ($size === 'small' ? 'text-base sm:text-lg' : 'text-lg sm:text-xl') }}" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        KHARCHIFY
    </span>
</div>
