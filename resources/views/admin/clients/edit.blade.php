<x-admin title="Edit Client">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white">Edit Client: {{ $client->name }}</h2>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card">
                <form action="{{ route('admin.clients.update', $client->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-white">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $client->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $client->email) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Phone Number</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $client->phone) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Company Name</label>
                            <input type="text" name="company" class="form-control"
                                value="{{ old('company', $client->company) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="New Password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active"
                                {{ old('status', $client->is_approved ? 'active' : 'inactive') == 'active' ? 'selected' : '' }}>
                                Active</option>
                            <option value="inactive"
                                {{ old('status', $client->is_approved ? 'active' : 'inactive') == 'inactive' ? 'selected' : '' }}>
                                Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Update Client
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin>
