<x-admin title="Email Integration Settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white">Email Integration Settings</h2>
    </div>

    <form action="{{ route('admin.settings.email.store') }}" method="POST">
        @csrf
        <div class="glass-card mb-4">
            <h5 class="text-white mb-3"><i class="fas fa-inbox me-2"></i> Incoming Mail (IMAP)</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-white">IMAP Host</label>
                    <input type="text" name="imap_host" class="form-control"
                        value="{{ $settings['imap_host'] ?? '' }}" placeholder="imap.gmail.com" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-white">Port</label>
                    <input type="number" name="imap_port" class="form-control"
                        value="{{ $settings['imap_port'] ?? '993' }}" placeholder="993" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-white">Encryption</label>
                    <select name="imap_encryption" class="form-select">
                        <option value="ssl" {{ ($settings['imap_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL
                        </option>
                        <option value="tls" {{ ($settings['imap_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS
                        </option>
                        <option value="null" {{ ($settings['imap_encryption'] ?? '') == 'null' ? 'selected' : '' }}>
                            None</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-white">Username</label>
                    <input type="text" name="imap_user" class="form-control"
                        value="{{ $settings['imap_user'] ?? '' }}" placeholder="email@example.com" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-white">Password</label>
                    <input type="password" name="imap_password" class="form-control"
                        value="{{ $settings['imap_password'] ?? '' }}" placeholder="App Password" required>
                </div>
            </div>
        </div>

        <div class="glass-card mb-4">
            <h5 class="text-white mb-3"><i class="fas fa-paper-plane me-2"></i> Outgoing Mail (SMTP)</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-white">SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-control"
                        value="{{ $settings['smtp_host'] ?? '' }}" placeholder="smtp.gmail.com">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-white">Port</label>
                    <input type="number" name="smtp_port" class="form-control"
                        value="{{ $settings['smtp_port'] ?? '587' }}" placeholder="587">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-white">Encryption</label>
                    <select name="smtp_encryption" class="form-select">
                        <option value="tls" {{ ($settings['smtp_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>
                            TLS</option>
                        <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>
                            SSL</option>
                        <option value="null" {{ ($settings['smtp_encryption'] ?? '') == 'null' ? 'selected' : '' }}>
                            None</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-white">Username</label>
                    <input type="text" name="smtp_user" class="form-control"
                        value="{{ $settings['smtp_user'] ?? '' }}" placeholder="email@example.com">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-white">Password</label>
                    <input type="password" name="smtp_password" class="form-control"
                        value="{{ $settings['smtp_password'] ?? '' }}" placeholder="App Password">
                </div>
            </div>
        </div>

        <div class="glass-card mb-4">
            <h5 class="text-white mb-3">Sender Identity</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-white">From Name</label>
                    <input type="text" name="from_name" class="form-control"
                        value="{{ $settings['from_name'] ?? config('app.name') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-white">From Email</label>
                    <input type="email" name="from_email" class="form-control"
                        value="{{ $settings['from_email'] ?? '' }}">
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-4">Save Email Settings</button>
        </div>
    </form>
</x-admin>
