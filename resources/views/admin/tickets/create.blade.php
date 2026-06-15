<x-admin title="Create New Ticket">
    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Create New Ticket</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Open a new support ticket.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tickets.index') }}" class="btn-premium btn-premium-secondary px-4 py-2">Cancel</a>
            <button type="submit" form="createTicketForm" class="btn-premium btn-premium-primary px-4 py-2 shadow-sm">
                Create Ticket
            </button>
        </div>
    </div>

    <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
            <form id="createTicketForm" action="{{ route('admin.tickets.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-high">Client <span class="text-danger">*</span></label>
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
                </div>

                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-high">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-premium-control w-100" required
                        value="{{ old('subject') }}" placeholder="Brief description of the issue">
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="heading-label d-block mb-2 text-high">Priority <span class="text-danger">*</span></label>
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
                    <div class="col-md-6">
                        <label class="heading-label d-block mb-2 text-high">Assign To</label>
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

                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-high">Description <span class="text-danger">*</span></label>
                    <x-textarea id="ticket-body" name="body" class="form-premium-control w-100" rows="8"
                        texteditor="true"></x-textarea>
                </div>


            </form>
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


