<x-admin title="Add New Client">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white">Add New Client</h2>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card">
                <form action="{{ route('admin.clients.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                value="{{ old('name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Email Address <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Company</label>
                        <input type="text" name="company" class="form-control" value="{{ old('company') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                        <div class="form-text text-muted">Inactive clients cannot log in.</div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Create Client
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin>
