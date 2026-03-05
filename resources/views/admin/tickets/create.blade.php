<x-admin title="Create New Ticket">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-4 h2" style="color: var(--text-high);">Create New Ticket</h2>
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
                        <label class="form-label">Client <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <x-select name="client_id" id="clientSelect" class="form-select" required
                                onchange="updateClientLink()" placeholder="Select Client">
                                <option value="" class="bg-dark">Select Client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ old('client_id') == $client->id ? 'selected' : '' }} class="bg-dark">
                                        {{ $client->name }} ({{ $client->email }})
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required
                                value="{{ old('subject') }}" placeholder="Brief description of the issue">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <x-select name="priority" class="form-select" required placeholder="Select Priority">
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}
                                        class="bg-dark">Low</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}
                                        class="bg-dark">Medium
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}
                                        class="bg-dark">High
                                    </option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}
                                        class="bg-dark">Urgent
                                    </option>
                                </x-select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign To</label>
                                <x-select name="assigned_to" class="form-select" placeholder="Unassigned">
                                    <option value="" class="bg-dark">Unassigned</option>
                                    @foreach ($staff as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('assigned_to') == $user->id ? 'selected' : '' }} class="bg-dark">
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <x-textarea id="ticket-body" name="body" class="form-control" rows="8"
                                texteditor="true"></x-textarea>
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
    </script>
</x-admin>
