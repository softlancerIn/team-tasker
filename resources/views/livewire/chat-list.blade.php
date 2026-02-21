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
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['participants', 'latestMessage'])
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query
                        ->where('user_id', '!=', $userId) // Don't count own messages
                        ->where('created_at', '>', function ($q) use ($userId) {
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
            })
            ->sortByDesc(function ($c) {
                return $c->latestMessage?->created_at?->timestamp ?? ($c->created_at?->timestamp ?? 0);
            })
            ->values();

        // 2. Get Client Groups
        $clientGroups = Conversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('type', 'client_group')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['participants', 'latestMessage'])
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query->where('user_id', '!=', $userId)->where('created_at', '>', function ($q) use ($userId) {
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
            })
            ->sortByDesc(function ($c) {
                return $c->latestMessage?->created_at?->timestamp ?? ($c->created_at?->timestamp ?? 0);
            })
            ->values();

        // 3. Get Users (Direct Messages)
        // If Client (role_id 3), only show Staff. If Admin/Staff, show everyone (or just Clients/Staff).
        // Let's assume Admin sees everyone. Client sees Staff.

        $isClient = Auth::user()->role_id == 3;

        $usersQuery = User::where('id', '!=', $userId);

        if ($isClient) {
            // Clients see Staff only
            $usersQuery->where('role_id', '!=', 3);
        }

        $users = $usersQuery
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->get()
            ->map(function ($user) use ($userId) {
                // Find private conversation with this user
                $conversation = Conversation::where('type', 'private')
                    ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
                    ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
                    ->with(['latestMessage'])
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
            })
            ->sortByDesc(function ($user) {
                return $user->conversation?->latestMessage?->created_at?->timestamp ?? 0;
            })
            ->values();

        return [
            'conversations' => $conversations,
            'clientGroups' => $clientGroups,
            'users' => $users,
            'isClient' => $isClient,
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
    } // Listen for the event dispatched by layout

    #[On('groupCreated')]
    #[On('messageReceived')]
    #[On('global-message-received')]
    public function refreshList()
    {
        // Volts automatically re-renders on event if we use $refresh or just method call
        $this->dispatch('$refresh');
    }
}; ?>

<div class="d-flex flex-column h-100">
    <div class="p-3 border-bottom" style="border-color: var(--border-color) !important;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold" style="color: var(--text-main);">{{ $isClient ? 'My Groups' : 'Customers' }}</h5>
            @if (!$isClient)
                <button class="btn btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#createGroupModal"
                    style="background: var(--input-bg); color: var(--text-muted);">
                    <i class="fas fa-plus"></i>
                </button>
            @endif
        </div>
        <div class="search-container w-100 position-relative">
            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3"
                style="color: var(--text-muted);"></i>
            <input type="text" wire:model.live="search" class="form-control rounded-pill ps-5 border-0"
                placeholder="{{ $isClient ? 'Search Groups...' : 'Search Customers...' }}"
                style="background: var(--input-bg); color: var(--text-main);">
        </div>
    </div>

    <div class="overflow-auto flex-grow-1">
        <!-- Client Groups -->
        <div class="d-flex justify-content-between align-items-center px-3 py-2 mt-2">
            <div class="text-uppercase small fw-bold" style="color: var(--text-muted);">Client Groups</div>
            @if (!$isClient)
                <button class="btn btn-sm rounded-circle" data-bs-toggle="modal"
                    data-bs-target="#createClientGroupModal"
                    style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted);"
                    title="Create Client Group">
                    <i class="fas fa-plus"></i>
                </button>
            @endif
        </div>

        @if ($clientGroups->isNotEmpty())
            @foreach ($clientGroups as $group)
                <div class="d-flex align-items-center p-2 rounded user-item mx-2"
                    wire:click="selectConversation({{ $group->id }})"
                    style="cursor: pointer; {{ $selectedConversationId == $group->id ? 'background: rgba(var(--primary-rgb), 0.1);' : '' }}">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white"
                        style="width: 40px; height: 40px; background: linear-gradient(45deg, #FF9F43, #FF6B6B);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0 text-truncate" style="color: var(--text-main);">{{ $group->name }}</h6>
                            @if ($group->unread_count > 0)
                                <span class="badge bg-danger rounded-pill">{{ $group->unread_count }}</span>
                            @endif
                        </div>
                        <small class="text-truncate d-block" style="color: var(--text-muted);">
                            {{ $group->users_count }} members
                        </small>
                    </div>
                </div>
            @endforeach
        @else
            <div class="px-3 py-2 text-muted small fst-italic">No client groups yet.</div>
        @endif

        @if (!$isClient)
            <hr class="my-2" style="border-color: var(--border-color); opacity: 0.1;">
            <div class="text-uppercase small fw-bold px-3 py-2" style="color: var(--text-muted);">
                Direct Messages</div>

            <!-- Direct Messages -->
            @foreach ($users as $user)
                <div class="d-flex align-items-center p-3 border-bottom user-item"
                    wire:click="selectUser({{ $user->id }})"
                    style="cursor: pointer; border-color: var(--border-color) !important; {{ $user->conversation && $selectedConversationId == $user->conversation->id ? 'background: rgba(var(--primary-rgb), 0.1);' : '' }}"
                    data-user-id="{{ $user->id }}">
                    <div class="position-relative">
                        @if ($user->profile_image)
                            <img src="{{ asset('storage/' . $user->profile_image) }}" class="rounded-circle"
                                width="45" height="45" style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px; font-size: 1.2rem;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                        <!-- Online Status Dot -->
                        <span class="status-dot position-absolute top-0 end-0 p-1 bg-secondary rounded-circle"
                            style="border: 2px solid var(--sidebar-bg);"></span>
                    </div>
                    <div class="ms-3 flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold" style="color: var(--text-main);">{{ $user->name }}</h6>
                            @if ($user->conversation && $user->conversation->unread_count > 0)
                                <span
                                    class="badge bg-primary rounded-pill">{{ $user->conversation->unread_count }}</span>
                            @endif
                        </div>
                        <small class="text-truncate d-block" style="color: var(--text-muted);">
                            {{ $user->email }}
                        </small>
                    </div>
                </div>
            @endforeach

            <!-- Groups -->
            @if ($conversations->isNotEmpty())
                <hr class="my-2" style="border-color: var(--border-color); opacity: 0.1;">
                <div class="text-uppercase small fw-bold px-2 mt-2 mb-1" style="color: var(--text-muted);">Groups</div>
                @foreach ($conversations as $group)
                    <div class="d-flex align-items-center p-2 rounded user-item"
                        wire:click="selectConversation({{ $group->id }})"
                        style="cursor: pointer; {{ $selectedConversationId == $group->id ? 'background: rgba(var(--primary-rgb), 0.1);' : '' }}">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                            style="width: 40px; height: 40px;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ms-3 flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0 text-truncate" style="color: var(--text-main);">{{ $group->name }}
                                </h6>
                                @if ($group->unread_count > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $group->unread_count }}</span>
                                @endif
                            </div>
                            <small class="text-truncate d-block" style="color: var(--text-muted);">
                                {{ $group->users_count }} members
                            </small>
                        </div>
                    </div>
                @endforeach
            @endif
        @endif
    </div>

    <!-- Include Modal Component -->
    <livewire:create-group-modal />
    <livewire:create-client-group-modal />

    <style>
        .user-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        [data-theme="light"] .user-item:hover {
            background: rgba(0, 0, 0, 0.05);
        }
    </style>
</div>
