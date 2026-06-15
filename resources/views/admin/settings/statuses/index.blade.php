<x-admin>
    <x-slot:title>
        Settings | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">System Settings</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Configure global system parameters.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="data-grid-wrapper p-3 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
                <div class="nav flex-column nav-pills gap-2" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link text-start active" id="v-pills-general-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-general" type="button" role="tab">
                        <i class="fas fa-sliders-h me-2"></i> General
                    </button>
                    <button class="nav-link text-start" id="v-pills-statuses-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-statuses" type="button" role="tab">
                        <i class="fas fa-tags me-2"></i> Task Statuses
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- General Settings Tab -->
                <div class="tab-pane fade show active" id="v-pills-general" role="tabpanel">
                    <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
                        <h5 class="mb-4">General Settings</h5>
                        <form>
                            <div class="mb-4">
                                <label class="heading-label d-block mb-2 text-high">Application Name</label>
                                <input type="text" class="form-premium-control w-100" value="Team Tasker" readonly>
                            </div>
                            <div class="mb-4">
                                <label class="heading-label d-block mb-2 text-high">Admin Email</label>
                                <input type="email" class="form-premium-control w-100" value="{{ Auth::user()->email }}" readonly>
                            </div>
                            <div class="text-end mt-4">
                                <button type="button" class="btn-premium btn-premium-primary px-4 py-2" disabled>Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statuses Tab -->
                <div class="tab-pane fade" id="v-pills-statuses" role="tabpanel">
                    <div class="data-grid-wrapper mb-5">
                        <div class="data-grid-top">
                            <div class="data-grid-search">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Search statuses...">
                            </div>
                            <div class="data-grid-results">{{ $statuses->count() }} Results</div>
                            <div class="data-grid-actions">
                                <button type="button" class="btn-premium btn-premium-primary py-1 px-3" data-bs-toggle="modal" data-bs-target="#createStatusModal">
                                    <i class="fas fa-plus me-1"></i> Add Status
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table data-grid-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"><input type="checkbox" class="data-grid-checkbox" id="selectAll"></th>
                                        <th>ORDER <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                                        <th>NAME <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                                        <th>SLUG <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                                        <th>COLOR <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                                        <th>DEFAULT <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                                        <th class="text-end pe-4">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($statuses as $status)
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="{{ $status->id }}" class="data-grid-checkbox item-checkbox"></td>
                                            <td class="text-low" style="color: #64748b !important;">#{{ $status->order }}</td>
                                            <td class="text-high fw-medium">
                                                <span class="badge-premium" style="background: rgba(var(--bs-{{ $status->color }}-rgb, 100, 116, 139), 0.1); color: var(--bs-{{ $status->color }}); font-size: 0.65rem; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase;">{{ $status->name }}</span>
                                            </td>
                                            <td class="text-high">{{ $status->slug }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div style="width: 16px; height: 16px; background-color: var(--bs-{{ $status->color }}); border-radius: 4px;"></div>
                                                    <span class="text-medium">{{ ucfirst($status->color) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($status->is_default)
                                                    <span class="badge-premium" style="background: rgba(16,185,129,0.1); color: #10b981; font-size: 0.65rem; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase;">DEFAULT</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="action-link border-0 bg-transparent" onclick="editStatus({{ $status->id }}, '{{ $status->name }}', '{{ $status->color }}', {{ $status->order }})">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>

                                                @if (!$status->is_default && $status->tasks_count == 0)
                                                    <form action="{{ route('admin.statuses.delete', $status->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this status?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-link delete border-0 bg-transparent">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center py-5 text-medium">No statuses found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Status Modal -->
    <x-modal id="createStatusModal" title="Create New Status" submitText="Create"
        formAction="{{ route('admin.statuses.store') }}">
        <div class="mb-3">
            <label for="name" class="form-label text-main">Status Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="color" class="form-label text-main">Color Badge</label>
            <x-select id="color" name="color" required placeholder="Select Color">
                <option value="primary" class="bg-dark">Primary (Blue)</option>
                <option value="secondary" class="bg-dark">Secondary (Gray)</option>
                <option value="success" class="bg-dark">Success (Green)</option>
                <option value="danger" class="bg-dark">Danger (Red)</option>
                <option value="warning" class="bg-dark">Warning (Yellow)</option>
                <option value="info" class="bg-dark">Info (Cyan)</option>
                <option value="dark" class="bg-dark">Dark</option>
            </x-select>
        </div>
    </x-modal>

    <!-- Edit Status Modal -->
    <x-modal id="editStatusModal" title="Edit Status" submitText="Update" formAction="#">
        <!-- Note: formAction is set dynamically via JS. We removed it from x-modal attr and using vanilla form inside if needed OR
             actually x-modal expects formAction. Let's customize x-modal usage or use internal form.
             Ideally x-modal puts form around body. Let's just use a form inside or direct slot usage if x-modal supports it.
             Wait, looking at x-modal, it conditionally renders form if formAction is set.
             We can set a dummy formAction and update it via JS. -->

        <input type="hidden" name="_method" value="POST">
        <!-- Controller expects POST for update as per routes -->

        <div class="mb-3">
            <label for="edit_name" class="form-label text-main">Status Name</label>
            <input type="text" class="form-control" id="edit_name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="edit_color" class="form-label text-main">Color Badge</label>
            <x-select id="edit_color" name="color" required placeholder="Select Color">
                <option value="primary" class="bg-dark">Primary (Blue)</option>
                <option value="secondary" class="bg-dark">Secondary (Gray)</option>
                <option value="success" class="bg-dark">Success (Green)</option>
                <option value="danger" class="bg-dark">Danger (Red)</option>
                <option value="warning" class="bg-dark">Warning (Yellow)</option>
                <option value="info" class="bg-dark">Info (Cyan)</option>
                <option value="dark" class="bg-dark">Dark</option>
            </x-select>
        </div>
        <div class="mb-3">
            <label for="edit_order" class="form-label text-main">Order</label>
            <input type="number" class="form-control" id="edit_order" name="order" required>
        </div>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for tab parameter in URL
            const urlParams = new URLSearchParams(window.location.search);
            const tabName = urlParams.get('tab');

            if (tabName) {
                const tabTrigger = document.querySelector(`#v-pills-${tabName}-tab`);
                if (tabTrigger) {
                    const tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }
        });

        function editStatus(id, name, color, order) {
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_color').value = color;
            document.getElementById('edit_order').value = order;

            // Update form action dynamically.
            // The modal component renders <form action="...">. We need to find that form.
            // Since x-modal puts a form if formAction is present.
            // We'll give the edit modal a dummy default action in the blade component so the form renders.
            const modalEl = document.getElementById('editStatusModal');
            const form = modalEl.querySelector('form');
            if (form) {
                form.action = `/admin/settings/statuses/${id}/update`;
            }

            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    </script>

    <style>
        .nav-pills .nav-link {
            color: var(--text-muted);
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--primary);
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .light-mode .nav-pills .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
    </style>
</x-admin>


