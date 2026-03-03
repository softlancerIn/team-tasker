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

<div class="d-flex flex-column h-100" style="background: var(--bg-surface);">
    <div class="p-3 border-bottom border-main">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold" style="color: var(--text-high);">{{ $isClient ? 'My Groups' : 'Conversations' }}</h5>
            @if (!$isClient)
                <button class="btn-premium btn-premium-secondary p-0 rounded-circle" data-bs-toggle="modal"
                    data-bs-target="#createGroupModal"
                    style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: var(--bg-input);">
                    <i class="fas fa-plus" style="font-size: 0.8rem;"></i>
                </button>
            @endif
        </div>
        <div class="search-container-premium w-100">
            <i class="fas fa-search search-icon-premium" style="font-size: 0.9rem;"></i>
            <input type="text" wire:model.live="search" class="form-premium-control ps-5 py-2"
                placeholder="{{ $isClient ? 'Search groups...' : 'Search contacts...' }}" style="font-size: 0.85rem;">
        </div>
    </div>

    <div class="overflow-auto flex-grow-1">
        <!-- Client Groups -->
        <div class="d-flex justify-content-between align-items-center px-4 py-3">
            <div class="heading-label mb-0" style="font-size: 0.7rem;">Client Groups</div>
            @if (!$isClient)
                <button class="btn btn-sm px-1 py-0 border-0 opacity-50" data-bs-toggle="modal"
                    data-bs-target="#createClientGroupModal" style="color: var(--text-high);"
                    title="Create Client Group">
                    <i class="fas fa-plus-circle"></i>
                </button>
            @endif
        </div>

        @if ($clientGroups->isNotEmpty())
            @foreach ($clientGroups as $group)
                <div class="d-flex align-items-center px-4 py-3 user-item-premium {{ $selectedConversationId == $group->id ? 'active' : '' }}"
                    wire:click="selectConversation({{ $group->id }})">
                    <div class="avatar-premium"
                        style="width: 42px; height: 42px; background: linear-gradient(135deg, var(--primary), var(--accent));">
                        <i class="fas fa-user-tie text-white" style="font-size: 1rem;"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 text-truncate fw-bold" style="color: var(--text-high); font-size: 0.9rem;">
                                {{ $group->name }}</h6>
                            @if ($group->unread_count > 0)
                                <span class="badge-premium py-0 px-2 rounded-pill"
                                    style="background: var(--accent); color: white; font-size: 0.65rem;">{{ $group->unread_count }}</span>
                            @endif
                        </div>
                        <div class="text-truncate d-block" style="color: var(--text-low); font-size: 0.75rem;">
                            {{ $group->users_count }} members
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="px-4 py-2 text-low small fst-italic" style="font-size: 0.8rem;">No client groups.</div>
        @endif

        @if (!$isClient)
            <div class="heading-label px-4 py-3 mb-0"
                style="font-size: 0.7rem; border-top: 1px solid var(--border-subtle);">Direct Messages</div>

            <!-- Direct Messages -->
            @foreach ($users as $user)
                <div class="d-flex align-items-center px-4 py-3 user-item-premium {{ $user->conversation && $selectedConversationId == $user->conversation->id ? 'active' : '' }}"
                    wire:click="selectUser({{ $user->id }})" data-user-id="{{ $user->id }}">
                    <div class="position-relative">
                        <div class="avatar-premium" style="width: 42px; height: 42px;">
                            @if ($user->profile_image)
                                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Avatar">
                            @else
                                {{ substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <!-- Online Status Dot -->
                        <span
                            class="position-absolute bottom-0 end-0 p-1 bg-success rounded-circle border border-1 border-dark"
                            style="width: 12px; height: 12px; transform: translate(10%, 10%);"></span>
                    </div>
                    <div class="ms-3 flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-truncate" style="color: var(--text-high); font-size: 0.9rem;">
                                {{ $user->name }}</h6>
                            @if ($user->conversation && $user->conversation->unread_count > 0)
                                <span class="badge-premium py-0 px-2 rounded-pill"
                                    style="background: var(--primary); color: white; font-size: 0.65rem;">{{ $user->conversation->unread_count }}</span>
                            @endif
                        </div>
                        <div class="text-truncate d-block" style="color: var(--text-low); font-size: 0.75rem;">
                            {{ $user->email }}
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Groups -->
            @if ($conversations->isNotEmpty())
                <div class="heading-label px-4 py-3 mb-0"
                    style="font-size: 0.7rem; border-top: 1px solid var(--border-subtle);">Staff Groups</div>
                @foreach ($conversations as $group)
                    <div class="d-flex align-items-center px-4 py-3 user-item-premium {{ $selectedConversationId == $group->id ? 'active' : '' }}"
                        wire:click="selectConversation({{ $group->id }})">
                        <div class="avatar-premium" style="width: 42px; height: 42px; background: var(--bg-input);">
                            <i class="fas fa-users" style="color: var(--primary);"></i>
                        </div>
                        <div class="ms-3 flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-truncate fw-bold"
                                    style="color: var(--text-high); font-size: 0.9rem;">{{ $group->name }}
                                </h6>
                                @if ($group->unread_count > 0)
                                    <span class="badge-premium py-0 px-2 rounded-pill"
                                        style="background: var(--accent); color: white; font-size: 0.65rem;">{{ $group->unread_count }}</span>
                                @endif
                            </div>
                            <div class="text-truncate d-block" style="color: var(--text-low); font-size: 0.75rem;">
                                {{ $group->users_count }} members
                            </div>
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
        .user-item-premium {
            cursor: pointer;
            transition: var(--transition-base);
            border-left: 3px solid transparent;
        }

        .user-item-premium:hover {
            background: var(--bg-input);
        }

        .user-item-premium.active {
            background: rgba(var(--primary-rgb), 0.08);
            border-left-color: var(--primary);
        }

        .user-item-premium.active .fw-bold {
            color: var(--primary) !important;
        }
    </style>
</div>
