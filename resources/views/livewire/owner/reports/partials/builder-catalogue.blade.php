{{-- Block catalogue for the report builder (styles: rb- in report-builder.blade.php) --}}
<div class="rb-cat-top">
    <input class="rb-input" wire:model.live.debounce.250ms="catalogueSearch" placeholder="Search blocks…" aria-label="Search blocks">
    <div class="rb-domains">
        <button type="button" class="rb-domain {{ $catalogueDomain === 'all' ? 'active' : '' }}" wire:click="$set('catalogueDomain', 'all')">All</button>
        @foreach ($domains as $d)
            <button type="button" class="rb-domain {{ $catalogueDomain === $d ? 'active' : '' }}" wire:click="$set('catalogueDomain', '{{ $d }}')">{{ $domainMeta[$d][0] ?? ucfirst($d) }}</button>
        @endforeach
    </div>
</div>
<div class="rb-cat-list">
    @forelse ($catalogueGroups as $domain => $items)
        <div class="rb-cat-group">{{ $domainMeta[$domain][0] ?? ucfirst($domain) }}</div>
        @foreach ($items as $item)
            <button type="button" class="rb-cat-item" wire:click="addBlock('{{ $item['id'] }}')" wire:key="cat-{{ $item['id'] }}" title="Add to the report">
                <span class="rb-cat-name">
                    <span>{{ $item['label'] }}</span>
                    @if (($usedIds[$item['id']] ?? 0) > 0)
                        <span class="rb-used">Added{{ $usedIds[$item['id']] > 1 ? ' ×' . $usedIds[$item['id']] : '' }}</span>
                    @else
                        <svg class="rb-add-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    @endif
                </span>
                <span class="rb-cat-desc">{{ $item['description'] }}</span>
                <span class="rb-cat-chips">
                    @foreach ($item['viz_options'] as $v)
                        <span class="rb-chip">{{ $vizLabel[$v] ?? $v }}</span>
                    @endforeach
                    @if (! $item['needs_dates'] && $item['id'] !== 'text_block')
                        <span class="rb-chip" title="{{ $item['period_note'] ?? '' }}">Ignores period</span>
                    @endif
                </span>
            </button>
        @endforeach
    @empty
        <div class="rb-canvas-note" style="padding:20px 14px;text-align:center">No blocks match "{{ $catalogueSearch }}".</div>
    @endforelse
</div>
