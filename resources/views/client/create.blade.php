<x-client title="Create Ticket">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card" style="border: 1px solid var(--border-main);">
                <h4 class="fw-bold mb-4" style="color: var(--text-high);">Create New Ticket</h4>
                <form action="{{ route('client.tickets.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="heading-label mb-2">Subject</label>
                        <input type="text" name="subject" class="form-premium-control" required
                            placeholder="What can we help you with?">
                    </div>
                    <div class="mb-4">
                        <label class="heading-label mb-2">Priority</label>
                        <select name="priority" class="form-premium-control">
                            <option value="low">Low - General Question</option>
                            <option value="medium">Medium - Detailed Issue</option>
                            <option value="high">High - Urgent Problem</option>
                            <option value="urgent">Urgent - System Down</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="heading-label mb-2">Detailed Description</label>
                        <textarea id="ticket-body" name="body" class="form-premium-control" rows="8"></textarea>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-4">
                        <button type="submit" class="btn-premium btn-premium-primary">
                            <i class="fas fa-paper-plane me-2"></i> Submit Ticket
                        </button>
                        <a href="{{ route('client.dashboard') }}" class="btn-premium btn-premium-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            const isDark = savedTheme === 'dark';

            tinymce.init({
                selector: '#ticket-body',
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
