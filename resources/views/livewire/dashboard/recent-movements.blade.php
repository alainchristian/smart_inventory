<div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl">
    <!-- Header -->
    <div class="p-4 sm:p-5 border-b" style="border-color: var(--border);">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-[15px] font-bold" style="color: var(--text);">Box Movements</h2>
                <p class="text-xs mt-0.5" style="color: var(--text-sub);">Recent inventory activity</p>
            </div>
            <a href="{{ route('owner.boxes.index') }}"
               class="text-[13px] font-semibold px-2.5 py-1.5 rounded-lg transition-colors"
               style="color: var(--accent); background: var(--accent-dim);">
                View All
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm recent-movements-table">
            <thead style="background: var(--surface2); border-bottom: 1px solid var(--border);">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('box_code')" class="flex items-center gap-1 text-[10.5px] font-bold uppercase tracking-wider transition-colors"
                                style="color: var(--text-dim);">
                            Box Code
                            @if($sortField === 'box_code')
                                <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left text-[10.5px] font-bold uppercase tracking-wider" style="color: var(--text-dim);">
                        Product
                    </th>
                    <th class="px-4 py-3 text-center">
                        <button wire:click="sortBy('status')" class="flex items-center justify-center gap-1 text-[10.5px] font-bold uppercase tracking-wider transition-colors mx-auto"
                                style="color: var(--text-dim);">
                            Status
                            @if($sortField === 'status')
                                <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left text-[10.5px] font-bold uppercase tracking-wider" style="color: var(--text-dim);">
                        Location
                    </th>
                    <th class="px-4 py-3 text-right text-[10.5px] font-bold uppercase tracking-wider" style="color: var(--text-dim);">
                        Items Remaining
                    </th>
                    <th class="px-4 py-3 text-right">
                        <button wire:click="sortBy('updated_at')" class="flex items-center justify-end gap-1 text-[10.5px] font-bold uppercase tracking-wider transition-colors ml-auto"
                                style="color: var(--text-dim);">
                            Last Moved
                            @if($sortField === 'updated_at')
                                <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                            @endif
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody style="border-top: 1px solid var(--border);">
                @forelse($boxes as $box)
                    <tr class="transition-colors cursor-pointer border-b" style="border-color: var(--border);"
                        onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                        <!-- Box Code -->
                        <td class="px-4 py-3.5">
                            <span class="text-[14px] font-semibold" style="font-family: var(--mono); color: var(--accent);">
                                {{ $box->box_code }}
                            </span>
                        </td>

                        <!-- Product -->
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded flex items-center justify-center flex-shrink-0" style="background: var(--surface2);">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--text-dim);">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[14px] font-medium truncate" style="color: var(--text);">{{ $box->product->name }}</div>
                                    <div class="text-[12px] mt-0.5" style="font-family: var(--mono); color: var(--text-dim);">{{ $box->product->sku }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3.5">
                            <div class="flex justify-center">
                                <span class="status-pill {{ $box->status->value }}">
                                    {{ ucfirst($box->status->value) }}
                                </span>
                            </div>
                        </td>

                        <!-- Location -->
                        <td class="px-4 py-3.5">
                            <div class="text-[14px]" style="color: var(--text-sub);">{{ $box->location->name ?? 'N/A' }}</div>
                            <div class="text-[12px] mt-0.5" style="color: var(--text-dim);">{{ ucfirst($box->location_type->value) }}</div>
                        </td>

                        <!-- Items Remaining -->
                        <td class="px-4 py-3.5">
                            <div class="flex flex-col items-end gap-1.5">
                                <span class="text-[14px] font-semibold" style="font-family: var(--mono); color: var(--text);">
                                    {{ $box->items_remaining }}<span style="color: var(--text-dim);"> / {{ $box->items_per_box }}</span>
                                </span>
                                <!-- Mini Progress Bar -->
                                <div class="w-20 h-1.5 rounded-full overflow-hidden" style="background: var(--surface2);">
                                    @php
                                        $percentage = $box->items_per_box > 0 ? ($box->items_remaining / $box->items_per_box) * 100 : 0;
                                        $barColor = $percentage > 50 ? 'var(--success)' : ($percentage > 20 ? 'var(--amber)' : 'var(--red)');
                                    @endphp
                                    <div class="h-full rounded-full transition-all" style="width: {{ $percentage }}%; background: {{ $barColor }};"></div>
                                </div>
                            </div>
                        </td>

                        <!-- Last Moved -->
                        <td class="px-4 py-3.5 text-right">
                            <div class="text-[12px]" style="color: var(--text-dim); font-family: var(--mono);">{{ $box->updated_at->diffForHumans() }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--text-sub);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-sm" style="color: var(--text-dim);">No box movements found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($boxes->hasPages())
        <div class="p-4 border-t" style="border-color: var(--border);">
            {{ $boxes->links() }}
        </div>
    @endif
</div>
