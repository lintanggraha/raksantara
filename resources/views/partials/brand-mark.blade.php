{{-- Raksantara mark: a shield (raksa = to guard) carrying a geometric "R". --}}
@php($brandMarkId = 'rk-grad-' . \Illuminate\Support\Str::random(6))
<svg class="{{ $class ?? 'app-brand-mark' }}" width="{{ $size ?? 28 }}" height="{{ $size ?? 28 }}" viewBox="0 0 24 24" fill="none" role="img" aria-label="Raksantara">
    <defs>
        <linearGradient id="{{ $brandMarkId }}" x1="4" y1="2" x2="20" y2="22" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#2563eb"/>
            <stop offset="1" stop-color="#1e3a8a"/>
        </linearGradient>
    </defs>
    <path d="M12 1.75 20.25 4.75V11.2c0 5.1-3.45 9.1-8.25 11.05C7.2 20.3 3.75 16.3 3.75 11.2V4.75L12 1.75Z" fill="url(#{{ $brandMarkId }})"/>
    <path d="M9.25 16.5V7h3.35a2.6 2.6 0 0 1 0 5.2H9.25M11.9 12.2l3.3 4.3" stroke="#fff" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
