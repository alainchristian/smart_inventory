<x-app-layout>
    <div class="mb-4 sm:mb-6 flex items-center justify-between gap-3 flex-wrap">
        <div>
            <h1 class="text-xl sm:text-3xl font-bold" style="color:var(--text);">Transfer Requests</h1>
            <p class="text-xl mt-0.5" style="color:var(--text-dim);">Manage incoming requests from shops</p>
        </div>
    </div>

    <livewire:warehouse-manager.transfers.transfers-list />
</x-app-layout>
