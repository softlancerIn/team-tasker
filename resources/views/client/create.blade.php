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
                        <x-select name="priority" placeholder="Select Priority">
                            <option value="low" class="bg-dark">Low - General Question</option>
                            <option value="medium" class="bg-dark">Medium - Detailed Issue</option>
                            <option value="high" class="bg-dark">High - Urgent Problem</option>
                            <option value="urgent" class="bg-dark">Urgent - System Down</option>
                        </x-select>
                    </div>
                    <div class="mb-4">
                        <label class="heading-label mb-2">Detailed Description</label>
                        <x-textarea id="ticket-body" name="body" class="form-premium-control" rows="8"
                            texteditor="true"></x-textarea>
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

</x-client>
