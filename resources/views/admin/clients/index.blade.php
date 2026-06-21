<x-admin title="Our Clients">
    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Our Clients</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Manage and view your Clients.</p>
        </div>
        @if (Auth::user()->hasPermission('clients.create'))
            <a href="{{ route('admin.clients.create') }}" class="btn-premium btn-premium-primary px-4 py-2 shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> Create Client
            </a>
        @endif
    </div>

    <div class="data-grid-wrapper mb-5">
        <div class="data-grid-top">
            <div class="data-grid-search">
                <i class="fas fa-search"></i>
                <form action="{{ route('admin.clients.index') }}" method="GET" id="searchForm">
                    <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" onchange="document.getElementById('searchForm').submit()">
                </form>
            </div>
            <div class="data-grid-results">{{ $clients->total() }} Results</div>
            <div class="data-grid-actions">
                {{ $clients->links() }}
            </div>
        </div>
        <div class="data-grid-bulk-actions" id="bulkActionBar">
            <div class="data-grid-bulk-left">
                <span class="data-grid-bulk-count"><span id="selectedCount">0</span> Items Selected</span>
                <button type="button" class="btn-bulk-danger" onclick="submitBulkAction('delete')">
                    <i class="fas fa-trash-alt"></i> Bulk Delete
                </button>
                <button type="button" class="btn-bulk-outline" onclick="submitBulkAction('edit')">
                    <i class="fas fa-edit"></i> Bulk Edit
                </button>
            </div>
            <button type="button" class="btn-deselect-all" onclick="document.getElementById('selectAll').click()">
                Deselect All
            </button>
        </div>

        <div class="table-responsive">
            <table class="table data-grid-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" class="data-grid-checkbox" id="selectAll"></th>
                        <th>ID <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th>PROJECT TITLE <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th>CLIENT <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th>STATUS <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th>CREATED AT <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $client->id }}" class="data-grid-checkbox item-checkbox"></td>
                            <td class="text-low" style="color: #64748b !important;">#{{ $client->id }}</td>
                            <td class="text-high fw-medium">{{ $client->company ?? $client->name }}</td>
                            <td class="text-high">{{ $client->name }}</td>
                            <td>
                                @if($client->is_approved)
                                    <span class="badge-premium" style="background: rgba(16,185,129,0.1); color: #10b981; font-size: 0.65rem; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase;">ACTIVE</span>
                                @else
                                    <span class="badge-premium" style="background: #f1f5f9; color: #64748b; font-size: 0.65rem; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase;">INACTIVE</span>
                                @endif
                            </td>
                            <td class="text-high">{{ $client->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="text-end pe-4">
                                @if (Auth::user()->hasPermission('clients.edit'))
                                    <a href="{{ route('admin.clients.edit', $client->id) }}" class="action-link"><i class="fas fa-pencil-alt"></i></a>
                                @endif
                                @if (Auth::user()->hasPermission('clients.delete'))
                                    <button onclick="if(confirm('Delete?')) document.getElementById('del-{{ $client->id }}').submit()" class="action-link delete border-0 bg-transparent"><i class="fas fa-trash"></i></button>
                                    <form id="del-{{ $client->id }}" action="{{ route('admin.clients.delete', $client->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-medium">No projects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Filter Slideover -->
    <div class="filter-slideover" id="filterSlideover">
        <form action="{{ route('admin.clients.index') }}" method="GET" class="h-100 d-flex flex-column">
            <div class="filter-slideover-header">
                <h4><i class="fas fa-sliders-h text-low me-2"></i> Advanced Filters</h4>
                <div class="filter-slideover-close" onclick="document.getElementById('filterSlideover').classList.remove('show')">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            <div class="filter-slideover-body">
                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-low">SEARCH QUERY</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-premium-control bg-white text-dark border-main" placeholder="Search anything...">
                </div>
                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-low">STATUS</label>
                    <x-select name="status" placeholder="All Status" :selected="request('status')">
                        <option value="" class="bg-dark">All Status</option>
                        <option value="1" class="bg-dark">Active</option>
                        <option value="0" class="bg-dark">Inactive</option>
                    </x-select>
                </div>
                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-low">CREATED DATE</label>
                    <input type="date" name="created_at" value="{{ request('created_at') }}" class="form-premium-control bg-white text-dark border-main">
                </div>
                <div class="mb-4">
                    <label class="heading-label d-block mb-2 text-low">UPDATED DATE</label>
                    <input type="date" name="updated_at" value="{{ request('updated_at') }}" class="form-premium-control bg-white text-dark border-main">
                </div>
            </div>
            <div class="filter-slideover-footer">
                <a href="{{ route('admin.clients.index') }}" class="btn-premium btn-premium-secondary w-50 justify-content-center bg-white text-dark border-main">Reset</a>
                <button type="submit" class="btn-premium btn-premium-primary w-50 justify-content-center" style="background: #0ea5e9;">Apply Filters</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const bulkActionBar = document.getElementById('bulkActionBar');
            const selectedCount = document.getElementById('selectedCount');
            const topBar = document.querySelector('.data-grid-top');

            function updateBulkBar() {
                const checked = document.querySelectorAll('.item-checkbox:checked').length;
                selectedCount.textContent = checked;

                if (checked > 0) {
                    bulkActionBar.classList.add('active');
                } else {
                    bulkActionBar.classList.remove('active');
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateBulkBar();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkBar);
            });
        });

        function submitBulkAction(action) {
            alert('Bulk action: ' + action);
        }
    </script>
</x-admin>


