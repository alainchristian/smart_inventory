import './bootstrap';

// Self-hosted fonts (were Google Fonts): same origin, cached with /build
import '@fontsource/dm-sans/300.css';
import '@fontsource/dm-sans/400.css';
import '@fontsource/dm-sans/500.css';
import '@fontsource/dm-sans/600.css';
import '@fontsource/dm-sans/700.css';
import '@fontsource/dm-sans/800.css';
import '@fontsource/dm-mono/400.css';
import '@fontsource/dm-mono/500.css';

// Wait for Alpine (bundled with Livewire) to be available
document.addEventListener('alpine:init', () => {
    // Get the Alpine instance from Livewire
    const Alpine = window.Alpine;

    // Initialize theme store (light theme only)
    Alpine.store('theme', {
        current: 'light'
    });

    // Set light theme on page load
    document.documentElement.setAttribute('data-theme', 'light');
    localStorage.setItem('theme', 'light');
});
