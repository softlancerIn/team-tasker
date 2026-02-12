<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component {
    public $search = '';
    public $selectedConversationId = null;

    public function with()
    {
        $userId = Auth::id();

        // 1. Get Group Conversations for the user
        $conversations = Conversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('type', 'group')
            ->with(['participants', 'latestMessage'])
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query
                        ->where('user_id', '!=', $userId) // Don't count own messages
                        ->where('created_at', '>', function ($q) use ($userId) {
                            // This is a simplified logic. Ideally we use pivot last_read_at.
                            // But pivot access in subquery is tricky.
                            // Let's rely on caching or simpler logic for now, or just `is_read` if we fix the schema.
                            // Given schema: `last_read_at` is on pivot.
                            // Let's use `is_read` column on messages table for simplicity if 1-on-1, but group chat needs pivot.
                            // IMPROVED LOGIC: Get all messages where created_at > (select last_read_at from conversation_participants ... )
                            $q->select('last_read_at')->from('conversation_participants')->whereColumn('conversation_participants.conversation_id', 'messages.conversation_id')->where('conversation_participants.user_id', $userId)->limit(1);
                        });
                },
            ])
            ->get()
            ->map(function ($c) {
                $c->is_group = true;
                $c->display_name = $c->name;
                $c->users_count = $c->participants->count();
                return $c;
            });

        // 2. Get Users (for 1-on-1) AND mixed with existing private conversations
        // Strategy: Get all users. If a private conversation exists, attach it.
        $users = User::where('id', '!=', $userId)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->get()
            ->map(function ($user) use ($userId) {
                // Find private conversation with this user
                $conversation = Conversation::where('type', 'private')
                    ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
                    ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
                    ->withCount([
                        'messages as unread_count' => function ($query) use ($userId) {
                            $query->where('user_id', '!=', $userId)->where('created_at', '>', function ($q) use ($userId) {
                                $q->select('last_read_at')->from('conversation_participants')->whereColumn('conversation_participants.conversation_id', 'messages.conversation_id')->where('conversation_participants.user_id', $userId)->limit(1);
                            });
                        },
                    ])
                    ->first();

                $user->conversation = $conversation;
                return $user;
            });

        return [
            'conversations' => $conversations,
            'users' => $users,
        ];
    }

    public function selectUser($userId)
    {
        // Find or create private conversation
        $conversation = Conversation::where('type', 'private')->whereHas('participants', fn($q) => $q->where('user_id', Auth::id()))->whereHas('participants', fn($q) => $q->where('user_id', $userId))->first();

        if (!$conversation) {
            $conversation = Conversation::create(['type' => 'private']);
            $conversation->participants()->attach([Auth::id(), $userId]);
        }

        $this->selectConversation($conversation->id);
    }

    public function selectConversation($conversationId)
    {
        $this->selectedConversationId = $conversationId;
        $this->dispatch('conversationSelected', conversationId: $conversationId);

        // Update last_read_at
        Auth::user()
            ->conversations()
            ->updateExistingPivot($conversationId, ['last_read_at' => now()]);
    }

    #[On('groupCreated')]
    #[On('messageReceived')]
    public function refreshList()
    {
        // Volts automatically re-renders on event if we use $refresh or just method call
        $this->dispatch('$refresh');
    }
}; ?>

<div class="d-flex flex-column h-100">
    <div class="p-3 border-bottom border-secondary border-opacity-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Messages</h5>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div class="search-container w-100">
            <i class="fas fa-search"></i>
            <input type="text" wire:model.live="search" class="form-control" placeholder="Search...">
        </div>
    </div>

    <div class="overflow-auto flex-grow-1 p-2">
        <!-- Direct Messages -->
        <div class="text-uppercase text-muted small fw-bold px-2 mt-2 mb-1">Direct Messages</div>
        @foreach ($users as $user)
            <div class="d-flex align-items-center p-2 rounded user-item {{ $user->conversation && $selectedConversationId == $user->conversation->id ? 'bg-primary bg-opacity-10' : '' }}"
                wire:click="selectUser({{ $user->id }})" style="cursor: pointer;">
                <div class="position-relative">
                    @if ($user->profile_image)
                        <img src="{{ asset('storage/' . $user->profile_image) }}" class="rounded-circle" width="40"
                            height="40" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white"
                            style="width: 40px; height: 40px;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="ms-3 flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-0 text-truncate">{{ $user->name }}</h6>
                        @if ($user->conversation && $user->conversation->unread_count > 0)
                            <span class="badge bg-danger rounded-pill">{{ $user->conversation->unread_count }}</span>
                        @endif
                    </div>
                    <small class="text-muted text-truncate d-block">
                        {{ $user->role->name ?? 'Member' }}
                    </small>
                </div>
            </div>
        @endforeach

        <!-- Groups -->
        @if ($conversations->isNotEmpty())
            <hr class="border-secondary border-opacity-10 my-2">
            <div class="text-uppercase text-muted small fw-bold px-2 mt-2 mb-1">Groups</div>
            @foreach ($conversations as $group)
                <div class="d-flex align-items-center p-2 rounded user-item {{ $selectedConversationId == $group->id ? 'bg-primary bg-opacity-10' : '' }}"
                    wire:click="selectConversation({{ $group->id }})" style="cursor: pointer;">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                        style="width: 40px; height: 40px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0 text-truncate">{{ $group->name }}</h6>
                            @if ($group->unread_count > 0)
                                <span class="badge bg-danger rounded-pill">{{ $group->unread_count }}</span>
                            @endif
                        </div>
                        <small class="text-muted text-truncate d-block">
                            {{ $group->users_count }} members
                        </small>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Include Modal Component -->
    <livewire:create-group-modal />

    <style>
        .user-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        [data-theme="light"] .user-item:hover {
            background: rgba(0, 0, 0, 0.05);
        }
    </style>
</div>
