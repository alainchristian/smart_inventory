<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150']) }} style="background: var(--accent); color: white; border: none;" onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';">
    {{ $slot }}
</button>
