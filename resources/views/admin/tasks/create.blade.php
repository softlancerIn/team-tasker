<x-admin>
    <x-slot:title>
        Create Task | Team Tasker
    </x-slot:title>

    <div class="glass-card mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-magic text-primary"></i>
            <div class="flex-grow-1">
                <label class="form-label mb-0 small">Quick Start: Load from Template</label>
                <x-select id="templateSelector" name="template_id" placeholder="Select a template...">
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" class="bg-dark"
                            data-structure="{{ json_encode($template->structure) }}">{{ $template->name }}</option>
                    @endforeach
                </x-select>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <h4 class="mb-4">Create New Task</h4>
        <form action="{{ route('store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Task Title</label>
                        <input type="text" class="form-control bg-transparent text-white border-secondary"
                            name="title" placeholder="e.g. Design Landing Page" required>
                        @error('title')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <x-textarea id="mytextarea" name="description" placeholder="Detailed description of the task..."
                            texteditor="true"></x-textarea>
                        @error('description')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Parent Task (for Subtasks)</label>
                            <x-select name="parent_id" placeholder="None (Top-Level Task)">
                                <option value="" class="bg-dark">None (Top-Level Task)</option>
                                @foreach ($parentTasks as $ptask)
                                    <option value="{{ $ptask->id }}"
                                        {{ ($selectedParentId ?? null) == $ptask->id ? 'selected' : '' }}
                                        class="bg-dark">#{{ $ptask->id }} - {{ $ptask->title }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dependencies (Blockers)</label>
                            <x-multiselect id="dependencies" name="dependencies[]" placeholder="Select Dependencies">
                                @foreach ($allTasks as $atask)
                                    <option value="{{ $atask->id }}" class="bg-dark">#{{ $atask->id }} -
                                        {{ $atask->title }}</option>
                                @endforeach
                            </x-multiselect>
                            <div class="text-muted extra-small mt-1">Hold Ctrl/Cmd to select multiple</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Attachments</label>
                        <input type="file" name="attachments[]"
                            class="form-control bg-transparent text-white border-secondary" multiple>
                        <div class="text-muted extra-small mt-1">Max 10MB per file. Multiple files allowed.</div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="p-3 rounded-3"
                        style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assign To</label>
                            <x-select id="assigned_to" name="assigned_to" placeholder="Unassigned">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" class="bg-dark">{{ $user->name }}
                                        ({{ $user->role->name ?? 'No Role' }})
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <x-select id="status_id" name="status_id" placeholder="Select Status" required="true">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" {{ $status->is_default ? 'selected' : '' }}
                                        class="bg-dark">
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <x-select id="priority" name="priority" placeholder="Select Priority">
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority }}" {{ $priority == 'Medium' ? 'selected' : '' }}
                                        class="bg-dark">{{ $priority }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <x-multiselect id="tags" name="tags[]" placeholder="Select Tags">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}" class="bg-dark">{{ $tag->name }}</option>
                                @endforeach
                            </x-multiselect>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estimated Hours</label>
                            <input type="number" step="0.5" name="estimated_hours"
                                class="form-control bg-transparent text-white border-secondary" placeholder="e.g. 5.5">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deadline</label>
                            <input type="datetime-local" name="deadline"
                                class="form-control bg-transparent text-white border-secondary">
                        </div>

                        <hr class="border-secondary opacity-25">

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1"
                                id="recurSwitch" onchange="toggleRecur()">
                            <label class="form-check-label" for="recurSwitch">Recurring Task</label>
                        </div>

                        <div id="recurSettings" style="display: none;">
                            <x-select name="recurring_interval"
                                class="form-select bg-transparent text-white border-secondary form-select-sm"
                                placeholder="Select Interval">
                                <option value="daily" class="bg-dark">Daily</option>
                                <option value="weekly" class="bg-dark">Weekly</option>
                                <option value="monthly" class="bg-dark">Monthly</option>
                                <option value="yearly" class="bg-dark">Yearly</option>
                            </x-select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-plus me-1"></i> Create Task
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleRecur() {
            const settings = document.getElementById('recurSettings');
            const isChecked = document.getElementById('recurSwitch').checked;
            settings.style.display = isChecked ? 'block' : 'none';
        }
    </script>


    <script>
        setTimeout(function() {
            $('.alert-success,.alert-danger').fadeOut('fast');
        }, 3000);


        feather.replace();

        document.getElementById('templateSelector').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.value) return;

            const structure = JSON.parse(selectedOption.getAttribute('data-structure'));

            if (structure.title) document.getElementsByName('title')[0].value = structure.title;

            if (structure.priority) {
                const el = document.getElementsByName('priority')[0];
                el.value = structure.priority;
                if (el.tomselect) el.tomselect.sync();
            }

            if (structure.estimated_hours) document.getElementsByName('estimated_hours')[0].value = structure
                .estimated_hours;

            if (structure.description) {
                tinymce.get('mytextarea').setContent(structure.description);
            }

            if (structure.tags && Array.isArray(structure.tags)) {
                const tagSelect = document.getElementsByName('tags[]')[0];
                if (tagSelect) {
                    const values = structure.tags.map(t => String(t));
                    if (tagSelect.tomselect) {
                        tagSelect.tomselect.setValue(values);
                    } else {
                        Array.from(tagSelect.options).forEach(option => {
                            option.selected = values.includes(option.value);
                        });
                    }
                }
            }
        });
    </script>
</x-admin>
