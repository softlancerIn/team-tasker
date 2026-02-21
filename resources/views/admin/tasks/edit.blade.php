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
                        <textarea id="mytextarea" class="form-control bg-transparent text-white border-secondary" name="description"
                            rows="4">{{ $task->description }}</textarea>
                        @error('description')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Parent Task (for Subtasks)</label>
                            <select name="parent_id" class="form-select bg-transparent text-white border-secondary">
                                <option value="" class="bg-dark">None (Top-Level Task)</option>
                                @foreach ($parentTasks as $ptask)
                                    <option value="{{ $ptask->id }}"
                                        {{ $task->parent_id == $ptask->id ? 'selected' : '' }} class="bg-dark">
                                        #{{ $ptask->id }} - {{ $ptask->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dependencies (Blockers)</label>
                            @php $depIds = $task->dependencies->pluck('depends_on_id')->toArray(); @endphp
                            <select name="dependencies[]" class="form-select bg-transparent text-white border-secondary"
                                multiple>
                                @foreach ($allTasks as $atask)
                                    <option value="{{ $atask->id }}"
                                        {{ in_array($atask->id, $depIds) ? 'selected' : '' }} class="bg-dark">
                                        #{{ $atask->id }} - {{ $atask->title }}</option>
                                @endforeach
                            </select>
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
                            <select name="assigned_to" class="form-select bg-transparent text-white border-secondary">
                                <option value="" class="bg-dark">Unassigned</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" class="bg-dark"
                                        {{ $task->assigned_to == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->role->name ?? 'No Role' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status_id" class="form-select bg-transparent text-white border-secondary"
                                required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ $task->status_id == $status->id ? 'selected' : '' }} class="bg-dark">
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select bg-transparent text-white border-secondary">
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority }}"
                                        {{ $task->priority == $priority ? 'selected' : '' }} class="bg-dark">
                                        {{ $priority }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            @php $tagIds = $task->tags->pluck('id')->toArray(); @endphp
                            <select name="tags[]" class="form-select bg-transparent text-white border-secondary"
                                multiple>
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ in_array($tag->id, $tagIds) ? 'selected' : '' }} class="bg-dark">
                                        {{ $tag->name }}</option>
                                @endforeach
                            </select>
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
                            <select name="recurring_interval"
                                class="form-select bg-transparent text-white border-secondary form-select-sm">
                                <option value="daily" {{ $task->recurring_interval == 'daily' ? 'selected' : '' }}
                                    class="bg-dark">Daily</option>
                                <option value="weekly" {{ $task->recurring_interval == 'weekly' ? 'selected' : '' }}
                                    class="bg-dark">Weekly</option>
                                <option value="monthly" {{ $task->recurring_interval == 'monthly' ? 'selected' : '' }}
                                    class="bg-dark">Monthly</option>
                                <option value="yearly" {{ $task->recurring_interval == 'yearly' ? 'selected' : '' }}
                                    class="bg-dark">Yearly</option>
                            </select>
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

        const savedTheme = localStorage.getItem('theme') || 'dark';
        const isDark = savedTheme === 'dark';

        tinymce.init({
            selector: '#mytextarea,#longtextarea',
            height: 400,
            skin: isDark ? 'oxide-dark' : 'oxide',
            content_css: isDark ? 'dark' : 'default',
            branding: false,
            placeholder: 'Describe the task in detail...',
            plugins: [
                'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
                'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen',
                'insertdatetime',
                'media', 'table', 'emoticons', 'help'
            ],
            menubar: true,
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code | styleselect',
            extended_valid_elements: 'i[class|style],table[class|style],th[class|style],td[class|style],h1[class|style],h2[class|style],h3[class|style],h4[class|style],h5[class|style],h6[class|style]',
            valid_elements: '*[*]',
            content_css: false,
            content_style: isDark ?
                'body { background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent), #1a2436; color: rgba(255, 255, 255, 0.8); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; } i { font-style: italic; } body.mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before { color: rgba(255, 255, 255, 0.4); }' :
                'body { background: #ffffff; color: #333; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; } i { font-style: italic; }',
            entity_encoding: 'raw',
            remove_trailing_brs: false,
            valid_children: '+body[style|i]',
            setup: function(editor) {
                editor.on('init', function() {
                    const container = editor.getContainer();
                    if (container) {
                        container.style.border = isDark ? '1px solid rgba(99, 102, 241, 0.3)' :
                            '1px solid #ced4da';
                        container.style.borderRadius = '8px';
                    }
                });
            }
        });

        feather.replace()
    </script>
</x-admin>
