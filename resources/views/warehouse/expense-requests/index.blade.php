<x-app-layout>
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold" style="color:var(--text);">Expense Requests</h1>
                <p class="mt-1 text-xs" style="color:var(--text-dim);">Request cash from shop locations</p>
            </div>

            <livewire:warehouse.expense-requests.create-request />
        </div>
    </div>
</x-app-layout>
