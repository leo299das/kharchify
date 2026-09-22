<svg viewBox="0 0 100 85" fill="none" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <defs>
        <linearGradient id="comp-icon-left-{{ $id ?? 'main' }}" x1="0%" y1="100%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#047857" />
            <stop offset="100%" stop-color="#059669" />
        </linearGradient>

        <linearGradient id="comp-icon-mid-{{ $id ?? 'main' }}" x1="0%" y1="100%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#059669" />
            <stop offset="100%" stop-color="#10b981" />
        </linearGradient>

        <linearGradient id="comp-icon-arrow-{{ $id ?? 'main' }}" x1="0%" y1="100%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#0d9488" />
            <stop offset="50%" stop-color="#10b981" />
            <stop offset="100%" stop-color="#34d399" />
        </linearGradient>

        <linearGradient id="comp-icon-dark-{{ $id ?? 'main' }}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#065f46" />
            <stop offset="100%" stop-color="#022c22" />
        </linearGradient>
    </defs>

    <g>
        <!-- Left Parallelogram Bar -->
        <path d="M 6 72 L 24 72 L 40 38 L 22 38 Z" fill="url(#comp-icon-left-{{ $id ?? 'main' }})" />

        <!-- Middle Main Slope Body -->
        <path d="M 28 72 L 46 72 L 72 20 L 54 20 Z" fill="url(#comp-icon-mid-{{ $id ?? 'main' }})" />

        <!-- Bottom Dark Emerald Triangle Facet -->
        <path d="M 50 72 L 74 72 L 62 48 Z" fill="url(#comp-icon-dark-{{ $id ?? 'main' }})" />

        <!-- Top Rising Arrow Head -->
        <path d="M 43 32 L 80 32 L 72 6 Z" fill="url(#comp-icon-arrow-{{ $id ?? 'main' }})" />
        
        <!-- Arrow Body Connection Highlight -->
        <path d="M 39 50 L 58 50 L 70 24 L 51 24 Z" fill="url(#comp-icon-arrow-{{ $id ?? 'main' }})" opacity="0.95" />
    </g>
</svg>
