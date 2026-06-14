<x-admin title="Add New Client">
    <div class="sticky-header shadow-sm rounded-3 d-flex justify-content-between align-items-center px-4 py-3" style="position: sticky; top: 65px; z-index: 100; background: var(--bg-surface); border: 1px solid var(--border-main);">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; border-color: var(--border-subtle);">
                <i class="fas fa-chevron-left text-high"></i>
            </a>
            <div>
                <h2 class="h5 fw-bold mb-0 text-high">Add New Client</h2>
                <p class="text-low mb-0" style="font-size: 0.8rem;">Create a new client profile.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.clients.index') }}" class="btn px-4" style="border: 1px solid var(--border-subtle); color: var(--text-high); background: transparent;">Cancel</a>
            <button type="submit" form="createClientForm" class="btn btn-primary px-4 fw-medium">
                Create Client
            </button>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card">
                <form id="createClientForm" action="{{ route('admin.clients.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                value="{{ old('name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Company</label>
                        <input type="text" name="company" class="form-control" value="{{ old('company') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <x-select name="status" placeholder="Select Status" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}
                                class="bg-dark">Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}
                                class="bg-dark">Inactive
                            </option>
                        </x-select>
                        <div class="form-text text-muted">Inactive clients cannot log in.</div>
                    </div>


                </form>
            </div>
        </div>
    </div>
</x-admin>
