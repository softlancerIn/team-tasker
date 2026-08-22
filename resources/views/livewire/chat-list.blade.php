<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component {
    public $search = '';
    public $selectedConversationId = null;
    public $userLimit = 20;

    public function loadMoreUsers()
    {
        $this->userLimit += 20;
    }

    public function with()
    {
        $isClientGuard = Auth::guard('client')->check();
        $user = $isClientGuard ? Auth::guard('client')->user() : Auth::user();
        $userId = $user->id;

        $isClient = $isClientGuard;
        $isSuperAdmin = !$isClientGuard && $user->role_id == 1;

        // Get Unread Counts efficiently
        $unreadCounts = \Illuminate\Support\Facades\DB::table('messages')
            ->join('conversation_participants', function ($join) use ($userId, $isClientGuard) {
                $join->on('messages.conversation_id', '=', 'conversation_participants.conversation_id');
                if ($isClientGuard) {
                    $join->where('conversation_participants.client_id', '=', $userId);
                } else {
                    $join->where('conversation_participants.user_id', '=', $userId);
                }
            })
            ->where(function ($query) use ($userId, $isClientGuard) {
                if ($isClientGuard) {
                    $query->where('messages.client_id', '!=', $userId)->orWhereNull('messages.client_id');
                } else {
                    $query->where('messages.user_id', '!=', $userId)->orWhereNull('messages.user_id');
                }
            })
            ->where(function ($query) {
                $query->whereNull('conversation_participants.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'conversation_participants.last_read_at');
            })
            ->select('messages.conversation_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('messages.conversation_id')
            ->pluck('count', 'conversation_id');

        // 1. Get Group Conversations for the user
        $conversations = Conversation::whereHas($isClientGuard ? 'clientParticipants' : 'participants', function ($q) use ($userId, $isClientGuard) {
            if ($isClientGuard) {
                $q->where('conversation_participants.client_id', $userId);
            } else {
                $q->where('conversation_participants.user_id', $userId);
            }
        })
            ->where('type', 'group')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['participants', 'clientParticipants', 'latestMessage'])
            ->get()
            ->map(function ($c) use ($unreadCounts) {
                $c->is_group = true;
                $c->display_name = $c->name;
                $c->users_count = $c->participants->count() + $c->clientParticipants->count();
                $c->unread_count = $unreadCounts[$c->id] ?? 0;
                return $c;
            })
            ->sortByDesc(function ($c) {
                return $c->latestMessage?->created_at?->timestamp ?? ($c->created_at?->timestamp ?? 0);
            })
            ->values();

        // 2. Get Client Groups
        $clientGroups = Conversation::whereHas($isClientGuard ? 'clientParticipants' : 'participants', function ($q) use ($userId, $isClientGuard) {
            if ($isClientGuard) {
                $q->where('conversation_participants.client_id', $userId);
            } else {
                $q->where('conversation_participants.user_id', $userId);
            }
        })
            ->where('type', 'client_group')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['participants', 'clientParticipants', 'latestMessage'])
            ->get()
            ->map(function ($c) use ($unreadCounts) {
                $c->is_group = true;
                $c->display_name = $c->name;
                $c->users_count = $c->participants->count() + $c->clientParticipants->count();
                $c->unread_count = $unreadCounts[$c->id] ?? 0;
                return $c;
            })
            ->sortByDesc(function ($c) {
                return $c->latestMessage?->created_at?->timestamp ?? ($c->created_at?->timestamp ?? 0);
            })
            ->values();

        // 3. Get Users (Direct Messages)
        // If Client, they can only see Users (Staff) that they are allowed to see
        // If Staff, they can see Users (Staff) and Clients

        $users = collect();

        // Pre-load all private conversations for the current user FIRST
        $myPrivateConversations = Conversation::where('type', 'private')
            ->whereHas($isClientGuard ? 'clientParticipants' : 'participants', function ($q) use ($userId, $isClientGuard) {
                if ($isClientGuard) {
                    $q->where('conversation_participants.client_id', $userId);
                } else {
                    $q->where('conversation_participants.user_id', $userId);
                }
            })
            ->with(['latestMessage', 'participants', 'clientParticipants'])
            ->get();

        // Collect existing contact IDs we already have an active conversation with
        $existingStaffIds = [];
        $existingClientIds = [];
        foreach ($myPrivateConversations as $c) {
            foreach ($c->participants as $p) {
                if ($p->pivot->user_id)
                    $existingStaffIds[] = $p->pivot->user_id;
            }
            foreach ($c->clientParticipants as $p) {
                if ($p->pivot->client_id)
                    $existingClientIds[] = $p->pivot->client_id;
            }
        }

        if ($isClient) {
            // Client looking at Staff
            $usersQuery = User::query();
            // Apply Chat Permissions
            $allowedIds = \Illuminate\Support\Facades\DB::table('chat_user_permissions')
                ->where('client_id', $userId) // Explicit permission to Client
                ->pluck('allowed_user_id')
                ->toArray();

            $staffWhoAllowedMe = \Illuminate\Support\Facades\DB::table('chat_user_permissions')
                ->where('allowed_client_id', $userId) // Staff who explicitly granted permission to Client
                ->pluck('user_id')
                ->toArray();

            $allowedIds = array_unique(array_merge($allowedIds, $staffWhoAllowedMe, $existingStaffIds));

            $usersQuery->where(function ($q) use ($allowedIds) {
                // Always show Super Admins
                $q->where('role_id', 1);
                if (!empty($allowedIds)) {
                    $q->orWhereIn('id', $allowedIds);
                }
            });

            $users = $usersQuery
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->limit($this->userLimit)
                ->get();

            $clients = collect(); // empty for clients looking at staff
        } else {
            // Staff looking at Staff
            $usersQuery = User::where('id', '!=', $userId);

            $allowedIds = \Illuminate\Support\Facades\DB::table('chat_user_permissions')
                ->where('user_id', $userId)
                ->pluck('allowed_user_id')
                ->toArray();

            $allowedIds = array_unique(array_merge($allowedIds, $existingStaffIds));

            if (!$isSuperAdmin) {
                $usersQuery->where(function ($q) use ($allowedIds) {
                    $q->where('role_id', 1);
                    if (!empty($allowedIds)) {
                        $q->orWhereIn('id', $allowedIds);
                    }
                });
            }

            $users = $usersQuery
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->limit($this->userLimit)
                ->get();

            // Staff looking at Clients
            $clientsQuery = \App\Models\Client::query();

            $allowedClientIds = \Illuminate\Support\Facades\DB::table('chat_user_permissions')
                ->where('user_id', $userId)
                ->whereNotNull('allowed_client_id')
                ->pluck('allowed_client_id')
                ->toArray();

            $allowedClientIds = array_unique(array_merge($allowedClientIds, $existingClientIds));

            if (!$isSuperAdmin) {
                $clientsQuery->where(function ($q) use ($allowedClientIds) {
                    // Clients don't have role_id = 1, so if no permissions, they see NO clients, 
                    // unless we want to show all clients by default?
                    // The request says "after give the permission to user he chat direct message"
                    // meaning they should only see clients they have permission for.
                    $q->whereIn('id', $allowedClientIds);
                });
            }

            $clients = $clientsQuery
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->limit($this->userLimit)
                ->get();
            // Stop merging clients into users, return them separately
        }

        $users = $users->map(function ($targetUser) use ($myPrivateConversations, $unreadCounts, $isClientGuard, $userId) {
            // Determine if target is client
            $targetIsClient = false;

            $conversation = $myPrivateConversations->first(function ($c) use ($targetUser, $targetIsClient) {
                return $c->participants->contains(function ($p) use ($targetUser, $targetIsClient) {
                    return !$targetIsClient && $p->pivot->user_id == $targetUser->id;
                });
            });

            if ($conversation) {
                $conversation->unread_count = $unreadCounts[$conversation->id] ?? 0;
            }

            $targetUser->conversation = $conversation;
            $targetUser->is_client = $targetIsClient;
            return $targetUser;
        })
            ->sortByDesc(function ($user) {
                return $user->conversation?->latestMessage?->created_at?->timestamp ?? 0;
            })
            ->values();

        $clients = $clients->map(function ($targetUser) use ($myPrivateConversations, $unreadCounts, $isClientGuard, $userId) {
            // Determine if target is client
            $targetIsClient = true;

            $conversation = $myPrivateConversations->first(function ($c) use ($targetUser, $targetIsClient) {
                return $c->clientParticipants->contains(function ($p) use ($targetUser, $targetIsClient) {
                    return $targetIsClient && $p->pivot->client_id == $targetUser->id;
                });
            });

            if ($conversation) {
                $conversation->unread_count = $unreadCounts[$conversation->id] ?? 0;
            }

            $targetUser->conversation = $conversation;
            $targetUser->is_client = $targetIsClient;
            return $targetUser;
        })
            ->sortByDesc(function ($user) {
                return $user->conversation?->latestMessage?->created_at?->timestamp ?? 0;
            })
            ->values();

        return [
            'conversations' => $conversations,
            'clientGroups' => $clientGroups,
            'users' => $users,
            'clients' => $clients,
            'isClient' => $isClient,
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }

    public function selectUser($userId, $isClientTarget = false)
    {
        \Illuminate\Support\Facades\Log::info('selectUser called', ['userId' => $userId, 'isClientTarget' => $isClientTarget]);

        $isClientGuard = Auth::guard('client')->check();
        $myId = $isClientGuard ? Auth::guard('client')->id() : Auth::id();

        // Find or create private conversation
        $conversation = Conversation::where('type', 'private')
            ->whereHas($isClientGuard ? 'clientParticipants' : 'participants', function ($q) use ($myId, $isClientGuard) {
                if ($isClientGuard) {
                    $q->where('conversation_participants.client_id', $myId);
                } else {
                    $q->where('conversation_participants.user_id', $myId);
                }
            })
            ->whereHas($isClientTarget ? 'clientParticipants' : 'participants', function ($q) use ($userId, $isClientTarget) {
                if ($isClientTarget) {
                    $q->where('conversation_participants.client_id', $userId);
                } else {
                    $q->where('conversation_participants.user_id', $userId);
                }
            })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create(['type' => 'private']);
            // Attach me
            if ($isClientGuard) {
                $conversation->participants()->attach([$myId], ['client_id' => $myId, 'user_id' => null]);
            } else {
                $conversation->participants()->attach([$myId], ['user_id' => $myId, 'client_id' => null]);
            }
            // Attach target
            if ($isClientTarget) {
                $conversation->participants()->attach([$userId], ['client_id' => $userId, 'user_id' => null]);
            } else {
                $conversation->participants()->attach([$userId], ['user_id' => $userId, 'client_id' => null]);
            }
        }

        $this->selectConversation($conversation->id);
    }

    public function selectConversation($conversationId)
    {
        \Illuminate\Support\Facades\Log::info('selectConversation called', ['id' => $conversationId]);
        $this->selectedConversationId = $conversationId;
        $this->dispatch('conversationSelected', $conversationId);

        // Update last_read_at
        $isClientGuard = Auth::guard('client')->check();
        $user = $isClientGuard ? Auth::guard('client')->user() : Auth::user();

        $user->conversations()
            ->updateExistingPivot($conversationId, ['last_read_at' => now()]);
    } // Listen for the event dispatched by layout

    #[On('groupCreated')]
    #[On('messageReceived')]
    #[On('global-message-received')]
    #[On('socket-messages-read')]
    public function refreshList()
    {
        // Calling any Volt method triggers a re-render of the component automatically
        // No need to manually dispatch $refresh
    }
}; ?>

<div class="d-flex flex-column h-100" style="background: var(--bg-surface);" x-data="{
    onlineUsers: window.onlineUsers ? window.onlineUsers.map(String) : [],
    myStatus: localStorage.getItem('user_presence_status_' + {{ auth()->id() ?? 0 }}) || 'online',
    userStatuses: window.userStatuses || {},
    init() {
        if (window.userStatuses) {
            this.userStatuses = window.userStatuses;
        }
        window.addEventListener('online-users-updated', (e) => {
            this.onlineUsers = e.detail.map(String);
        });
        window.addEventListener('all-user-statuses-updated', (e) => {
            this.userStatuses = e.detail || {};
            if (this.userStatuses[{{ auth()->id() ?? 0 }}]) {
                this.myStatus = this.userStatuses[{{ auth()->id() ?? 0 }}];
            }
        });
        window.addEventListener('user-status-changed-event', (e) => {
            if (e.detail && e.detail.userId) {
                this.userStatuses[e.detail.userId] = e.detail.status;
                if (String(e.detail.userId) === String({{ auth()->id() ?? 0 }})) {
                    this.myStatus = e.detail.status;
                }
            }
        });
    },
    changeStatus(status) {
        this.myStatus = status;
        localStorage.setItem('user_presence_status_' + {{ auth()->id() ?? 0 }}, status);
        if (window.socket) {
            window.socket.emit('update_status', { userId: {{ auth()->id() ?? 0 }}, status: status });
        }
    },
    getStatusBadge(userId) {
        const uId = Number(userId);
        const st = (this.userStatuses || {})[uId] || (this.userStatuses || {})[String(userId)];
        if (st === 'away') return { color: '#f59e0b', label: 'Away', dot: '🟡' };
        if (st === 'busy') return { color: '#ea4335', label: 'Busy', dot: '🔴' };
        if (st === 'offline') return { color: '#6b7280', label: 'Offline', dot: '⚫' };
        if (this.onlineUsers.includes(String(userId)) || this.onlineUsers.includes(uId) || st === 'online') {
            return { color: '#00a884', label: 'Online', dot: '🟢' };
        }
        return { color: '#6b7280', label: 'Offline', dot: '⚫' };
    }
}">
    <div class="p-3 border-bottom border-main">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold" style="color: var(--text-high);">{{ $isClient ? 'My Groups' : 'Conversations' }}</h5>
                <!-- Status Picker Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2 rounded-pill d-flex align-items-center gap-1"
                        type="button" data-bs-toggle="dropdown" style="font-size: 0.75rem; border-color: var(--border-main); color: var(--text-high);">
                        <span x-text="myStatus === 'online' ? '🟢' : (myStatus === 'away' ? '🟡' : (myStatus === 'busy' ? '🔴' : '⚫'))"></span>
                        <span class="text-capitalize" x-text="myStatus"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-premium border-main" style="font-size: 0.8rem; min-width: 140px;">
                        <li><a class="dropdown-item d-flex align-items-center gap-2 py-1" href="#" @click.prevent="changeStatus('online')">🟢 Online</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2 py-1" href="#" @click.prevent="changeStatus('away')">🟡 Away</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2 py-1" href="#" @click.prevent="changeStatus('busy')">🔴 Busy</a></li>
                    </ul>
                </div>
            </div>

            @if (!$isClient && ($isSuperAdmin || auth()->user()->hasPermission('chat.create_staff_group')))
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
            @if (!$isClient && ($isSuperAdmin || auth()->user()->hasPermission('chat.create_client_group')))
                <button class="btn btn-sm px-1 py-0 border-0 opacity-50" data-bs-toggle="modal"
                    data-bs-target="#createClientGroupModal" style="color: var(--text-high);" title="Create Client Group">
                    <i class="fas fa-plus-circle"></i>
                </button>
            @endif
        </div>

        @if ($clientGroups->isNotEmpty())
            @foreach ($clientGroups as $group)
                <div class="d-flex align-items-center px-4 py-3 user-item-premium {{ $selectedConversationId == $group->id ? 'active' : '' }}"
                    wire:click="selectConversation({{ $group->id }})" wire:key="group-{{ $group->id }}">
                    <div class="avatar-premium"
                        style="width: 42px; height: 42px; background: linear-gradient(135deg, var(--primary), var(--accent));">
                        <i class="fas fa-user-tie text-white" style="font-size: 1rem;"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 text-truncate fw-bold" style="color: var(--text-high); font-size: 0.9rem;">
                                {{ $group->name }}
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
        @else
            <div class="px-4 py-2 text-low small fst-italic" style="font-size: 0.8rem;">No client groups.</div>
        @endif

        @if (!$isClient)
            @if(isset($clients) && $clients->isNotEmpty())
                <div class="heading-label px-4 py-3 mb-0"
                    style="font-size: 0.7rem; border-top: 1px solid var(--border-subtle);">Client Messages</div>

                <!-- Client Messages -->
                @foreach ($clients as $client)
                    <div class="d-flex align-items-center px-4 py-3 user-item-premium {{ $client->conversation && $selectedConversationId == $client->conversation->id ? 'active' : '' }}"
                        wire:click="selectUser({{ $client->id }}, true)" wire:key="user-client-{{ $client->id }}"
                        data-user-id="{{ $client->id }}">
                        <div class="position-relative">
                            <div class="avatar-premium"
                                style="width: 42px; height: 42px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                                @if ($client->profile_image)
                                    <img alt="team-tasker" src="{{ asset('storage/' . $client->profile_image) }}" alt="Avatar">
                                @else
                                    {{ substr($client->name, 0, 1) }}
                                @endif
                            </div>
                        </div>
                        <div class="ms-3 flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold text-truncate" style="color: var(--text-high); font-size: 0.9rem;">
                                    {{ $client->name }}
                                </h6>
                                @if ($client->conversation && $client->conversation->unread_count > 0)
                                    <span class="badge-premium py-0 px-2 rounded-pill"
                                        style="background: var(--primary); color: white; font-size: 0.65rem;">{{ $client->conversation->unread_count }}</span>
                                @endif
                            </div>
                            <div class="text-truncate d-block" style="color: var(--text-low); font-size: 0.75rem;">
                                {{ $client->company ?? 'Client' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        @endif

        <div class="heading-label px-4 py-3 mb-0"
            style="font-size: 0.7rem; border-top: 1px solid var(--border-subtle);">Direct Messages</div>

        <!-- Direct Messages -->
        @foreach ($users as $user)
            <div class="d-flex align-items-center px-4 py-3 user-item-premium {{ $user->conversation && $selectedConversationId == $user->conversation->id ? 'active' : '' }}"
                wire:click="selectUser({{ $user->id }}, {{ isset($user->is_client) && $user->is_client ? 'true' : 'false' }})"
                wire:key="user-{{ isset($user->is_client) && $user->is_client ? 'client' : 'user' }}-{{ $user->id }}"
                data-user-id="{{ $user->id }}">
                <div class="position-relative">
                    <div class="avatar-premium" style="width: 42px; height: 42px;">
                        @if ($user->profile_image)
                            <img alt="team-tasker" src="{{ asset('storage/' . $user->profile_image) }}" alt="Avatar">
                        @else
                            {{ substr($user->name, 0, 1) }}
                        @endif
                    </div>
                    <!-- Online Status Dot -->
                    <span class="position-absolute bottom-0 end-0 p-1 rounded-circle border border-1 border-dark"
                        :style="'width: 12px; height: 12px; background-color: ' + getStatusBadge('{{ $user->id }}').color"></span>
                </div>
                <div class="ms-3 flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="mb-0 fw-bold text-truncate" style="color: var(--text-high); font-size: 0.9rem;">
                            {{ $user->name }}
                        </h6>
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

        @if ($users->count() >= $userLimit)
            <div class="text-center py-2">
                <button wire:click="loadMoreUsers" class="btn btn-sm btn-link text-decoration-none"
                    style="font-size: 0.75rem;">
                    Load More Users
                </button>
            </div>
        @endif

        @if (!$isClient)
            <!-- Groups -->
            @if ($conversations->isNotEmpty())
                <div class="heading-label px-4 py-3 mb-0"
                    style="font-size: 0.7rem; border-top: 1px solid var(--border-subtle);">Staff Groups</div>
                @foreach ($conversations as $group)
                    <div class="d-flex align-items-center px-4 py-3 user-item-premium {{ $selectedConversationId == $group->id ? 'active' : '' }}"
                        wire:click="selectConversation({{ $group->id }})" wire:key="staff-group-{{ $group->id }}">
                        <div class="avatar-premium" style="width: 42px; height: 42px; background: var(--bg-input);">
                            <i class="fas fa-users" style="color: var(--primary);"></i>
                        </div>
                        <div class="ms-3 flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-truncate fw-bold" style="color: var(--text-high); font-size: 0.9rem;">
                                    {{ $group->name }}
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
            color: var(--text-high) !important;
        }

        /* Custom Scrollbar for Chat List */
        .overflow-auto::-webkit-scrollbar {
            width: 4px;
        }

        .overflow-auto::-webkit-scrollbar-track {
            background: transparent;
        }

        .overflow-auto::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.3);
            border-radius: 10px;
        }

        .overflow-auto::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.5);
        }
    </style>
</div>