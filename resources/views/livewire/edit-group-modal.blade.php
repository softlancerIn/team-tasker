<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component {
    public $conversationId;
    public $name = '';
    public $selectedUsers = [];
    public $users = [];

    public function mount()
    {
        $this->users = User::where('id', '!=', Auth::id())->get();
    }

    #[On('openEditGroupModal')]
    public function loadGroup($conversationId)
    {
        $conversation = Conversation::with('participants')->find($conversationId);
        if ($conversation && $conversation->type === 'group') {
            $this->conversationId = $conversation->id;
            $this->name = $conversation->name;
            // Get all participant IDs except the current user (though current user is a participant)
            // Ideally we show everyone, but let's exclude Auth user from the selection list to avoid accidental self-removal if logic allows
            // or just pre-select them.
            // Let's filter out Auth ID for the select list logic if the list UI excludes Auth user.

            $this->selectedUsers = $conversation->participants->pluck('id')->reject(fn($id) => $id == Auth::id())->toArray();

            $this->dispatch('open-modal', id: 'editGroupModal');
        }
    }

    public function updateGroup()
    {
        $this->validate([
            'name' => 'required|min:3',
            'selectedUsers' => 'required|array|min:1', // At least one other person
        ]);

        $conversation = Conversation::find($this->conversationId);

        if ($conversation && $conversation->type === 'group') {
            $conversation->update(['name' => $this->name]);

            // Sync participants: Auth user + selected users
            $participantIds = array_merge([Auth::id()], $this->selectedUsers);
            $conversation->participants()->sync($participantIds);

            $this->dispatch('groupUpdated', conversationId: $conversation->id);
            $this->dispatch('close-modal', id: 'editGroupModal');
        }
    }
}; ?>

<div>
    <x-modal id="editGroupModal" title="Edit Group Chat">
        <form wire:submit.prevent="updateGroup">
            <div class="mb-3">
                <label class="form-label text-main">Group Name</label>
                <input type="text" wire:model="name" class="form-control" placeholder="Group Name" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-main">Members</label>
                <div class="d-flex flex-column gap-2 overflow-auto" style="max-height: 200px;">
                    @foreach ($users as $user)
                        <label
                            class="d-flex align-items-center gap-2 p-2 border border-secondary border-opacity-10 rounded cursor-pointer hover-bg-light">
                            <input type="checkbox" wire:model="selectedUsers" value="{{ $user->id }}"
                                class="form-check-input">
                            <span>{{ $user->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </x-modal>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('open-modal', (event) => {
                const modalEl = document.getElementById(event.id);
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            });
            @this.on('close-modal', (event) => {
                const modalEl = document.getElementById(event.id);
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
                // Also hide explicitly if getInstance fails (sometimes happens if not fully init)
                if (!modal) {
                    const bsModal = new bootstrap.Modal(modalEl);
                    bsModal.hide();
                }
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            });
        });
    </script>
</div>
