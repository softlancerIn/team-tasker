<x-admin title="General Settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0" style="color: var(--text-high);">General Settings</h2>
    </div>

    <div class="glass-card" style="border: 1px solid var(--border-main);">
        <h5 class="fw-bold mb-4" style="color: var(--text-high);">General Configuration</h5>
        <form action="{{ route('admin.settings.general.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="heading-label mb-2">Application Name</label>
                <input type="text" name="app_name" class="form-premium-control" value="{{ config('app.name') }}"
                    disabled>
                <div class="text-low small mt-2">Updates to core config require deployment access.</div>
            </div>
            <!-- Add more general settings here as needed -->
            <p class="text-low mt-4 mb-0"><i class="fas fa-clock me-2" style="color: var(--primary);"></i>More general
                settings coming soon.</p>
        </form>
    </div>
</x-admin>
