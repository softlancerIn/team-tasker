<x-admin title="General Settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white">General Settings</h2>
    </div>

    <div class="glass-card">
        <h5 class="text-white mb-4">General Configuration</h5>
        <form action="{{ route('admin.settings.general.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label text-white">Application Name</label>
                <input type="text" name="app_name" class="form-control" value="{{ config('app.name') }}" disabled>
                <div class="form-text text-muted">Updates to core config require deployment access.</div>
            </div>
            <!-- Add more general settings here as needed -->
            <p class="text-muted">More general settings coming soon.</p>

            {{-- <button type="submit" class="btn btn-primary">Save Changes</button> --}}
        </form>
    </div>
</x-admin>
