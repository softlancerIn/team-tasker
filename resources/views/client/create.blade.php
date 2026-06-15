<x-client title="Create Ticket">
    <div class="top-bar-premium mb-4">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Create New Ticket</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Submit a support request to our team.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('client.dashboard') }}" class="btn-premium btn-premium-secondary px-4 py-2">Cancel</a>
            <button type="submit" form="createTicketForm" class="btn-premium btn-premium-primary px-4 py-2 shadow-sm">
                <i class="fas fa-paper-plane me-2"></i> Submit Ticket
            </button>
        </div>
    </div>

    <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
        <form id="createTicketForm" action="{{ route('client.tickets.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-high">Subject <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-premium-control w-100" required
                    placeholder="What can we help you with?">
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-high">Priority <span class="text-danger">*</span></label>
                <x-select name="priority" placeholder="Select Priority" required>
                    <option value="low" class="bg-dark">Low - General Question</option>
                    <option value="medium" class="bg-dark">Medium - Detailed Issue</option>
                    <option value="high" class="bg-dark">High - Urgent Problem</option>
                    <option value="urgent" class="bg-dark">Urgent - System Down</option>
                </x-select>
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-high">Detailed Description <span class="text-danger">*</span></label>
                <x-textarea id="ticket-body" name="body" class="form-premium-control w-100" rows="8"
                    texteditor="true" required></x-textarea>
            </div>
        </form>
    </div>

</x-client>
