<x-admin>
    <x-slot:title>
        Role Management | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Role Management</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Manage user roles and permissions.</p>
        </div>
        <button class="btn-premium btn-premium-primary px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="fas fa-plus-circle me-1"></i> Add Role
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

    <div class="data-grid-wrapper mb-5">
        <div class="data-grid-top">
            <div class="data-grid-search">
                <i class="fas fa-search"></i>
                <form action="{{ route('admin.roles.index') }}" method="GET" id="searchForm">
                    <input type="text" name="search" placeholder="Search roles..." value="{{ request('search') }}" onchange="document.getElementById('searchForm').submit()">
                </form>
            </div>
            <div class="data-grid-results">{{ $roles->count() }} Results</div>
            <div class="data-grid-actions">
                {{ $roles->links('components.pagination.premium') }}
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-grid-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" class="data-grid-checkbox" id="selectAll"></th>
                        <th>ROLE NAME <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th>SLUG <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th>PERMISSIONS <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th>USERS <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th class="text-end pe-4">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $role->id }}" class="data-grid-checkbox item-checkbox"></td>
                            <td class="text-high fw-medium">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-icon-premium icon-primary-premium m-0 d-inline-flex justify-content-center align-items-center" style="width: 24px; height: 24px;">
                                        <i class="fas fa-shield-halved" style="font-size: 0.6rem;"></i>
                                    </div>
                                    {{ $role->name }}
                                </div>
                            </td>
                            <td class="text-high">{{ $role->slug }}</td>
                            <td>
                                <button class="btn btn-sm btn-link text-primary p-0 text-decoration-none border-0 fw-medium" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                    {{ is_array($role->permissions) ? count($role->permissions) : 0 }} Perms
                                </button>
                            </td>
                            <td>
                                <a href="{{ route('admin.users.index', ['role_id' => $role->id]) }}" class="action-link d-flex align-items-center gap-1">
                                    <i class="fas fa-users" style="font-size: 0.8rem;"></i>
                                    <span class="small fw-medium">{{ $role->users_count }}</span>
                                </a>
                            </td>
                            <td class="text-end pe-4">
                                <button class="action-link border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                @if ($role->users_count == 0)
                                    <button class="action-link delete border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#deleteRoleModal{{ $role->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-medium">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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


