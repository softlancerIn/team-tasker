<x-admin title="Edit Client">
    <div class="sticky-header shadow-sm rounded-3 d-flex justify-content-between align-items-center px-4 py-3" style="position: sticky; top: 65px; z-index: 100; background: var(--bg-surface); border: 1px solid var(--border-main);">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; border-color: var(--border-subtle);">
                <i class="fas fa-chevron-left text-high"></i>
            </a>
            <div>
                <h2 class="h5 fw-bold mb-0 text-high">Edit Client: {{ $client->name }}</h2>
                <p class="text-low mb-0" style="font-size: 0.8rem;">Modify the details of your client.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.clients.index') }}" class="btn px-4" style="border: 1px solid var(--border-subtle); color: var(--text-high); background: transparent;">Cancel</a>
            <button type="submit" form="editClientForm" class="btn btn-primary px-4 fw-medium">
                Update Client
            </button>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card">
                <form id="editClientForm" action="{{ route('admin.clients.update', $client->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $client->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $client->email) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $client->phone) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company" class="form-control"
                                value="{{ old('company', $client->company) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="New Password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
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
        </div>
    </div>
</x-admin>
