<x-admin>
    <x-slot:title>
        Role Management | Team Tasker
    </x-slot:title>

    <div class="top-bar d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Role Management</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="fas fa-plus me-1"></i> Add Role
        </button>
    </div>

    <!-- Add Role Modal -->
    <x-modal id="addRoleModal" title="Create New Role" submitText="Create Role"
        formAction="{{ route('admin.roles.store') }}">
        <div class="mb-3">
            <label class="form-label">Role Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Developer, Designer" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role Slug</label>
            <input type="text" name="slug" class="form-control" placeholder="e.g. developer" required>
        </div>
    </x-modal>

    <div class="row g-4 mt-4">
        @foreach ($roles as $role)
            <div class="col-md-4">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon icon-primary mb-0">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                                <li>
                                    <button class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#editRoleModal{{ $role->id }}">
                                        <i class="fas fa-edit me-2"></i> Edit Role
                                    </button>
                                </li>
                                @if ($role->users_count == 0)
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteRoleModal{{ $role->id }}">
                                            <i class="fas fa-trash-alt me-2"></i> Delete Role
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="text-white mb-0">{{ $role->name }}</h4>
                        <span
                            class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-3 py-1 rounded-pill small">
                            {{ $role->users_count }} Users
                        </span>
                    </div>
                    <p class="text-muted small">Slug: <code class="text-accent">{{ $role->slug }}</code></p>

                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <button class="btn btn-sm btn-link text-primary p-0 text-decoration-none border-0"
                            data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                            <i class="fas fa-cog me-1"></i> Permissions (Coming Soon)
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @foreach ($roles as $role)
        <!-- Edit Role Modal -->
        <x-modal id="editRoleModal{{ $role->id }}" title="Edit Role: {{ $role->name }}" submitText="Save Changes"
            formAction="{{ route('admin.roles.update', $role->id) }}">
            <div class="mb-3">
                <label class="form-label">Role Name</label>
                <input type="text" name="name" value="{{ $role->name }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role Slug</label>
                <input type="text" name="slug" value="{{ $role->slug }}" class="form-control" required>
            </div>
        </x-modal>

        @if ($role->users_count == 0)
            <!-- Delete Role Confirmation Modal -->
            <x-modal id="deleteRoleModal{{ $role->id }}" title="Are you sure?" variant="danger"
                cancelText="No, Cancel" submitText="Yes, Delete Role" size="modal-sm"
                formAction="{{ route('admin.roles.delete', $role->id) }}" method="DELETE" bodyClass="text-center p-4">
                <div class="mb-3 text-danger" style="font-size: 3rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p class="text-muted small mb-0">You are about to delete the <strong>{{ $role->name }}</strong> role.
                    This action cannot be undone.</p>
            </x-modal>
        @endif
    @endforeach
</x-admin>
