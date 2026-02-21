<x-client title="Ticket #{{ $ticket->id }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary btn-sm mb-2">
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

    <div class="glass-card mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="avatar icon-accent" style="width: 40px; height: 40px; border-radius: 50%;">
                @if ($ticket->user && $ticket->user->profile_image)
                    <img src="{{ asset('storage/' . $ticket->user->profile_image) }}" class="rounded-circle"
                        width="40" height="40">
                @else
                    <i class="fas fa-user"></i>
                @endif
            </div>
            <div>
                <div class="fw-bold text-white">Me</div>
                <small class="text-muted">{{ $ticket->created_at->format('M d, Y H:i') }}</small>
            </div>
        </div>
        <div class="text-main">
            {!! $ticket->body !!}
        </div>
    </div>

    <!-- Replies -->
    @foreach ($ticket->replies as $reply)
        <div class="glass-card mb-4 {{ $reply->type == 'internal' ? 'border-primary' : '' }}"
            style="{{ $reply->type == 'internal' ? 'background: rgba(99, 102, 241, 0.05);' : '' }}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar {{ $reply->type == 'internal' ? 'icon-primary' : 'icon-accent' }}"
                        style="width: 40px; height: 40px; border-radius: 50%;">
                        @if ($reply->user && $reply->user->profile_image)
                            <img src="{{ asset('storage/' . $reply->user->profile_image) }}" class="rounded-circle"
                                width="40" height="40">
                        @else
                            <i class="fas fa-{{ $reply->type == 'internal' ? 'headset' : 'user' }}"></i>
                        @endif
                    </div>
                    <div>
                        <div class="fw-bold text-white">
                            {{ $reply->user ? $reply->user->name : 'Support Team' }}
                            @if ($reply->type == 'internal')
                                <span class="badge bg-primary ms-2" style="font-size: 0.6rem;">Agent</span>
                            @endif
                        </div>
                        <small class="text-muted">
                            {{ $reply->created_at->format('M d, Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
            <div class="text-main">
                {!! $reply->body !!}
            </div>
        </div>
    @endforeach

    <!-- Reply Form -->
    <div class="glass-card">
        <h5 class="text-white mb-3">Reply</h5>
        <form action="{{ route('client.tickets.reply', $ticket->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <textarea id="reply-editor" name="body" class="form-control" rows="5"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane me-1"></i> Send Reply
            </button>
        </form>
    </div>

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
</x-client>
