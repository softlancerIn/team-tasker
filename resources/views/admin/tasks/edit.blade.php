<x-admin>
    <x-slot:title>
        Edit Task | Team Tasker
    </x-slot:title>

    <div class="glass-card">
        <h4 class="mb-4">Edit Task</h4>
        <form action="{{ route('update') }}" method="post">
            @csrf
            <input type="hidden" name="id" value="{{ $task->id }}">

            <div class="mb-3">
                <label for="title" class="form-label">Task Title</label>
                <input type="text" class="form-control bg-transparent text-white border-secondary" name="title"
                    value="{{ $task->title }}">
                @if ($errors->has('title'))
                    <div class="text-danger mt-1 small">{{ $errors->first('title') }}</div>
                @endif
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="mytextarea" class="form-control bg-transparent text-white border-secondary" name="description"
                    rows="4">{{ $task->description }}</textarea>
                @if ($errors->has('description'))
                    <div class="text-danger mt-1 small">{{ $errors->first('description') }}</div>
                @endif
            </div>

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

            <div class="mb-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-select bg-transparent text-white border-secondary">
                    <option value="pending" class="bg-dark" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending
                    </option>
                    <option value="in_progress" class="bg-dark" {{ $task->status == 'in_progress' ? 'selected' : '' }}>
                        In Progress</option>
                    <option value="completed" class="bg-dark" {{ $task->status == 'completed' ? 'selected' : '' }}>
                        Completed</option>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Task</button>
            </div>
        </form>
    </div>

    <script>
        setTimeout(function() {
            $('.alert-success,.alert-danger').fadeOut('fast');
        }, 3000);

        tinymce.init({
            selector: '#mytextarea,#longtextarea',
            height: 400,
            skin: 'oxide-dark',
            content_css: 'dark',
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
            content_style: 'body { background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent), #1a2436; color: rgba(255, 255, 255, 0.8); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; } i { font-style: italic; } body.mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before { color: rgba(255, 255, 255, 0.4); }',
            entity_encoding: 'raw',
            remove_trailing_brs: false,
            valid_children: '+body[style|i]',
            setup: function(editor) {
                editor.on('init', function() {
                    const container = editor.getContainer();
                    if (container) {
                        container.style.border = '1px solid rgba(99, 102, 241, 0.3)';
                        container.style.borderRadius = '8px';
                    }
                });
            }
        });

        feather.replace()
    </script>
</x-admin>
