<x-admin title="Create New Ticket">
    <div class="sticky-header shadow-sm rounded-3 d-flex justify-content-between align-items-center px-4 py-3" style="position: sticky; top: 65px; z-index: 100; background: var(--bg-surface); border: 1px solid var(--border-main);">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; border-color: var(--border-subtle);">
                <i class="fas fa-chevron-left text-high"></i>
            </a>
            <div>
                <h2 class="h5 fw-bold mb-0 text-high">Create New Ticket</h2>
                <p class="text-low mb-0" style="font-size: 0.8rem;">Open a new support ticket.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tickets.index') }}" class="btn px-4" style="border: 1px solid var(--border-subtle); color: var(--text-high); background: transparent;">Cancel</a>
            <button type="submit" form="createTicketForm" class="btn btn-primary px-4 fw-medium">
                Create Ticket
            </button>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card">
                <form id="createTicketForm" action="{{ route('admin.tickets.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Client <span class="text-danger">*</span></label>
                        <div>
                            <x-select name="client_id" id="clientSelect" onchange="updateClientLink()"
                                placeholder="Select Client">
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
                                <x-select name="priority" required placeholder="Select Priority">
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
                                <x-select name="assigned_to" placeholder="Unassigned">
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
