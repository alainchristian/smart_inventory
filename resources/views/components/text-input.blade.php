@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md shadow-sm']) }} style="background: var(--surface2); border: 1px solid var(--border); color: var(--text); padding: 0.5rem 0.75rem;" onfocus="this.style.borderColor='var(--accent)'; this.style.outline='none';" onblur="this.style.borderColor='var(--border)';">

