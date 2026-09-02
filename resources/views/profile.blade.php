<x-app-layout>
    <div class="dashboard-page-header">
        <div>
            <h1>{{ __('Profile Settings') }}</h1>
            <p>{{ __('Manage your account details, password and security') }}</p>
        </div>
    </div>

    <style>
    .pr-page-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px }
    .pr-page-grid-span2 { grid-column:1 / -1 }
    @media(max-width:900px) {
        .pr-page-grid { grid-template-columns:1fr }
    }
    </style>

    <div class="pr-page-grid">
        <livewire:profile.update-profile-information-form />
        <livewire:profile.update-password-form />
        <div class="pr-page-grid-span2">
            <livewire:profile.delete-user-form />
        </div>
    </div>
</x-app-layout>
