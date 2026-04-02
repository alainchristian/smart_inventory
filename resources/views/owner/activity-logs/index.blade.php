<x-app-layout>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.querySelector('[data-page-title]');
            if (el) el.textContent = 'Activity Logs';
        });
    </script>
    @endpush

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
            <h1 style="margin:0;font-size:1.35rem;font-weight:700;color:var(--text-main)">Activity Logs</h1>
            <p style="margin:4px 0 0;font-size:.85rem;color:var(--text-sub)">Full audit trail of all system actions</p>
        </div>
    </div>

    <livewire:owner.activity-logs />

</x-app-layout>
