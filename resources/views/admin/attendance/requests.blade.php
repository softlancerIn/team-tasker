<x-admin>
    <x-slot:title>
        Attendance Requests | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Leave & Regularization Requests</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Manage employee leave, overtime, and regularization requests.</p>
        </div>
        <button class="btn-premium btn-premium-primary px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#newRequestModal">
            <i class="fas fa-plus-circle me-1"></i> New Request
        </button>
    </div>

    <div class="data-grid-wrapper mb-5">
        <div class="data-grid-top">
            <div class="data-grid-search">
                <form action="{{ route('admin.attendance.requests') }}" method="GET" class="d-flex align-items-center m-0 w-100">
                    <i class="fas fa-search me-2 text-low"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="border-1 bg-transparent text-high w-100" style="outline: none;">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                </form>
            </div>
            <div class="data-grid-results">{{ $requests->total() }} Results</div>
            <div class="data-grid-actions">
                {{ $requests->links() }}
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-grid-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Action By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-premium" style="width: 32px; height: 32px;">
                                        @if ($req->user->profile_image)
                                            <img src="{{ asset('storage/' . $req->user->profile_image) }}" alt="Profile">
                                        @else
                                            {{ substr($req->user->name ?? 'U', 0, 1) }}
                                        @endif
                                    </div>
                                    <span class="text-high fw-semibold">{{ $req->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-premium bg-primary-subtle text-primary border border-primary border-opacity-25 px-2 py-1">
                                    {{ $req->type }}
                                </span>
                            </td>
                            <td class="text-low">
                                {{ \Carbon\Carbon::parse($req->start_date)->format('d/m/Y') }}
                                @if($req->end_date && $req->start_date !== $req->end_date)
                                    - {{ \Carbon\Carbon::parse($req->end_date)->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="text-low text-truncate" style="max-width: 200px;" title="{{ $req->reason }}">
                                {{ $req->reason }}
                            </td>
                            <td>
                                @php
                                    $statusColor = match($req->status) {
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        default => 'warning'
                                    };
                                @endphp
                                <span class="badge-premium bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border border-{{ $statusColor }} border-opacity-25 px-2 py-1">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="text-low">
                                {{ $req->actionBy ? $req->actionBy->name : '-' }}
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if($req->status === 'Pending' && Auth::user()->hasPermission('attendance.requests_manage'))
                                        <button type="button" class="btn btn-sm btn-success" title="Approve" onclick="updateStatus({{ $req->id }}, 'Approved')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="updateStatus({{ $req->id }}, 'Rejected')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    
                                    @if($req->status === 'Pending' && $req->user_id === Auth::id())
                                        <button type="button" class="btn btn-sm btn-premium-primary" title="Edit Request" onclick="editRequest({{ $req->id }}, '{{ $req->type }}', '{{ $req->start_date }}', '{{ $req->end_date }}', `{{ addslashes($req->reason) }}`)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif

                                    <a href="{{ route('admin.attendance.calendar', ['user_id' => $req->user_id]) }}" class="btn btn-sm btn-premium-secondary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($requests->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center text-low py-4">No requests found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- New Request Modal -->
    <div class="modal fade" id="newRequestModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-main">
                <div class="modal-header border-subtle">
                    <h5 class="modal-title fw-bold text-high">New Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.attendance.requests.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-high fw-semibold">Type</label>
                            <select name="type" class="form-premium-control w-100" required>
                                <option value="Leave">Leave</option>
                                <option value="Regularization">Attendance Regularization</option>
                                <option value="Overtime">Overtime</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-high fw-semibold">Start Date</label>
                                <input type="date" name="start_date" class="form-premium-control w-100" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-high fw-semibold">End Date (Optional)</label>
                                <input type="date" name="end_date" class="form-premium-control w-100">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-high fw-semibold">Reason</label>
                            <textarea name="reason" class="form-premium-control w-100" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-subtle">
                        <button type="button" class="btn-premium btn-premium-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-premium btn-premium-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Request Modal -->
    <div class="modal fade" id="editRequestModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-main">
                <div class="modal-header border-subtle">
                    <h5 class="modal-title fw-bold text-high">Edit Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editRequestForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-high fw-semibold">Type</label>
                            <select name="type" id="editRequestType" class="form-premium-control w-100" required>
                                <option value="Leave">Leave</option>
                                <option value="Regularization">Attendance Regularization</option>
                                <option value="Overtime">Overtime</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-high fw-semibold">Start Date</label>
                                <input type="date" name="start_date" id="editRequestStartDate" class="form-premium-control w-100" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-high fw-semibold">End Date (Optional)</label>
                                <input type="date" name="end_date" id="editRequestEndDate" class="form-premium-control w-100">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-high fw-semibold">Reason</label>
                            <textarea name="reason" id="editRequestReason" class="form-premium-control w-100" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-subtle">
                        <button type="button" class="btn-premium btn-premium-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-premium btn-premium-primary">Update Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Leave Request Action Modal -->
    <div class="modal fade" id="leaveActionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-main">
                <div class="modal-header border-subtle">
                    <h5 class="modal-title fw-bold text-high" id="leaveActionTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="leaveActionForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" id="leaveActionStatus">
                    <div class="modal-body">
                        <p class="text-medium mb-3" id="leaveActionMessage">Are you sure you want to proceed?</p>
                        <div class="mb-3">
                            <label class="form-label text-high fw-semibold">Action Notes (Optional)</label>
                            <textarea name="action_notes" class="form-premium-control w-100" rows="3" placeholder="Explain the reason for approval or rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-subtle">
                        <button type="button" class="btn-premium btn-premium-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-premium btn-premium-primary" id="leaveActionSubmitBtn">
                            Confirm Action
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editRequest(id, type, startDate, endDate, reason) {
            const modal = new bootstrap.Modal(document.getElementById('editRequestModal'));
            const form = document.getElementById('editRequestForm');
            
            form.action = "{{ route('admin.attendance.requests.update', ':id') }}".replace(':id', id);
            
            document.getElementById('editRequestType').value = type;
            document.getElementById('editRequestStartDate').value = startDate;
            document.getElementById('editRequestEndDate').value = endDate || '';
            document.getElementById('editRequestReason').value = reason;
            
            modal.show();
        }

        function updateStatus(id, status) {
            const modal = new bootstrap.Modal(document.getElementById('leaveActionModal'));
            const form = document.getElementById('leaveActionForm');
            form.action = "{{ route('admin.attendance.requests.updateStatus', ':id') }}".replace(':id', id);
            
            document.getElementById('leaveActionStatus').value = status;
            document.getElementById('leaveActionTitle').innerText = status === 'Approved' ? 'Approve Leave Request' : 'Reject Leave Request';
            document.getElementById('leaveActionMessage').innerText = `Are you sure you want to mark this request as ${status}?`;
            
            const submitBtn = document.getElementById('leaveActionSubmitBtn');
            submitBtn.className = status === 'Approved' ? 'btn-premium btn-premium-primary' : 'btn-premium btn btn-danger';
            submitBtn.innerText = status === 'Approved' ? 'Approve Request' : 'Reject Request';
            
            modal.show();
        }
    </script>
</x-admin>

<div class="filter-slideover" id="filterSlideoverAttendance">
    <form action="{{ route('admin.attendance.requests') }}" method="GET" class="h-100 d-flex flex-column">
        <div class="filter-slideover-header">
            <h4><i class="fas fa-sliders-h text-low me-2"></i> Advanced Filters</h4>
            <div class="filter-slideover-close" onclick="document.querySelector('.filter-slideover').classList.remove('show')">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <div class="filter-slideover-body">
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">STATUS</label>
                <select name="status" class="form-premium-control bg-white text-dark border-main">
                    <option value="">All Statuses</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
        </div>
        <div class="filter-slideover-footer">
            <a href="{{ route('admin.attendance.requests') }}" class="btn-premium btn-premium-secondary w-50 justify-content-center bg-white text-dark border-main">Reset</a>
            <button type="submit" class="btn-premium btn-premium-primary w-50 justify-content-center" style="background: #0ea5e9;">Apply Filters</button>
        </div>
    </form>
</div>
