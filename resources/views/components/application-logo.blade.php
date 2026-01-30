<svg {{ $attributes }} viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <!-- Main Box Structure -->
    <defs>
        <linearGradient id="boxGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#1D4ED8;stop-opacity:1" />
        </linearGradient>
        <linearGradient id="accentGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#10B981;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
        </linearGradient>
    </defs>

    <!-- Large Box (Bottom) -->
    <g>
        <!-- Box Front -->
        <path d="M 60 110 L 140 110 L 140 170 L 60 170 Z" fill="url(#boxGradient)" stroke="#1E40AF" stroke-width="2"/>
        <!-- Box Top -->
        <path d="M 60 110 L 100 85 L 180 85 L 140 110 Z" fill="#60A5FA" stroke="#1E40AF" stroke-width="2"/>
        <!-- Box Side -->
        <path d="M 140 110 L 180 85 L 180 145 L 140 170 Z" fill="#2563EB" stroke="#1E40AF" stroke-width="2"/>

        <!-- Barcode detail -->
        <rect x="70" y="130" width="60" height="25" fill="white" opacity="0.9" rx="2"/>
        <line x1="75" y1="135" x2="75" y2="150" stroke="#1E40AF" stroke-width="1.5"/>
        <line x1="80" y1="135" x2="80" y2="150" stroke="#1E40AF" stroke-width="2"/>
        <line x1="85" y1="135" x2="85" y2="150" stroke="#1E40AF" stroke-width="1"/>
        <line x1="88" y1="135" x2="88" y2="150" stroke="#1E40AF" stroke-width="2"/>
        <line x1="93" y1="135" x2="93" y2="150" stroke="#1E40AF" stroke-width="1"/>
        <line x1="97" y1="135" x2="97" y2="150" stroke="#1E40AF" stroke-width="2.5"/>
        <line x1="102" y1="135" x2="102" y2="150" stroke="#1E40AF" stroke-width="1"/>
        <line x1="106" y1="135" x2="106" y2="150" stroke="#1E40AF" stroke-width="1.5"/>
        <line x1="110" y1="135" x2="110" y2="150" stroke="#1E40AF" stroke-width="2"/>
        <line x1="115" y1="135" x2="115" y2="150" stroke="#1E40AF" stroke-width="1"/>
        <line x1="120" y1="135" x2="120" y2="150" stroke="#1E40AF" stroke-width="2"/>
    </g>

    <!-- Medium Box (Middle) -->
    <g>
        <!-- Box Front -->
        <path d="M 30 70 L 90 70 L 90 115 L 30 115 Z" fill="url(#accentGradient)" stroke="#047857" stroke-width="2"/>
        <!-- Box Top -->
        <path d="M 30 70 L 60 50 L 120 50 L 90 70 Z" fill="#34D399" stroke="#047857" stroke-width="2"/>
        <!-- Box Side -->
        <path d="M 90 70 L 120 50 L 120 95 L 90 115 Z" fill="#10B981" stroke="#047857" stroke-width="2"/>

        <!-- Handle detail -->
        <rect x="50" y="82" width="20" height="3" fill="white" opacity="0.8" rx="1.5"/>
    </g>

    <!-- Small Box (Top) -->
    <g>
        <!-- Box Front -->
        <path d="M 110 35 L 160 35 L 160 75 L 110 75 Z" fill="#FBBF24" stroke="#D97706" stroke-width="2"/>
        <!-- Box Top -->
        <path d="M 110 35 L 135 20 L 185 20 L 160 35 Z" fill="#FCD34D" stroke="#D97706" stroke-width="2"/>
        <!-- Box Side -->
        <path d="M 160 35 L 185 20 L 185 60 L 160 75 Z" fill="#F59E0B" stroke="#D97706" stroke-width="2"/>

        <!-- Checkmark -->
        <circle cx="135" cy="55" r="12" fill="white" opacity="0.9"/>
        <path d="M 130 55 L 133 58 L 140 51" stroke="#10B981" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    </g>

    <!-- Modern accent line -->
    <line x1="20" y1="180" x2="180" y2="180" stroke="#3B82F6" stroke-width="3" stroke-linecap="round" opacity="0.3"/>
</svg>
