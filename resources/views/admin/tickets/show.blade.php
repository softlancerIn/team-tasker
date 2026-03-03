<x-admin title="Ticket #{{ $ticket->id }}">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <a href="{{ route('admin.tickets.index') }}" class="btn-premium btn-premium-secondary btn-sm mb-3 px-3 py-1"
                style="font-size: 0.8rem;">
                <i class="fas fa-arrow-left me-1"></i> Back to Archive
            </a>
            <h2 class="h3 fw-bold mb-0">
                <span class="text-low">#{{ $ticket->id }}</span> - {{ $ticket->subject }}
            </h2>
        </div>
        <div class="d-flex gap-3">
            <span class="badge-premium px-3 py-2"
                style="background: {{ $ticket->status == 'open' ? 'rgba(var(--accent-rgb), 0.1)' : 'var(--bg-input)' }}; color: {{ $ticket->status == 'open' ? 'var(--accent)' : 'var(--text-medium)' }}; border: 1px solid {{ $ticket->status == 'open' ? 'rgba(var(--accent-rgb), 0.2)' : 'var(--border-main)' }}; white-space: nowrap;">
                <i class="fas fa-circle me-1" style="font-size: 0.5rem; vertical-align: middle;"></i>
                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Conversation Column -->
        <div class="col-lg-8">
            <!-- Original Ticket Body -->
            <div class="glass-card mb-4"
                style="border: 1px solid var(--border-main); position: relative; overflow: hidden;">
                <div class="position-absolute top-0 start-0 h-100 bg-primary opacity-10" style="width: 4px;"></div>
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-premium"
                            style="width: 52px; height: 52px; border: 2px solid var(--border-main);">
                            @if ($ticket->user && $ticket->user->profile_image)
                                <img src="{{ asset('storage/' . $ticket->user->profile_image) }}" alt="Avatar">
                            @else
                                <div class="d-flex align-items-center justify-content-center w-100 h-100"
                                    style="background: var(--bg-input); color: var(--text-high); font-weight: 700;">
                                    {{ substr($ticket->user ? $ticket->user->name : $ticket->email_source, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="fw-bold mb-1" style="color: var(--text-high); font-size: 1.05rem;">
                                {{ $ticket->user ? $ticket->user->name : $ticket->email_source }}
                            </div>
                            <div class="text-low d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                <span>{{ $ticket->created_at->format('M d, Y • H:i') }}</span>
                                <span class="badge-premium py-0 px-2"
                                    style="background: var(--bg-input); font-size: 0.65rem;">Client</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-main mb-4" style="line-height: 1.7; color: var(--text-medium); font-size: 0.95rem;">
                    {!! $ticket->body !!}
                </div>

                @if ($ticket->attachments)
                    <div class="mt-4 pt-4 border-top border-subtle">
                        <h6 class="heading-label mb-3" style="font-size: 0.7rem;"><i class="fas fa-paperclip me-1"></i>
                            Original Attachments</h6>
                        <a href="{{ asset('storage/' . $ticket->attachments) }}" target="_blank"
                            class="btn-premium btn-premium-secondary btn-sm px-3 py-2" style="font-size: 0.8rem;">
                            <i class="fas fa-cloud-download-alt me-2"></i> Download Document
                        </a>
                    </div>
                @endif
            </div>

            <!-- Replies -->
            @foreach ($ticket->replies as $reply)
                <div class="glass-card mb-4"
                    style="{{ $reply->is_private ? 'background: rgba(var(--accent-rgb), 0.03); border: 1px solid rgba(var(--accent-rgb), 0.15);' : 'border: 1px solid var(--border-subtle);' }} position: relative; overflow: hidden;">

                    @if ($reply->is_private)
                        <div class="position-absolute top-0 start-0 h-100 bg-accent opacity-20" style="width: 4px;">
                        </div>
                        <div class="heading-label d-inline-flex align-items-center mb-3"
                            style="color: var(--accent); font-size: 0.65rem; background: rgba(var(--accent-rgb), 0.05); padding: 2px 8px; border-radius: 4px;">
                            <i class="fas fa-lock me-2"></i> Internal Team Note
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-premium"
                                style="width: 44px; height: 44px; border: 2px solid {{ $reply->type == 'internal' ? 'var(--primary)' : 'var(--border-main)' }};">
                                @if ($reply->user && $reply->user->profile_image)
                                    <img src="{{ asset('storage/' . $reply->user->profile_image) }}" alt="Avatar">
                                @else
                                    <div class="d-flex align-items-center justify-content-center w-100 h-100"
                                        style="background: var(--bg-input); color: var(--text-high); font-weight: 600;">
                                        {{ substr($reply->user ? $reply->user->name : 'C', 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="fw-bold" style="color: var(--text-high); font-size: 0.95rem;">
                                    {{ $reply->user ? $reply->user->name : 'Client Contact' }}
                                    @if ($reply->type == 'internal')
                                        <span class="badge-premium py-0 px-2 ms-2"
                                            style="font-size: 0.6rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">Agent</span>
                                    @endif
                                </div>
                                <div style="font-size: 0.7rem; color: var(--text-low);">
                                    {{ $reply->created_at->format('M d, Y • H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-main mb-3" style="color: var(--text-medium); line-height: 1.6; font-size: 0.9rem;">
                        {!! $reply->body !!}
                    </div>

                    @if ($reply->attachments)
                        <div class="mt-3 text-end">
                            <a href="{{ asset('storage/' . $reply->attachments) }}" target="_blank"
                                class="btn-premium btn-premium-secondary py-1 px-3"
                                style="font-size: 0.75rem; background: var(--bg-input);">
                                <i class="fas fa-paperclip me-1"></i> View Attachment
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Reply Form -->
            <div class="glass-card" style="border: 1px solid var(--border-main);">
                <h5 class="fw-bold mb-4" style="color: var(--text-high);">Resolution & Reply</h5>
                <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <textarea id="reply-editor" name="body" class="form-premium-control" rows="5"></textarea>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="heading-label mb-2" style="font-size: 0.7rem;">Attachments</label>
                            <input type="file" name="attachments" class="form-premium-control py-2 px-3"
                                style="font-size: 0.8rem; background: var(--bg-input);">
                        </div>
                        <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-4">
                            <div class="form-check form-switch p-0 m-0 d-flex align-items-center gap-3">
                                <label class="fw-medium small mb-0" for="internalNote"
                                    style="color: var(--text-medium); cursor: pointer; order: 1;">Internal Note</label>
                                <input class="form-check-input ms-0" type="checkbox" id="internalNote"
                                    name="is_internal_note"
                                    style="cursor: pointer; order: 2; width: 32px; height: 16px;">
                            </div>
                            <button type="submit" class="btn-premium btn-premium-primary px-4 py-2">
                                <i class="fas fa-paper-plane me-2"></i> Post Reply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Details Card -->
            <div class="glass-card mb-4" style="border: 1px solid var(--border-main);">
                <h5 class="fw-bold mb-4" style="color: var(--text-high);">Management Sidebar</h5>

                <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="mb-4">
                    @csrf
                    <label class="heading-label mb-2" style="font-size: 0.7rem;">Update Status</label>
                    <select name="status" class="form-premium-control py-2 px-3" onchange="this.form.submit()"
                        style="font-size: 0.85rem; background: var(--bg-input);">
                        <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open Ticket</option>
                        <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In
                            Progress</option>
                        <option value="waiting_for_client"
                            {{ $ticket->status == 'waiting_for_client' ? 'selected' : '' }}>Awaiting Feedback</option>
                        <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Close Discussion
                        </option>
                        <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Mark Resolved
                        </option>
                    </select>
                </form>

                <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="mb-4">
                    @csrf
                    <label class="heading-label mb-2" style="font-size: 0.7rem;">Ticket Priority</label>
                    <select name="priority" class="form-premium-control py-2 px-3" onchange="this.form.submit()"
                        style="font-size: 0.85rem; background: var(--bg-input);">
                        <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>Low Priority
                        </option>
                        <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Medium Priority
                        </option>
                        <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>High Priority
                        </option>
                        <option value="urgent" {{ $ticket->priority == 'urgent' ? 'selected' : '' }}>Urgent Priority
                        </option>
                    </select>
                </form>

                <div class="mb-4">
                    <label class="heading-label mb-2" style="font-size: 0.7rem;">Contact Reference</label>
                    <div class="p-2 rounded"
                        style="background: var(--bg-input); border: 1px solid var(--border-subtle);">
                        <div class="small fw-medium text-truncate" style="color: var(--text-high);">
                            {{ $ticket->user ? $ticket->user->email : $ticket->email_source }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-top border-subtle">
                    <button type="button"
                        class="btn-premium btn-premium-secondary w-100 py-3 d-flex align-items-center justify-content-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#confirmConvertModal"
                        style="color: var(--primary); background: rgba(var(--primary-rgb), 0.05); border: 1px dashed var(--primary);">
                        <i class="fas fa-project-diagram"></i> <span class="fw-bold">Promote to Task</span>
                    </button>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div class="modal fade" id="confirmConvertModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content overflow-hidden border-0 shadow-premium"
                        style="background: var(--bg-sidebar); border-radius: var(--radius-lg);">
                        <div class="modal-header border-0 p-4 pb-0">
                            <h5 class="modal-title fw-bold" style="color: var(--text-high);">Workflow Promotion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="text-low mb-4" style="font-size: 0.9rem;">You are promoting this support inquiry
                                to a formal biological task. This creates a traceable development thread while keeping
                                the original context linked.</p>

                            <div class="p-3 rounded-premium mb-4"
                                style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid rgba(var(--primary-rgb), 0.1);">
                                <div class="d-flex gap-3">
                                    <div style="color: var(--primary);"><i class="fas fa-info-circle fs-4"></i></div>
                                    <div class="small text-low">
                                        <div class="fw-bold text-high mb-1">Automation Sync</div>
                                        <ul class="ps-3 mb-0">
                                            <li>Task will inherit Title, Body, and Priority.</li>
                                            <li>Seamless navigation between Ticket and Task.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('admin.tickets.convert_to_task', $ticket->id) }}" method="POST">
                                @csrf
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn-premium btn-premium-primary py-3 fw-bold">
                                        <i class="fas fa-check-circle me-2"></i> Confirm Promotion
                                    </button>
                                    <button type="button"
                                        class="btn btn-link text-low text-decoration-none py-2 border-0"
                                        data-bs-dismiss="modal">Maintain as Ticket</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Card -->
            <div class="glass-card mb-4" style="border: 1px solid var(--border-main);">
                <h5 class="fw-bold mb-4" style="color: var(--text-high);">Agent Assignment</h5>
                <form action="{{ route('admin.tickets.assign', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="heading-label mb-2" style="font-size: 0.7rem;">Select Responsible Agent</label>
                        <select name="assigned_to" class="form-premium-control py-2 px-3"
                            style="font-size: 0.85rem; background: var(--bg-input);">
                            <option value="">-- No Assignment --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ $ticket->assigned_to == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="btn-premium btn-premium-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-user-check"></i> <span>Sync Assignment</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin>
