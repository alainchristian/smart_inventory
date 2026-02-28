import './bootstrap';

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
