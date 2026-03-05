<x-admin title="General Settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0" style="color: var(--text-high);">General Settings</h2>
    </div>

    <div class="glass-card" style="border: 1px solid var(--border-main);">
        <h5 class="fw-bold mb-4" style="color: var(--text-high);">General Configuration</h5>
        <form action="{{ route('admin.settings.general.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="heading-label mb-2">Application Name</label>
                <input type="text" name="app_name" class="form-premium-control"
                    value="{{ $settings['app_name'] ?? config('app.name') }}" required>
                <div class="text-low small mt-2">This name appears in the main sidebar header.</div>
            </div>

            <div class="mb-4">
                <label class="heading-label mb-2">Project Logo</label>
                <div class="d-flex align-items-center gap-4">
                    <div class="avatar-premium shadow-premium"
                        style="width: 80px; height: 80px; background: var(--bg-surface); border: 2px solid var(--border-subtle);">
                        @if (isset($settings['app_logo']) && $settings['app_logo'])
                            <img src="{{ asset('storage/' . $settings['app_logo']) }}" alt="Logo"
                                style="width: 100%; height: 100%; object-fit: contain; border-radius: inherit;">
                        @else
                            <i class="fas fa-layer-group text-medium" style="font-size: 2rem;"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" name="app_logo" class="form-premium-control" accept="image/*">
                        <div class="text-low small mt-2">Recommended size: 256x256px (Max 2MB).</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-premium btn-premium-primary px-4 py-2 mt-2">
                <i class="fas fa-save me-2"></i> Save Settings
            </button>
        </form>
    </div>
</x-admin>
