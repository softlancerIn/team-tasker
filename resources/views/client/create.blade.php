<x-client title="Create Ticket">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card">
                <h4 class="text-white mb-4">Create New Ticket</h4>
                <form action="{{ route('client.tickets.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-white">Subject</label>
                        <input type="text" name="subject" class="form-control" required
                            placeholder="Brief description of the issue">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low - General Question</option>
                            <option value="medium">Medium - Detailed Issue</option>
                            <option value="high">High - Urgent Problem</option>
                            <option value="urgent">Urgent - System Down</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">Message</label>
                        <textarea id="ticket-body" name="body" class="form-control" rows="8"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Submit Ticket
                    </button>
                    <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
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
