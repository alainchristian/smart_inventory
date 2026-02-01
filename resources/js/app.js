import './bootstrap';
import collapse from '@alpinejs/collapse';

// Register Alpine plugins
document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(collapse);
});
