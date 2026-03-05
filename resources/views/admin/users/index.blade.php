<x-admin>
    <x-slot:title>
        Team Management | Team Tasker
    </x-slot:title>

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="h3 fw-bold mb-1" style="color: var(--text-high);">Team Management</h2>
            <p class="text-low mb-0" style="font-size: 0.85rem;">Manage team members, roles, and access permissions</p>
        </div>
        <button class="btn-premium btn-premium-primary px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus-circle me-1"></i> Add Team Member
        </button>
    </div>

    @if ($errors->any())
        <div class="glass-card mb-4 border-danger" style="background: rgba(var(--danger-rgb), 0.05);">
            <div class="d-flex gap-3">
                <div class="text-danger"><i class="fas fa-exclamation-circle fs-5"></i></div>
                <div>
                    <h6 class="fw-bold text-danger mb-2">Attention Required</h6>
                    <ul class="mb-0 text-low small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Add User Modal -->
    <x-modal id="addUserModal" title="Onboard Team Member" submitText="Create Profile"
        formAction="{{ route('admin.users.store') }}">
        <div class="mb-4">
            <label class="heading-label mb-2" style="font-size: 0.7rem;">Legal Full Name</label>
            <input type="text" name="name" class="form-premium-control" required placeholder="e.g. John Doe">
        </div>
        <div class="mb-4">
            <label class="heading-label mb-2" style="font-size: 0.7rem;">Professional Email</label>
            <input type="email" name="email" class="form-premium-control" required placeholder="john@company.com">
        </div>
        <div class="mb-4">
            <label class="heading-label mb-2" style="font-size: 0.7rem;">Secure Password</label>
            <input type="password" name="password" class="form-premium-control" required
                placeholder="Minimum 8 characters">
        </div>
        <div class="mb-4">
            <label class="heading-label mb-2" style="font-size: 0.7rem;">Administrative Role</label>
            <x-select name="role_id" class="form-premium-control" placeholder="Guest Access (No Role)">
                <option value="" class="bg-dark">Guest Access (No Role)</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" class="bg-dark">{{ $role->name }}</option>
                @endforeach
            </x-select>
        </div>
    </x-modal>

    {{-- ── Filter Bar (standalone form, never nested) ── --}}
    <form action="{{ route('admin.users.index') }}" method="GET"
        class="glass-card mb-4 d-flex flex-wrap align-items-center gap-3" style="border: 1px solid var(--border-main);">

        <span class="heading-label mb-0 me-1" style="font-size: 0.7rem; white-space: nowrap;">
            <i class="fas fa-filter me-1"></i> Filters:
        </span>

        <input type="text" name="name" value="{{ request('name') }}" class="form-premium-control"
            placeholder="Search by name..." style="max-width: 220px; font-size: 0.85rem; flex: 1 1 180px;">

        <input type="text" name="email" value="{{ request('email') }}" class="form-premium-control"
            placeholder="Search by email..." style="max-width: 220px; font-size: 0.85rem; flex: 1 1 180px;">

        <x-select name="role_id" class="form-premium-control" placeholder="All Roles"
            style="max-width: 180px; font-size: 0.85rem; flex: 1 1 140px;">
            <option value="" class="bg-dark">All Roles</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}
                    class="bg-dark">
                    {{ $role->name }}
                </option>
            @endforeach
        </x-select>

        <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
            @if (request()->anyFilled(['name', 'email', 'role_id']))
                <a href="{{ route('admin.users.index') }}" class="text-low text-decoration-none small"
                    style="white-space: nowrap;">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            @endif
            <button type="submit" class="btn-premium btn-premium-primary px-4"
                style="font-size: 0.85rem; white-space: nowrap;">
                <i class="fas fa-search me-1"></i> Search
            </button>
        </div>
    </form>


    {{-- ── Bulk Action Form wraps ONLY the table ── --}}
    <form id="bulkActionForm" action="{{ route('admin.users.bulkAction') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulkActionType">
        <input type="hidden" name="role_id" id="bulkRoleId">

        <div class="glass-card p-0 overflow-hidden" style="border: 1px solid var(--border-main);">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr style="background: var(--bg-input);">
                            <th class="ps-4 py-3" style="width: 40px;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </div>
                            </th>
                            <th class="py-3 heading-label">Team Member</th>
                            <th class="py-3 heading-label">Role</th>
                            <th class="py-3 heading-label text-center">Status</th>
                            <th class="py-3 heading-label">Contact Details</th>
                            <th class="pe-4 py-3 heading-label text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody style="border-top: none;">
                        @foreach ($users as $user)
                            <tr class="align-middle" style="border-bottom: 1px solid var(--border-subtle);">
                                <td class="ps-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="ids[]" value="{{ $user->id }}"
                                            class="form-check-input user-checkbox">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-premium" style="width: 36px; height: 36px;">
                                            @if ($user->profile_image)
                                                <img src="{{ asset('storage/' . $user->profile_image) }}"
                                                    alt="">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center w-100 h-100"
                                                    style="background: var(--primary); color: white; font-weight: 600;">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="color: var(--text-high); font-size: 0.9rem;">
                                                {{ $user->name }}</div>
                                            <div class="text-low" style="font-size: 0.75rem;">ID:
                                                #{{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($user->role)
                                        <span class="badge-premium"
                                            style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); font-size: 0.7rem;">
                                            {{ $user->role->name }}
                                        </span>
                                    @else
                                        <span class="text-low fst-italic small">No role assigned</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($user->is_approved)
                                        <span class="badge-premium"
                                            style="background: rgba(var(--accent-rgb), 0.1); color: var(--accent); font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1" style="font-size: 0.6rem;"></i>
                                            Approved
                                        </span>
                                    @else
                                        <span class="badge-premium"
                                            style="background: var(--bg-input); color: var(--text-medium); font-size: 0.7rem;">
                                            <i class="fas fa-clock me-1" style="font-size: 0.6rem;"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div style="color: var(--text-medium); font-size: 0.85rem;">
                                            {{ $user->email }}</div>
                                        @if ($user->phone)
                                            <div class="text-low" style="font-size: 0.75rem;"><i
                                                    class="fas fa-phone-alt me-1" style="font-size: 0.65rem;"></i>
                                                {{ $user->phone }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.toggleApproval', $user->id) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="btn-premium btn-premium-secondary btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; border-radius: 50%; color: {{ $user->is_approved ? 'var(--accent)' : 'var(--primary)' }};"
                                                    title="{{ $user->is_approved ? 'Revoke Approval' : 'Grant Approval' }}">
                                                    <i class="fas {{ $user->is_approved ? 'fa-user-slash' : 'fa-user-check' }}"
                                                        style="font-size: 0.8rem;"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button"
                                            class="btn-premium btn-premium-secondary btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; border-radius: 50%;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal{{ $user->id }}">
                                            <i class="fas fa-edit"
                                                style="font-size: 0.8rem; color: var(--text-medium);"></i>
                                        </button>

                                        @if ($user->id !== auth()->id())
                                            <button type="button"
                                                class="btn-premium btn-premium-secondary btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; border-radius: 50%; color: var(--danger);"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal{{ $user->id }}">
                                                <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
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
    <!-- Floating Bulk Action Bar -->
    <div id="bulkActionBar" class="bulk-action-bar hidden shadow-premium"
        style="border: 1px solid var(--border-main); background: var(--bg-surface);">
        <div class="container-fluid d-flex align-items-center justify-content-between py-3 px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-premium m-0 d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                    <span id="selectedCount" class="fw-bold">0</span>
                </div>
                <span class="text-high fw-bold" style="font-size: 0.95rem;">Users Selected</span>
            </div>

            <div class="d-flex align-items-center gap-4">
                <div class="d-flex border-end border-main pe-4 gap-2">
                    <button type="button" onclick="submitBulkAction('approve')"
                        class="btn-premium btn-premium-primary py-2 px-3"
                        style="font-size: 0.8rem; background: rgba(var(--accent-rgb), 0.1); color: var(--accent); border: 1px solid rgba(var(--accent-rgb), 0.2);">
                        <i class="fas fa-check-circle me-1"></i> Approve
                    </button>
                    <button type="button" onclick="submitBulkAction('disapprove')"
                        class="btn-premium btn-premium-secondary py-2 px-3" style="font-size: 0.8rem;">
                        <i class="fas fa-times-circle me-1"></i> Suspend
                    </button>
                </div>

                <div class="d-flex align-items-center gap-2 border-end border-main pe-4">
                    <x-select id="bulkRoleSelect" class="form-premium-control" placeholder="Map to Role..."
                        style="width: 160px; font-size: 0.8rem; background: var(--bg-input);">
                        <option value="" class="bg-dark">Map to Role...</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" class="bg-dark">{{ $role->name }}</option>
                        @endforeach
                    </x-select>
                    <button type="button" onclick="submitBulkAction('change_role')"
                        class="btn-premium btn-premium-primary py-2 px-3" style="font-size: 0.8rem;">
                        Apply
                    </button>
                </div>

                <button type="button" onclick="submitBulkAction('delete')" class="btn-premium py-2 px-3"
                    style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger); border: 1px solid rgba(var(--danger-rgb), 0.2); font-size: 0.8rem;">
                    <i class="fas fa-trash-alt me-1"></i> Delete Permanent
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
            background: var(--bg-surface);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-main);
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
        <x-modal id="editUserModal{{ $user->id }}" title="Refine Profile: {{ $user->name }}"
            submitText="Update Identity" formAction="{{ route('admin.users.update', $user->id) }}">
            <div class="mb-4">
                <label class="heading-label mb-2" style="font-size: 0.7rem;">Display Name</label>
                <input type="text" name="name" value="{{ $user->name }}" class="form-premium-control"
                    required>
            </div>
            <div class="mb-4">
                <label class="heading-label mb-2" style="font-size: 0.7rem;">Communication Email</label>
                <input type="email" name="email" value="{{ $user->email }}" class="form-premium-control"
                    required>
            </div>
            <div class="mb-4">
                <label class="heading-label mb-2" style="font-size: 0.7rem;">Authentication Password</label>
                <input type="password" name="password" class="form-premium-control"
                    placeholder="Leave empty to maintain current">
            </div>
            <div class="mb-4">
                <label class="heading-label mb-2" style="font-size: 0.7rem;">Access Permissions</label>
                <x-select name="role_id" class="form-premium-control" placeholder="Unassigned Role">
                    <option value="" class="bg-dark">Unassigned Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}
                            class="bg-dark">
                            {{ $role->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>
        </x-modal>

        @if ($user->id !== auth()->id())
            <!-- Delete User Confirmation Modal -->
            <x-modal id="deleteUserModal{{ $user->id }}" title="Security Warning" variant="danger"
                cancelText="Retain Member" submitText="Purge Account" size="modal-sm"
                formAction="{{ route('admin.users.delete', $user->id) }}" method="DELETE"
                bodyClass="text-center p-4">
                <div class="mb-4 text-danger" style="font-size: 3.5rem; opacity: 0.8;">
                    <i class="fas fa-user-minus"></i>
                </div>
                <h6 class="fw-bold mb-3" style="color: var(--text-high);">Irreversible Action</h6>
                <p class="text-low mb-0" style="font-size: 0.85rem;">You are about to permanently remove
                    <strong>{{ $user->name }}</strong>. All associated permissions and session keys will be
                    invalidated immediately.
                </p>
            </x-modal>
        @endif
    @endforeach
</x-admin>
