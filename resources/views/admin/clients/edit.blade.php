<x-admin title="Edit Client">
    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Edit Client: {{ $client->name }}</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Modify the details of your client.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.clients.index') }}" class="btn-premium btn-premium-secondary px-4 py-2">Cancel</a>
            <button type="submit" form="editClientForm" class="btn-premium btn-premium-primary px-4 py-2 shadow-sm">
                Update Client
            </button>
        </div>
    </div>

    <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
            <form id="editClientForm" action="{{ route('admin.clients.update', $client->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-high">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-premium-control w-100"
                        value="{{ old('name', $client->name) }}" required>
                </div>

                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-high">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-premium-control w-100"
                        value="{{ old('email', $client->email) }}" required>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="heading-label d-block mb-2 text-high">Phone Number</label>
                        <input type="text" name="phone" class="form-premium-control w-100"
                            value="{{ old('phone', $client->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="heading-label d-block mb-2 text-high">Company Name</label>
                        <input type="text" name="company" class="form-premium-control w-100"
                            value="{{ old('company', $client->company) }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-high">Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="form-premium-control w-100" placeholder="New Password">
                </div>

                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-high">Status <span class="text-danger">*</span></label>
                    <x-select name="status" placeholder="Select Status" required>
                        <option value="active"
                            {{ old('status', $client->is_approved ? 'active' : 'inactive') == 'active' ? 'selected' : '' }}
                            class="bg-dark">
                            Active</option>
                        <option value="inactive"
                            {{ old('status', $client->is_approved ? 'active' : 'inactive') == 'inactive' ? 'selected' : '' }}
                            class="bg-dark">
                            Inactive</option>
                    </x-select>
                </div>


            </form>
        </div>
</x-admin>


