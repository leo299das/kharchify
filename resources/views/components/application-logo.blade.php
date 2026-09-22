@props(['theme' => 'dark', 'size' => 'default'])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 select-none shrink-0']) }}>
    <!-- Geometric Growth Icon Mark -->
    <img src="{{ asset('images/logo-icon.svg') }}" alt="Kharchify Logo" class="{{ $size === 'large' ? 'h-9 w-9 sm:h-11 sm:w-11' : ($size === 'small' ? 'h-7 w-7' : 'h-8 w-8') }} shrink-0 object-contain" />

    <!-- Bold Wordmark -->
    <span class="font-black tracking-tight uppercase leading-none {{ $theme === 'white' ? 'text-white' : 'text-slate-900' }} {{ $size === 'large' ? 'text-2xl sm:text-3xl' : ($size === 'small' ? 'text-lg sm:text-xl' : 'text-xl sm:text-2xl') }}" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        KHARCHIFY
    </span>
</div>
