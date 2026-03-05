<x-admin title="Email Integration Settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0" style="color: var(--text-high);">Email Integration Settings</h2>
    </div>

    <form action="{{ route('admin.settings.email.store') }}" method="POST">
        @csrf
        <div class="glass-card mb-4" style="border: 1px solid var(--border-main);">
            <h5 class="fw-bold mb-4" style="color: var(--text-high);">
                <i class="fas fa-inbox me-2" style="color: var(--primary);"></i> Incoming Mail (IMAP)
            </h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="heading-label mb-2">IMAP Host</label>
                    <input type="text" name="imap_host" class="form-premium-control"
                        value="{{ $settings['imap_host'] ?? '' }}" placeholder="imap.gmail.com" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="heading-label mb-2">Port</label>
                    <input type="number" name="imap_port" class="form-premium-control"
                        value="{{ $settings['imap_port'] ?? '993' }}" placeholder="993" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="heading-label mb-2">Encryption</label>
                    <x-select name="imap_encryption" placeholder="Select Encryption">
                        <option value="ssl" {{ ($settings['imap_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}
                            class="bg-dark">SSL
                        </option>
                        <option value="tls" {{ ($settings['imap_encryption'] ?? '') == 'tls' ? 'selected' : '' }}
                            class="bg-dark">TLS
                        </option>
                        <option value="null" {{ ($settings['imap_encryption'] ?? '') == 'null' ? 'selected' : '' }}
                            class="bg-dark">
                            None</option>
                    </x-select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="heading-label mb-2">Username</label>
                    <input type="text" name="imap_user" class="form-premium-control"
                        value="{{ $settings['imap_user'] ?? '' }}" placeholder="email@example.com" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="heading-label mb-2">Password</label>
                    <input type="password" name="imap_password" class="form-premium-control"
                        value="{{ $settings['imap_password'] ?? '' }}" placeholder="App Password" required>
                </div>
            </div>
        </div>

        <div class="glass-card mb-4" style="border: 1px solid var(--border-main);">
            <h5 class="fw-bold mb-4" style="color: var(--text-high);">
                <i class="fas fa-paper-plane me-2" style="color: var(--primary);"></i> Outgoing Mail (SMTP)
            </h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="heading-label mb-2">SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-premium-control"
                        value="{{ $settings['smtp_host'] ?? '' }}" placeholder="smtp.gmail.com">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="heading-label mb-2">Port</label>
                    <input type="number" name="smtp_port" class="form-premium-control"
                        value="{{ $settings['smtp_port'] ?? '587' }}" placeholder="587">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="heading-label mb-2">Encryption</label>
                    <x-select name="smtp_encryption" placeholder="Select Encryption">
                        <option value="tls" {{ ($settings['smtp_encryption'] ?? '') == 'tls' ? 'selected' : '' }}
                            class="bg-dark">
                            TLS</option>
                        <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}
                            class="bg-dark">
                            SSL</option>
                        <option value="null" {{ ($settings['smtp_encryption'] ?? '') == 'null' ? 'selected' : '' }}
                            class="bg-dark">
                            None</option>
                    </x-select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="heading-label mb-2">Username</label>
                    <input type="text" name="smtp_user" class="form-premium-control"
                        value="{{ $settings['smtp_user'] ?? '' }}" placeholder="email@example.com">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="heading-label mb-2">Password</label>
                    <input type="password" name="smtp_password" class="form-premium-control"
                        value="{{ $settings['smtp_password'] ?? '' }}" placeholder="App Password">
                </div>
            </div>
        </div>

        <div class="glass-card mb-4" style="border: 1px solid var(--border-main);">
            <h5 class="fw-bold mb-4" style="color: var(--text-high);">Sender Identity</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="heading-label mb-2">From Name</label>
                    <input type="text" name="from_name" class="form-premium-control"
                        value="{{ $settings['from_name'] ?? config('app.name') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="heading-label mb-2">From Email</label>
                    <input type="email" name="from_email" class="form-premium-control"
                        value="{{ $settings['from_email'] ?? '' }}">
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn-premium btn-premium-primary px-5">
                <i class="fas fa-save me-2"></i>Save Email Settings
            </button>
        </div>
    </form>
</x-admin>
