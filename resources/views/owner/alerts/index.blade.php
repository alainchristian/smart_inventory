<x-app-layout>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.querySelector('[data-page-title]');
            if (el) el.textContent = 'Alerts';
        });
    </script>
    @endpush

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
            <h1 style="margin:0;font-size:1.35rem;font-weight:700;color:var(--text-main)">Alerts</h1>
            <p style="margin:4px 0 0;font-size:.85rem;color:var(--text-sub)">Monitor and manage system alerts</p>
        </div>
    </div>

    <livewire:owner.alerts />

</x-app-layout>
