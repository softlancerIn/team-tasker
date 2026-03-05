<x-admin>
    <x-slot:title>
        Edit Task | Team Tasker
    </x-slot:title>

    <div class="glass-card">
        <h4 class="mb-4">Edit Task #{{ $task->id }}</h4>
        <form action="{{ route('update', $task->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Task Title</label>
                        <input type="text" class="form-control bg-transparent text-white border-secondary"
                            name="title" value="{{ $task->title }}" required>
                        @error('title')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <x-textarea id="mytextarea" name="description"
                            texteditor="true">{{ $task->description }}</x-textarea>
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
                                        {{ $task->parent_id == $ptask->id ? 'selected' : '' }} class="bg-dark">
                                        #{{ $ptask->id }} - {{ $ptask->title }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dependencies (Blockers)</label>
                            @php $depIds = $task->dependencies->pluck('depends_on_id')->toArray(); @endphp
                            <x-multiselect name="dependencies[]" placeholder="Select Dependencies" :selected="$depIds">
                                @foreach ($allTasks as $atask)
                                    <option value="{{ $atask->id }}"
                                        {{ in_array($atask->id, $depIds) ? 'selected' : '' }} class="bg-dark">
                                        #{{ $atask->id }} - {{ $atask->title }}</option>
                                @endforeach
                            </x-multiselect>
                            <div class="text-muted extra-small mt-1">Hold Ctrl/Cmd to select multiple</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Attachments</label>
                        @if ($task->attachments->count() > 0)
                            <div class="mb-2">
                                <label class="extra-small text-muted">Current Attachments:</label>
                                <ul class="list-unstyled">
                                    @foreach ($task->attachments as $attachment)
                                        <li class="extra-small">
                                            <i class="fas fa-paperclip me-1"></i>
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                                                class="text-primary">{{ $attachment->file_name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <input type="file" name="attachments[]"
                            class="form-control bg-transparent text-white border-secondary" multiple>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="p-3 rounded-3"
                        style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assign To</label>
                            <x-select id="assigned_to" name="assigned_to" placeholder="Unassigned" :selected="$task->assigned_to">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" class="bg-dark">
                                        {{ $user->name }} ({{ $user->role->name ?? 'No Role' }})
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <x-select id="status_id" name="status_id" placeholder="Select Status" required="true"
                                :selected="$task->status_id">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" class="bg-dark">
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <x-select id="priority" name="priority" placeholder="Select Priority" :selected="$task->priority">
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority }}" class="bg-dark">
                                        {{ $priority }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <x-multiselect id="tags" name="tags[]" placeholder="Select Tags" :selected="$task->tags->pluck('id')->toArray()">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}" class="bg-dark">
                                        {{ $tag->name }}</option>
                                @endforeach
                            </x-multiselect>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estimated Hours</label>
                            <input type="number" step="0.5" name="estimated_hours"
                                value="{{ $task->estimated_hours }}"
                                class="form-control bg-transparent text-white border-secondary" placeholder="e.g. 5.5">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deadline</label>
                            <input type="datetime-local" name="deadline"
                                value="{{ $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : '' }}"
                                class="form-control bg-transparent text-white border-secondary">
                        </div>

                        <hr class="border-secondary opacity-25">

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1"
                                id="recurSwitch" {{ $task->is_recurring ? 'checked' : '' }} onchange="toggleRecur()">
                            <label class="form-check-label" for="recurSwitch">Recurring Task</label>
                        </div>

                        <div id="recurSettings" style="{{ $task->is_recurring ? '' : 'display: none;' }}">
                            <x-select name="recurring_interval" placeholder="Select Interval">
                                <option value="daily" {{ $task->recurring_interval == 'daily' ? 'selected' : '' }}
                                    class="bg-dark">Daily</option>
                                <option value="weekly" {{ $task->recurring_interval == 'weekly' ? 'selected' : '' }}
                                    class="bg-dark">Weekly</option>
                                <option value="monthly" {{ $task->recurring_interval == 'monthly' ? 'selected' : '' }}
                                    class="bg-dark">Monthly</option>
                                <option value="yearly" {{ $task->recurring_interval == 'yearly' ? 'selected' : '' }}
                                    class="bg-dark">Yearly</option>
                            </x-select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Update Task
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


        feather.replace()
    </script>
</x-admin>
