<x-admin title="Add New Client">
    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Add New Client</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Create a new client profile.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.clients.index') }}" class="btn-premium btn-premium-secondary px-4 py-2">Cancel</a>
            <button type="submit" form="createClientForm" class="btn-premium btn-premium-primary px-4 py-2 shadow-sm">
                Create Client
            </button>
        </div>
    </div>

    <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
            <form id="createClientForm" action="{{ route('admin.clients.store') }}" method="POST">
                @csrf
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="heading-label d-block mb-2 text-high">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-premium-control w-100" required
                            value="{{ old('name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="heading-label d-block mb-2 text-high">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-premium-control w-100" required
                            value="{{ old('email') }}">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="heading-label d-block mb-2 text-high">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-premium-control w-100" required minlength="8">
                    </div>
                    <div class="col-md-6">
                        <label class="heading-label d-block mb-2 text-high">Phone</label>
                        <input type="text" name="phone" class="form-premium-control w-100" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-high">Company</label>
                    <input type="text" name="company" class="form-premium-control w-100" value="{{ old('company') }}">
                </div>

                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-high">Status <span class="text-danger">*</span></label>
                    <x-select name="status" placeholder="Select Status" required>
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}
                            class="bg-dark">Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}
                            class="bg-dark">Inactive
                        </option>
                    </x-select>
                    <div class="text-low extra-small mt-2">Inactive clients cannot log in.</div>
                </div>


            </form>
        </div>
</x-admin>


