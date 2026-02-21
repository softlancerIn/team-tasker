<x-admin>
    <x-slot:title>
        Team Management | Team Tasker
    </x-slot:title>

    <div class="top-bar d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Team Management</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-1"></i> Add User
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger mt-4"
            role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Add User Modal -->
    <x-modal id="addUserModal" title="Add New Team Member" submitText="Create User"
        formAction="{{ route('admin.users.store') }}">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Select Role</label>
            <select name="role_id" class="form-select">
                <option value="">No Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
    </x-modal>

    <form id="bulkActionForm" action="{{ route('admin.users.bulkAction') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulkActionType">
        <input type="hidden" name="role_id" id="bulkRoleId">

        <div class="glass-card mt-4 table-container">
            <div
                class="pb-4 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 flex-grow-1">
                    <div class="col-md-3">
                        <input type="text" name="name" value="{{ request('name') }}" class="form-control"
                            placeholder="Search by name...">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="email" value="{{ request('email') }}" class="form-control"
                            placeholder="Search by email...">
                    </div>
                    <div class="col-md-2">
                        <select name="role_id" class="form-select">
                            <option value="">All Roles</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-dark table-hover mb-0 bg-transparent align-middle">
                    <thead>
                        <tr class="text-muted small uppercase">
                            <th class="border-0 px-4" style="width: 40px;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </div>
                            </th>
                            <th class="border-0">User</th>
                            <th class="border-0">Email</th>
                            <th class="border-0">Role</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="ids[]" value="{{ $user->id }}"
                                            class="form-check-input user-checkbox">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            @if ($user->profile_image)
                                                <img src="{{ asset('storage/' . $user->profile_image) }}"
                                                    alt=""
                                                    style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                            @else
                                                {{ substr($user->name, 0, 1) }}
                                            @endif
                                        </div>
                                        <span class="fw-medium text-white">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="text-secondary small">{{ $user->email }}</td>
                                <td>
                                    @if ($user->role)
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 rounded-pill extra-small">
                                            {{ $user->role->name }}
                                        </span>
                                    @else
                                        <span class="text-muted extra-small italic">No role</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($user->is_approved)
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill extra-small">
                                            Approved
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 rounded-pill extra-small">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end px-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.toggleApproval', $user->id) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-{{ $user->is_approved ? 'warning' : 'success' }} border-0"
                                                    title="{{ $user->is_approved ? 'Disapprove' : 'Approve' }}">
                                                    <i
                                                        class="fas {{ $user->is_approved ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-outline-secondary border-0"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal{{ $user->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        @if ($user->id !== auth()->id())
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal{{ $user->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <!-- Floating Bulk Action Bar -->
    <div id="bulkActionBar" class="bulk-action-bar hidden">
        <div class="container-fluid d-flex align-items-center justify-content-between py-3 px-4 shadow-lg">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary rounded-pill px-3 py-2" id="selectedCount">0</span>
                <span class="text-white fw-medium">Users Selected</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="d-flex border-end border-white border-opacity-10 pe-3 gap-2">
                    <button type="button" onclick="submitBulkAction('approve')" class="btn btn-success btn-sm px-3">
                        <i class="fas fa-check-circle me-1"></i> Approve
                    </button>
                    <button type="button" onclick="submitBulkAction('disapprove')"
                        class="btn btn-warning btn-sm px-3">
                        <i class="fas fa-times-circle me-1"></i> Disapprove
                    </button>
                </div>

                <div class="d-flex align-items-center gap-2 border-end border-white border-opacity-10 pe-3">
                    <select id="bulkRoleSelect" class="form-select form-select-sm"
                        style="width: 150px; background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);">
                        <option value="">Choose Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="submitBulkAction('change_role')" class="btn btn-primary btn-sm">
                        Apply
                    </button>
                </div>

                <button type="button" onclick="submitBulkAction('delete')" class="btn btn-danger btn-sm px-3">
                    <i class="fas fa-trash-alt me-1"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>

    <style>
        .bulk-action-bar {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(150%);
            width: 90%;
            max-width: 1000px;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        .bulk-action-bar.show {
            transform: translateX(-50%) translateY(0);
        }

        .extra-small {
            font-size: 0.65rem;
        }

        .table-container {
            position: relative;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const userCheckboxes = document.querySelectorAll('.user-checkbox');
            const bulkActionBar = document.getElementById('bulkActionBar');
            const selectedCount = document.getElementById('selectedCount');

            function updateBulkBar() {
                const checked = document.querySelectorAll('.user-checkbox:checked').length;
                selectedCount.textContent = checked;

                if (checked > 0) {
                    bulkActionBar.classList.add('show');
                } else {
                    bulkActionBar.classList.remove('show');
                }
            }

            selectAll.addEventListener('change', function() {
                userCheckboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBulkBar();
            });

            userCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkBar);
            });
        });

        function submitBulkAction(action) {
            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete selected users? This action cannot be undone.')) {
                    return;
                }
            }

            if (action === 'change_role') {
                const roleId = document.getElementById('bulkRoleSelect').value;
                if (!roleId) {
                    alert('Please select a role first.');
                    return;
                }
                document.getElementById('bulkRoleId').value = roleId;
            }

            document.getElementById('bulkActionType').value = action;
            document.getElementById('bulkActionForm').submit();
        }
    </script>

    @foreach ($users as $user)
        <!-- Edit User Modal -->
        <x-modal id="editUserModal{{ $user->id }}" title="Edit Team Member: {{ $user->name }}"
            submitText="Save Changes" formAction="{{ route('admin.users.update', $user->id) }}">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Select Role</label>
                <select name="role_id" class="form-select">
                    <option value="">No Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </x-modal>

        @if ($user->id !== auth()->id())
            <!-- Delete User Confirmation Modal -->
            <x-modal id="deleteUserModal{{ $user->id }}" title="Are you sure?" variant="danger"
                cancelText="No, Cancel" submitText="Yes, Delete User" size="modal-sm"
                formAction="{{ route('admin.users.delete', $user->id) }}" method="DELETE"
                bodyClass="text-center p-4">
                <div class="mb-3 text-danger" style="font-size: 3rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p class="text-muted small mb-0">You are about to delete <strong>{{ $user->name }}</strong>. This
                    action cannot be undone.</p>
            </x-modal>
        @endif
    @endforeach
</x-admin>
