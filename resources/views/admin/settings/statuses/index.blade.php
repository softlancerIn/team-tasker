<x-admin>
    <x-slot:title>
        Settings | Team Tasker
    </x-slot:title>

    <h4 class="mb-4">Settings</h4>

    <div class="row">
        <div class="col-md-3">
            <div class="glass-card p-3">
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
                    <div class="glass-card">
                        <h5 class="mb-4">General Settings</h5>
                        <form>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Application Name</label>
                                <input type="text" class="form-control" value="Team Tasker" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Admin Email</label>
                                <input type="email" class="form-control" value="{{ Auth::user()->email }}" readonly>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-primary" disabled>Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statuses Tab -->
                <div class="tab-pane fade" id="v-pills-statuses" role="tabpanel">
                    <div class="glass-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">Task Statuses</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createStatusModal">
                                <i class="fas fa-plus me-1"></i> Add Status
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table text-main">
                                <thead>
                                    <tr class="text-muted small uppercase">
                                        <th class="bg-transparent border-0">Order</th>
                                        <th class="bg-transparent border-0">Name</th>
                                        <th class="bg-transparent border-0">Slug</th>
                                        <th class="bg-transparent border-0">Color</th>
                                        <th class="bg-transparent border-0">Default</th>
                                        <th class="bg-transparent border-0 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($statuses as $status)
                                        <tr class="border-bottom border-secondary border-opacity-10">
                                            <td class="bg-transparent align-middle">{{ $status->order }}</td>
                                            <td class="bg-transparent align-middle">
                                                <span class="badge bg-{{ $status->color }}">{{ $status->name }}</span>
                                            </td>
                                            <td class="bg-transparent align-middle">{{ $status->slug }}</td>
                                            <td class="bg-transparent align-middle">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div
                                                        style="width: 20px; height: 20px; background-color: var(--bs-{{ $status->color }}); border-radius: 4px;">
                                                    </div>
                                                    {{ ucfirst($status->color) }}
                                                </div>
                                            </td>
                                            <td class="bg-transparent align-middle">
                                                @if ($status->is_default)
                                                    <span class="badge bg-success">Default</span>
                                                @endif
                                            </td>
                                            <td class="bg-transparent align-middle text-end">
                                                <button class="btn btn-sm btn-outline-info me-1"
                                                    onclick="editStatus({{ $status->id }}, '{{ $status->name }}', '{{ $status->color }}', {{ $status->order }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                @if (!$status->is_default && $status->tasks_count == 0)
                                                    <form action="{{ route('admin.statuses.delete', $status->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this status?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
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
            <select class="form-select" id="color" name="color" required>
                <option value="primary">Primary (Blue)</option>
                <option value="secondary">Secondary (Gray)</option>
                <option value="success">Success (Green)</option>
                <option value="danger">Danger (Red)</option>
                <option value="warning">Warning (Yellow)</option>
                <option value="info">Info (Cyan)</option>
                <option value="dark">Dark</option>
            </select>
        </div>
    </x-modal>

    <!-- Edit Status Modal -->
    <x-modal id="editStatusModal" title="Edit Status" submitText="Update" formAction="#" id="editStatusModalRoot">
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
            <select class="form-select" id="edit_color" name="color" required>
                <option value="primary">Primary (Blue)</option>
                <option value="secondary">Secondary (Gray)</option>
                <option value="success">Success (Green)</option>
                <option value="danger">Danger (Red)</option>
                <option value="warning">Warning (Yellow)</option>
                <option value="info">Info (Cyan)</option>
                <option value="dark">Dark</option>
            </select>
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
