<x-admin title="Create New Ticket">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white">Create New Ticket</h2>
        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card">
                <form action="{{ route('admin.tickets.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-white">Client <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="client_id" id="clientSelect" class="form-select" required
                                onchange="updateClientLink()">
                                <option value="">Select Client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }} ({{ $client->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required
                                value="{{ old('subject') }}" placeholder="Brief description of the issue">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select" required>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High
                                    </option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Assign To</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">Unassigned</option>
                                    @foreach ($staff as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Description <span class="text-danger">*</span></label>
                            <textarea id="ticket-body" name="body" class="form-control" rows="8"></textarea>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-paper-plane me-1"></i> Create Ticket
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            updateClientLink(); // Initialize on load
        });

        function updateClientLink() {
            const select = document.getElementById('clientSelect');
            const link = document.getElementById('editClientLink');
            const clientId = select.value;

            if (clientId) {
                // Construct the URL.
                const url = "{{ route('admin.clients.edit', ':id') }}".replace(':id', clientId);
                link.href = url;
                link.classList.remove('d-none');
            } else {
                link.classList.add('d-none');
                link.href = '#';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            const isDark = savedTheme === 'dark';

            tinymce.init({
                selector: '#ticket-body',
                height: 300,
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? 'dark' : 'default',
                menubar: false,
                statusbar: false,
                branding: false,
                plugins: 'autolink lists link image charmap preview anchor',
                toolbar: 'undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
                content_style: isDark ?
                    'body { background: transparent; color: rgba(255, 255, 255, 0.8); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; margin: 10px; }' :
                    'body { background: transparent; color: #333; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; margin: 10px; }'
            });
        });
    </script>
</x-admin>
