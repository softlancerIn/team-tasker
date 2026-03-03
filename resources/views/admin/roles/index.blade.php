<x-admin>
    <x-slot:title>
        Role Management | Team Tasker
    </x-slot:title>

    <div class="top-bar d-flex justify-content-between align-items-center mb-5">
        <h2 class="h3 fw-bold mb-0 text-high">Role Management</h2>
        <button class="btn-premium btn-premium-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="fas fa-plus me-1"></i> Add Role
        </button>
    </div>

    <!-- Add Role Modal -->
    <x-modal id="addRoleModal" title="Create New Role" submitText="Create Role"
        formAction="{{ route('admin.roles.store') }}">
        <div class="mb-3">
            <label class="form-label text-medium">Role Name</label>
            <input type="text" name="name" class="form-premium-control" placeholder="e.g. Developer, Designer"
                required>
        </div>
        <div class="mb-3">
            <label class="form-label text-medium">Role Slug</label>
            <input type="text" name="slug" class="form-premium-control" placeholder="e.g. developer" required>
        </div>
        <div class="mb-3">
            <label class="form-label text-medium">Permissions</label>
            <div class="row" style="max-height: 300px; overflow-y: auto;">
                @foreach (config('permissions') as $group => $permissions)
                    <div class="col-md-6 mb-3">
                        <h6 class="text-uppercase text-secondary small mb-2">{{ ucfirst($group) }}</h6>
                        @if (is_array($permissions))
                            @foreach ($permissions as $key => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                        value="{{ $group }}.{{ $key }}"
                                        id="perm_{{ $group }}_{{ $key }}">
                                    <label class="form-check-label" for="perm_{{ $group }}_{{ $key }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                    value="{{ $group }}" id="perm_{{ $group }}">
                                <label class="form-check-label" for="perm_{{ $group }}">
                                    {{ $permissions }}
                                </label>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </x-modal>

    <div class="row g-4 mt-4">
        @foreach ($roles as $role)
            <div class="col-md-4">
                <div class="glass-card h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="stat-icon-premium icon-primary-premium m-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-shield-halved" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-low p-0 hover-opacity" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
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

                    <h4 class="text-high fw-bold mb-3">{{ $role->name }}</h4>

                    <div class="mb-4">
                        <span class="badge-premium"
                            style="background: var(--bg-input); border: 1px solid var(--border-subtle);">
                            <span class="text-low">Slug:</span> <span class="text-medium">{{ $role->slug }}</span>
                        </span>
                    </div>

                    <div
                        class="mt-auto pt-4 border-top border-subtle d-flex justify-content-between align-items-center">
                        <button
                            class="btn btn-sm btn-link text-primary p-0 text-decoration-none border-0 d-flex align-items-center gap-1"
                            data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                            <span class="text-low small">Perms:</span>
                            <span
                                class="text-medium fw-bold small">{{ is_array($role->permissions) ? count($role->permissions) : 0 }}</span>
                        </button>

                        <a href="{{ route('admin.users.index', ['role_id' => $role->id]) }}"
                            class="d-flex align-items-center gap-2 text-decoration-none hover-opacity">
                            <i class="fas fa-users text-primary" style="font-size: 0.8rem;"></i>
                            <span class="small text-medium fw-medium">{{ $role->users_count }} Users</span>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @foreach ($roles as $role)
        <!-- Edit Role Modal -->
        <x-modal id="editRoleModal{{ $role->id }}" title="Edit Role: {{ $role->name }}"
            submitText="Save Changes" formAction="{{ route('admin.roles.update', $role->id) }}">
            <div class="mb-3">
                <label class="form-label text-medium">Role Name</label>
                <input type="text" name="name" value="{{ $role->name }}" class="form-premium-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-medium">Role Slug</label>
                <input type="text" name="slug" value="{{ $role->slug }}" class="form-premium-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-medium">Permissions</label>
                <div class="row" style="max-height: 300px; overflow-y: auto;">
                    @foreach (config('permissions') as $group => $permissions)
                        <div class="col-md-6 mb-3">
                            <h6 class="heading-label mb-2" style="font-size: 0.65rem; opacity: 0.7;">
                                {{ ucfirst($group) }}</h6>
                            @if (is_array($permissions))
                                @foreach ($permissions as $key => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                            value="{{ $group }}.{{ $key }}"
                                            id="perm_{{ $group }}_{{ $key }}_{{ $role->id }}"
                                            {{ in_array($group . '.' . $key, $role->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="perm_{{ $group }}_{{ $key }}_{{ $role->id }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            @else
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                        value="{{ $group }}"
                                        id="perm_{{ $group }}_{{ $role->id }}"
                                        {{ in_array($group, $role->permissions ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="perm_{{ $group }}_{{ $role->id }}">
                                        {{ $permissions }}
                                    </label>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </x-modal>

        @if ($role->users_count == 0)
            <!-- Delete Role Confirmation Modal -->
            <x-modal id="deleteRoleModal{{ $role->id }}" title="Are you sure?" variant="danger"
                cancelText="No, Cancel" submitText="Yes, Delete Role" size="modal-sm"
                formAction="{{ route('admin.roles.delete', $role->id) }}" method="DELETE"
                bodyClass="text-center p-4">
                <div class="mb-3 text-danger" style="font-size: 3rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p class="text-low small mb-0">You are about to delete the <strong
                        class="text-high">{{ $role->name }}</strong>
                    role.
                    This action cannot be undone.</p>
            </x-modal>
        @endif
    @endforeach
</x-admin>
