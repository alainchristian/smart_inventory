<x-app-layout>
    <div class="py-2 sm:py-6 lg:py-8">
        <div class="max-w-5xl mx-auto px-0 sm:px-4 lg:px-8">

            <div class="mb-4 sm:mb-6 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold" style="color:var(--text);">Transfer Requests</h1>
                    <p class="text-sm mt-0.5" style="color:var(--text-dim);">Manage incoming requests from shops</p>
                </div>
            </div>

            <livewire:warehouse-manager.transfers.transfers-list />

        </div>
    </div>
</x-app-layout>
