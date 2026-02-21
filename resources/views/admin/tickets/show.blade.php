<x-admin title="Ticket #{{ $ticket->id }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2 class="h4 text-white">
                #{{ $ticket->id }} - {{ $ticket->subject }}
            </h2>
        </div>
        <div class="d-flex gap-2">
            <span
                class="badge bg-{{ $ticket->status == 'open' ? 'success' : ($ticket->status == 'closed' ? 'secondary' : 'warning') }} fs-6">
                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Conversation Column -->
        <div class="col-lg-8">
            <!-- Original Ticket Body -->
            <div class="glass-card mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar icon-primary" style="width: 40px; height: 40px; border-radius: 50%;">
                            @if ($ticket->user && $ticket->user->profile_image)
                                <img src="{{ asset('storage/' . $ticket->user->profile_image) }}" class="rounded-circle"
                                    width="40" height="40">
                            @else
                                <i class="fas fa-user"></i>
                            @endif
                        </div>
                        <div>
                            <div class="fw-bold text-white">
                                {{ $ticket->user ? $ticket->user->name : $ticket->email_source }}
                            </div>
                            <small class="text-muted">
                                {{ $ticket->created_at->format('M d, Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="text-main mb-3">
                    {!! $ticket->body !!}
                </div>

                @if ($ticket->attachments)
                    <div class="mt-3 pt-3 border-top border-secondary">
                        <h6 class="text-white small mb-2"><i class="fas fa-paperclip me-1"></i> Attachments</h6>
                        <a href="{{ asset('storage/' . $ticket->attachments) }}" target="_blank"
                            class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-1"></i> Download Attachment
                        </a>
                    </div>
                @endif
            </div>

            <!-- Replies -->
            @foreach ($ticket->replies as $reply)
                <div class="glass-card mb-4 {{ $reply->is_private ? 'border-warning' : ($reply->type == 'internal' ? 'border-primary' : '') }}"
                    style="{{ $reply->is_private ? 'background: rgba(245, 158, 11, 0.05);' : ($reply->type == 'internal' ? 'background: rgba(99, 102, 241, 0.05);' : '') }}">

                    @if ($reply->is_private)
                        <div class="badge bg-warning text-dark mb-2"><i class="fas fa-lock me-1"></i> Internal Note
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar {{ $reply->type == 'internal' ? 'icon-accent' : 'icon-primary' }}"
                                style="width: 40px; height: 40px; border-radius: 50%;">
                                @if ($reply->user && $reply->user->profile_image)
                                    <img src="{{ asset('storage/' . $reply->user->profile_image) }}"
                                        class="rounded-circle" width="40" height="40">
                                @else
                                    <i class="fas fa-{{ $reply->type == 'internal' ? 'user-shield' : 'user' }}"></i>
                                @endif
                            </div>
                            <div>
                                <div class="fw-bold text-white">
                                    {{ $reply->user ? $reply->user->name : 'Client' }}
                                    @if ($reply->type == 'internal')
                                        <span class="badge bg-primary ms-2" style="font-size: 0.6rem;">Staff</span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $reply->created_at->format('M d, Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="text-main mb-3">
                        {!! $reply->body !!}
                    </div>

                    @if ($reply->attachments)
                        <div class="mt-2 text-end">
                            <a href="{{ asset('storage/' . $reply->attachments) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-paperclip me-1"></i> Attachment
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Reply Form -->
            <div class="glass-card">
                <h5 class="text-white mb-3">Reply to Ticket</h5>
                <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <textarea id="reply-editor" name="body" class="form-control" rows="5"></textarea>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label text-white small">Attachments</label>
                            <input type="file" name="attachments" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="internalNote"
                                    name="is_internal_note">
                                <label class="form-check-label text-white small" for="internalNote">Internal
                                    Note</label>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Send Reply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Details Card -->
            <div class="glass-card mb-4">
                <h5 class="text-white mb-3">Ticket Details</h5>

                <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="mb-3">
                    @csrf
                    <label class="text-muted small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm mb-2" onchange="this.form.submit()">
                        <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In
                            Progress</option>
                        <option value="waiting_for_client"
                            {{ $ticket->status == 'waiting_for_client' ? 'selected' : '' }}>Waiting for Client</option>
                        <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved
                        </option>
                    </select>
                </form>

                <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="mb-3">
                    @csrf
                    <label class="text-muted small mb-1">Priority</label>
                    <select name="priority" class="form-select form-select-sm mb-2" onchange="this.form.submit()">
                        <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ $ticket->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </form>

                <div class="mb-3">
                    <label class="text-muted small">Email</label>
                    <div class="text-white text-break">
                        {{ $ticket->user ? $ticket->user->email : $ticket->email_source }}
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary">
                    <button type="button" class="btn btn-warning btn-sm w-100 fw-bold" data-bs-toggle="modal"
                        data-bs-target="#confirmConvertModal">
                        <i class="fas fa-tasks me-1"></i> Convert to Task
                    </button>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div class="modal fade" id="confirmConvertModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content overflow-hidden border-0"
                        style="background: var(--card-bg); border-radius: 12px;">
                        <div class="modal-header border-0 p-4 pb-0">
                            <h5 class="modal-title text-white">Convert to Task?</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="text-main-50 mb-4">You are about to convert this ticket into a formal task. This
                                will allow you to track development progress and keep the client updated independently
                                of the support thread.</p>

                            <div class="p-3 rounded-3 mb-4"
                                style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.2);">
                                <div class="d-flex gap-3">
                                    <div class="text-warning"><i class="fas fa-info-circle fs-4"></i></div>
                                    <div class="small text-main-50">
                                        <div class="fw-bold text-white mb-1">What happens next?</div>
                                        <ul class="ps-3 mb-0">
                                            <li>A new task will be created with the ticket subject and body.</li>
                                            <li>The task will be linked to this ticket for easy navigation.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('admin.tickets.convert_to_task', $ticket->id) }}" method="POST">
                                @csrf
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-warning fw-bold py-2">
                                        <i class="fas fa-check me-2"></i> Yes, Convert Ticket
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary py-2 border-0"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Card -->
            <div class="glass-card mb-4">
                <h5 class="text-white mb-3">Assignment</h5>
                <form action="{{ route('admin.tickets.assign', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ $ticket->assigned_to == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        Update Assignment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Init TinyMCE -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            const isDark = savedTheme === 'dark';

            tinymce.init({
                selector: '#reply-editor',
                height: 300,
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? 'dark' : 'default',
                menubar: false,
                statusbar: false,
                branding: false,
                plugins: 'autolink lists link image charmap preview anchor',
                toolbar: 'undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
                content_style: isDark ?
                    'body { background: transparent; color: rgba(255, 255, 255, 0.8); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; margin: 10px; }' :
                    'body { background: transparent; color: #333; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; margin: 10px; }'
            });
        });
    </script>
</x-admin>
