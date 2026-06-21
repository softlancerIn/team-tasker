<?php

use Livewire\Volt\Component;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\MessageNotification;
use App\Events\MessageDelivered;
use App\Events\MessageRead;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $selectedConversationId;
    public $conversation;
    public $body = '';
    public $attachment;
    public $messages = [];
    public $searchQuery = '';
    public $showSearch = false;
    public $isBlocked = false;
    public $isBlockedBy = false;
    public $receiver; // Added for the other participant in a private chat
    public $showGroupInfo = false;
    public $perPage = 10; // Added for pagination

    public function mount($selectedConversationId = null)
    {
        if ($selectedConversationId) {
            $this->loadConversation($selectedConversationId);
        }
    }

    public function updatedSelectedConversationId($value)
    {
        if ($value) {
            $this->loadConversation($value);
        }
    }

    #[On('conversationSelected')]
    public function handleConversationSelected($conversationId)
    {
        $this->selectedConversationId = $conversationId;
        $this->loadConversation($conversationId);
    }

    public function loadConversation($conversationId)
    {
        $this->showGroupInfo = false; // Reset on conversation change
        $this->conversation = Conversation::with(['participants', 'messages.user', 'messages.attachments'])->find($conversationId);

        if (!$this->conversation) {
            return;
        }

        // Determine Receiver (for Private Chat)
        if ($this->conversation->type == 'private') {
            $this->receiver = $this->conversation->participants->where('id', '!=', Auth::id())->first();
        } else {
            $this->receiver = null;
        }

        // Reset pagination and load initial messages
        $this->perPage = 10;
        $this->messages = $this->conversation
            ->messages() // Relationship already loaded but we need query for pagination if we didn't eager load all
            ->with(['user', 'attachments'])
            ->latest()
            ->take($this->perPage)
            ->get()
            ->reverse()
            ->values()
            ->toArray();

        $this->dispatch('scroll-bottom');

        // Join the Socket.IO room for this conversation
        $this->dispatch('join-chat-room', $this->conversation->id);

        // Mark as read
        $this->markAsRead();
        Auth::user()
            ->conversations()
            ->updateExistingPivot($this->conversation->id, ['last_read_at' => now()]);

        // Check Block Status
        $this->checkBlockStatus();

        $this->showSearch = false;
        $this->searchQuery = '';
        $this->attachment = null;
    }

    public function checkBlockStatus()
    {
        if ($this->receiver) {
            $this->isBlocked = auth()->user()->blocking()->where('blocked_id', $this->receiver->id)->exists();
            $this->isBlockedBy = auth()->user()->blockedBy()->where('blocker_id', $this->receiver->id)->exists();
        }
    }

    public function blockUser()
    {
        if ($this->receiver) {
            auth()->user()->blocking()->attach($this->receiver->id);
            $this->isBlocked = true;
            $this->dispatch('alert', ['type' => 'success', 'message' => 'User blocked successfully.']);
        }
    }

    public function unblockUser()
    {
        if ($this->receiver) {
            auth()->user()->blocking()->detach($this->receiver->id);
            $this->isBlocked = false;
            $this->dispatch('alert', ['type' => 'success', 'message' => 'User unblocked successfully.']);
        }
    }

    public function clearChatHistory()
    {
        if ($this->conversation) {
            // Hard delete all messages in this conversation
            $this->conversation->messages()->delete();
            $this->messages = []; // Clear local messages array
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Chat history cleared.']);
        }
    }

    // Placeholder for markAsRead if it's a new method, otherwise the logic is already in loadConversation
    protected function markAsRead()
    {
        if ($this->conversation) {
            // Update pivot for last read
            Auth::user()
                ->conversations()
                ->updateExistingPivot($this->conversation->id, ['last_read_at' => now()]);

            // Update individual messages from others
            $unreadCount = $this->conversation
                ->messages()
                ->where('user_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            if ($unreadCount > 0) {
                // Determine the correct way to inform the sender that we read their messages
                // Instead of broadcast(new MessageRead...), we use Socket.IO
                $this->dispatch('messages-read-to-node', [
                    'room' => $this->conversation->id,
                    'userId' => Auth::id(),
                ]);
            }
        }
    }

    public function loadMessages()
    {
        // This method is now largely superseded by the new loadConversation logic
        // but keeping it for potential other uses or if it's called elsewhere.
        if ($this->conversation) {
            $this->messages = $this->conversation->messages()->with('user')->latest()->take(50)->get()->sortBy('created_at')->values()->toArray();

            // Mark as read
            Auth::user()
                ->conversations()
                ->updateExistingPivot($this->conversation->id, ['last_read_at' => now()]);
        }
    }

    public function sendMessage($sentBody = null)
    {
        if ($this->isBlocked || $this->isBlockedBy) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'You cannot send messages to this user.']);
            return;
        }

        $messageBody = $sentBody ?? $this->body;

        if ((empty(trim($messageBody)) && !$this->attachment) || !$this->conversation) {
            return;
        }

        $message = $this->conversation->messages()->create([
            'user_id' => auth()->id(),
            'body' => $messageBody,
        ]);

        // Notify Participants
        \Illuminate\Support\Facades\Log::info('ChatBox: Dispatching notifications for message ' . $message->id . ' to ' . ($this->conversation->participants->count() - 1) . ' participants.');
        foreach ($this->conversation->participants as $participant) {
            if ($participant->id != auth()->id()) {
                \Illuminate\Support\Facades\Log::info('ChatBox: Notifying user ' . $participant->id . ' (' . $participant->email . ')');
                $participant->notify(new \App\Notifications\ChatMessageNotification($message));
            }
        }

        if ($this->attachment) {
            // Handle single file upload
            $file = is_array($this->attachment) ? $this->attachment[0] : $this->attachment;

            $path = $file->store('chat-attachments', 'public');
            $message->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
            $this->attachment = null;
        }

        // Broadcast via Socket.IO (Custom Node Server)
        $this->dispatch('send-message-to-node', [
            'room' => $this->conversation->id,
            'message' => $message->load('user', 'attachments')->toArray(),
        ]);

        $this->messages[] = $message->load('user', 'attachments')->toArray();
        $this->body = '';
        $this->dispatch('scroll-bottom');
    }

    #[On('socket-message-received')]
    public function onSocketMessageReceived($data)
    {
        $message = $data['message'];

        if ($this->conversation && $message['conversation_id'] == $this->conversation->id) {
            if (!collect($this->messages)->contains('id', $message['id'])) {
                $this->messages[] = $message;
                $this->dispatch('scroll-bottom');
                $this->markAsRead();
            }
        }
    }

    #[On('socket-message-delivered')]
    public function onSocketMessageDelivered($data)
    {
        $payload = isset($data['data']) && is_array($data['data']) ? $data['data'] : $data;
        $messageId = $payload['messageId'] ?? null;

        if ($messageId && $this->conversation) {
            foreach ($this->messages as &$msg) {
                if ($msg['id'] == $messageId && $msg['user_id'] == Auth::id()) {
                    $msg['delivered_at'] = now()->toDateTimeString();
                    break;
                }
            }
        }
    }

    #[On('socket-messages-read')]
    public function onSocketMessagesRead($data)
    {
        // The sender receives this when the receiver views the messages
        // Here we update the local messages array to show blue ticks instantly
        $payload = isset($data['data']) && is_array($data['data']) ? $data['data'] : $data;

        if ($this->conversation && $payload['room'] == $this->conversation->id) {
            foreach ($this->messages as &$msg) {
                // If it's my message and it hasn't been read yet
                if ($msg['user_id'] == Auth::id()) {
                    $msg['read_at'] = now()->toDateTimeString();
                    $msg['is_read'] = true;
                }
            }
        }
    }

    public function deleteMessage($messageId)
    {
        $message = Message::find($messageId);
        if ($message && $message->user_id == auth()->id()) {
            // Soft delete: keep the row, mark deleted_at, and clear content
            $message->update([
                'deleted_at' => now(),
                'body' => '',
            ]);

            // Update local messages array
            foreach ($this->messages as &$msg) {
                if ($msg['id'] == $messageId) {
                    $msg['deleted_at'] = now();
                    $msg['body'] = '';
                }
            }

            $this->dispatch('alert', ['type' => 'success', 'message' => 'Message deleted.']);
        }
    }

    public function receiveMessage($messageId)
    {
        $message = Message::with(['user', 'attachments'])->find($messageId);
        if ($message && $message->conversation_id == $this->conversation->id) {
            // Remove duplicate assignment here

            // Mark as read immediately since we are in the chat
            $this->messages[] = $message->toArray();

            // Mark as read immediately since we are in the chat
            $message->update(['read_at' => now(), 'delivered_at' => now()]);

            broadcast(new MessageRead($message->id, $message->user_id));

            $this->dispatch('messageReceived'); // For unread counts update in list
        } else {
            // Mark as delivered
            if (!$message->delivered_at) {
                $message->update(['delivered_at' => now()]);
                broadcast(new MessageDelivered($message->id, $message->user_id));
            }
            $this->dispatch('messageReceived'); // Update unread counts globally even if not in this chat
        }
    }

    // Listeners for functionality that doesn't rely on Echo can be defined here if needed.
    // For Socket.IO, we handle events in the frontend Alpine component.

    public function closeChat()
    {
        $this->conversation = null;
        $this->messages = [];
    }

    public function toggleSearch()
    {
        $this->showSearch = !$this->showSearch;
        if (!$this->showSearch) {
            $this->searchQuery = '';
        }
    }

    // Computed property for filtered messages
    public function getFilteredMessagesProperty()
    {
        if (empty($this->searchQuery)) {
            return $this->messages;
        }

        return collect($this->messages)
            ->filter(function ($msg) {
                return stripos($msg['body'], $this->searchQuery) !== false;
            })
            ->values()
            ->toArray();
    }
}; ?>

<div class="d-flex flex-column h-100 position-relative">
    @if ($conversation)

        <!-- Header -->
        <div class="p-3 border-bottom border-main d-flex justify-content-between align-items-center"
            wire:key="chat-header-{{ $conversation->id }}" style="min-height: 73px; background: var(--bg-surface);"
            x-data="{
                status: 'Offline',
                isTyping: false,
                typingUser: '',
                typingTimeout: null,
                userId: {{ $conversation->type == 'private' && $receiver ? $receiver->id : 'null' }},
                conversationId: '{{ $conversation->id }}',
                currentUserId: {{ auth()->id() }},
                init() {
                    if (this.userId) {
                        this.status = window.onlineUsers && window.onlineUsers.includes(Number(this.userId)) ? 'Online' : 'Offline';
            
                        window.addEventListener('online-users-updated', (e) => {
                            const users = e.detail;
                            if (users.includes(Number(this.userId))) {
                                this.status = 'Online';
                            } else {
                                this.status = 'Offline';
                            }
                        });
                    }
            
                    window.addEventListener('user-typing-indicator', (event) => {
                        const roomMatch = String(event.detail.room) === String(this.conversationId);
                        const isNotMe = String(event.detail.userId) !== String(this.currentUserId);
            
                        console.log('Typing event in header:', {
                            roomMatch,
                            isNotMe,
                            eventRoom: event.detail.room,
                            myRoom: this.conversationId,
                            eventUser: event.detail.userId,
                            me: this.currentUserId
                        });
            
                        if (roomMatch && isNotMe) {
                            this.isTyping = true;
                            this.typingUser = event.detail.userName;
            
                            if (this.typingTimeout) clearTimeout(this.typingTimeout);
                            this.typingTimeout = setTimeout(() => {
                                this.isTyping = false;
                            }, 3000);
                        }
                    });
            
                    window.addEventListener('user-stop-typing-indicator', (event) => {
                        const roomMatch = String(event.detail.room) === String(this.conversationId);
                        const isNotMe = String(event.detail.userId) !== String(this.currentUserId);
            
                        if (roomMatch && isNotMe) {
                            this.isTyping = false;
                        }
                    });
                }
            }">
            <div class="d-flex align-items-center">
                <!-- Mobile Back Button -->
                <button class="btn btn-link p-0 me-3 d-md-none text-high" wire:click="$dispatch('backToUserList')"
                    style="font-size: 1.2rem; border: none; background: none;">
                    <i class="fas fa-arrow-left"></i>
                </button>
                @if ($conversation->type == 'group' || $conversation->type == 'client_group')
                    <div class="avatar-premium me-3" style="width: 45px; height: 45px; background: var(--bg-input);">
                        <i class="fas fa-users" style="color: var(--primary);"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--text-high);">{{ $conversation->name }}</h6>
                        <small x-show="!isTyping"
                            style="color: var(--text-low); font-size: 0.75rem;">{{ $conversation->participants->count() }}
                            members</small>
                        <small x-show="isTyping" class="text-primary fw-bold"
                            style="font-size: 0.75rem; font-style: italic;" x-cloak
                            x-text="typingUser + ' is typing...'"></small>
                    </div>
                @else
                    @php
                        $otherParticipant = $conversation->participants->where('id', '!=', auth()->id())->first();
                    @endphp
                    @if ($otherParticipant)
                        <div class="d-flex align-items-center">
                            <div class="avatar-premium" style="width: 45px; height: 45px;">
                                @if ($otherParticipant->profile_image)
                                    <img src="{{ asset('storage/' . $otherParticipant->profile_image) }}"
                                        alt="Avatar">
                                @else
                                    {{ substr($otherParticipant->name ?? 'U', 0, 1) }}
                                @endif
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 fw-bold" style="color: var(--text-high);">
                                    {{ $otherParticipant->name ?? 'User' }}</h6>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="rounded-circle" x-show="!isTyping"
                                        :class="status == 'Online' ? 'bg-success' : 'bg-secondary'"
                                        style="width: 8px; height: 8px;"></span>
                                    <small x-show="!isTyping" :class="status == 'Online' ? 'text-success' : 'text-low'"
                                        style="font-size: 0.7rem;" x-text="status"></small>

                                    <small x-show="isTyping" class="text-primary fw-bold"
                                        style="font-size: 0.75rem; font-style: italic;"
                                        x-text="typingUser + ' is typing...'"></small>
                                </div>
                            </div>
                        </div>
                    @else
                        <h6 class="mb-0 fw-bold" style="color: var(--text-high);">Chat</h6>
                    @endif
                @endif
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="search-container-premium" style="width: 240px;">
                    <i class="fas fa-search search-icon-premium" style="font-size: 0.8rem;"></i>
                    <input type="text" wire:model.live="searchQuery" class="form-premium-control ps-5 py-1"
                        placeholder="Search message..." style="font-size: 0.8rem;">
                </div>
                <div class="dropdown">
                    <button class="btn-premium btn-premium-secondary p-0 rounded-circle" type="button"
                        data-bs-toggle="dropdown"
                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: var(--bg-input);">
                        <i class="fas fa-ellipsis-v" style="font-size: 0.8rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-premium border-main"
                        style="background: var(--bg-surface);">
                        <li>
                            <a class="dropdown-item text-danger d-flex align-items-center gap-2 py-2" href="#"
                                wire:click.prevent="clearChatHistory"
                                wire:confirm="Are you sure? This will delete all messages for everyone in this chat.">
                                <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i> <span>Clear Chat
                                    History</span>
                            </a>
                        </li>
                        @if ($conversation->type != 'private')
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#"
                                    wire:click.prevent="$set('showGroupInfo', true)" style="color: var(--text-high);">
                                    <i class="fas fa-info-circle" style="font-size: 0.8rem;"></i> <span>Group
                                        Info</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            @if ($isBlocked)
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#"
                                    wire:click.prevent="unblockUser" style="color: var(--text-high);">
                                    <i class="fas fa-user-check" style="font-size: 0.8rem;"></i> <span>Unblock
                                        User</span>
                                </a>
                            @else
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#"
                                    wire:click.prevent="blockUser"
                                    wire:confirm="Are you sure you want to block this user?"
                                    style="color: var(--text-high);">
                                    <i class="fas fa-user-slash" style="font-size: 0.8rem;"></i> <span>Block User</span>
                                </a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Messages Area -->
        <div class="flex-grow-1 p-3 overflow-auto" id="chat-messages"
            style="scroll-behavior: smooth; background: transparent;" wire:key="chat-messages-{{ $conversation->id }}"
            x-data="chatMessages(@this, {
                conversationId: '{{ $conversation->id }}',
                userId: {{ auth()->id() }},
                receiverId: {{ $receiver ? $receiver->id : 'null' }}
            })">

            @if ($isBlocked)
                <div class="alert alert-danger text-center mx-4 mt-3"
                    style="background: rgba(var(--accent-h), var(--accent-s), var(--accent-l), 0.1); border: 1px solid var(--accent); color: var(--accent);">
                    <i class="fas fa-ban me-2"></i> You have blocked this user.
                </div>
            @elseif($isBlockedBy)
                <div class="alert alert-warning text-center mx-4 mt-3"
                    style="background: rgba(var(--primary-rgb), 0.1); border: 1px solid var(--primary); color: var(--primary);">
                    <i class="fas fa-exclamation-circle me-2"></i> You cannot message this user.
                </div>
            @endif

            @foreach ($messages as $message)
                @php
                    // Handle both array (from toArray()) and object (from persistent collection) if mixed
                    $msgUserId = is_array($message) ? $message['user_id'] : $message->user_id;
                    $isMe = $msgUserId == auth()->id();
                    $msgBody = is_array($message) ? $message['body'] : $message->body;
                    $msgUser = is_array($message) ? $message['user'] : $message->user;
                    $msgAttachments = is_array($message) ? $message['attachments'] ?? [] : $message->attachments;
                    $createdAt = is_array($message) ? $message['created_at'] : $message->created_at;
                    $isRead = is_array($message) ? $message['is_read'] ?? false : $message->is_read;
                @endphp
                <div class="d-flex mb-4 {{ $isMe ? 'justify-content-end' : '' }}"
                    wire:key="msg-{{ $message['id'] ?? $message->id }}">
                    @if (!$isMe)
                        <div class="avatar-premium me-3 align-self-end mb-1" style="width: 32px; height: 32px;">
                            @if (isset($msgUser['profile_image']) && $msgUser['profile_image'])
                                <img src="{{ asset('storage/' . $msgUser['profile_image']) }}" alt="Avatar">
                            @else
                                {{ substr($msgUser['name'] ?? 'U', 0, 1) }}
                            @endif
                        </div>
                    @endif

                    <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }}"
                        style="max-width: 75%;">
                        @php
                            $deletedAt = is_array($message) ? $message['deleted_at'] ?? null : $message->deleted_at;
                            $msgId = is_array($message) ? $message['id'] : $message->id;
                        @endphp

                        @if ($deletedAt)
                            <div class="p-3 rounded-premium"
                                style="background: var(--bg-input); border: 1px dashed var(--border-subtle); color: var(--text-low); font-style: italic; font-size: 0.85rem;">
                                <i class="fas fa-ban me-1"></i> Message deleted
                            </div>
                        @else
                            <div class="position-relative message-hover-container">
                                <div class="px-3 py-2 rounded-premium"
                                    style="{{ $isMe ? 'background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);' : 'background: var(--bg-surface); border: 1px solid var(--border-main); color: var(--text-high);' }}">
                                    @if (count($msgAttachments) > 0)
                                        @foreach ($msgAttachments as $att)
                                            @continue(!is_array($att) && !is_object($att))
                                            @php
                                                // Handle both array (when hydrated) and object (when model) cases
                                                $filePath = is_array($att) ? $att['file_path'] ?? '' : $att->file_path;
                                                $fileName = is_array($att) ? $att['file_name'] ?? '' : $att->file_name;
                                                $fileType = is_array($att) ? $att['file_type'] ?? '' : $att->file_type;
                                            @endphp
                                            @if (str_starts_with($fileType, 'image/'))
                                                <div
                                                    class="position-relative d-inline-block group-hover-show-download mb-2">
                                                    <img src="{{ asset('storage/' . $filePath) }}"
                                                        class="img-fluid rounded" style="max-height: 200px;">
                                                    <a href="{{ asset('storage/' . $filePath) }}"
                                                        download="{{ $fileName }}"
                                                        class="btn btn-dark btn-sm rounded-circle position-absolute top-0 end-0 m-1 opacity-75 hover-opacity-100 d-flex align-items-center justify-content-center"
                                                        style="width: 25px; height: 25px;" title="Download">
                                                        <i class="fas fa-download" style="font-size: 12px;"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center mb-2">
                                                    <a href="{{ asset('storage/' . $filePath) }}" target="_blank"
                                                        class="text-decoration-none"
                                                        style="color: {{ $isMe ? 'white' : 'var(--text-main)' }};">
                                                        <i class="fas fa-file me-1"></i> {{ $fileName }}
                                                    </a>
                                                    <a href="{{ asset('storage/' . $filePath) }}"
                                                        download="{{ $fileName }}"
                                                        class="ms-2 text-decoration-none"
                                                        style="color: {{ $isMe ? 'rgba(255,255,255,0.7)' : 'var(--text-muted)' }};"
                                                        title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif

                                    <div class="message-body" style="font-size: 0.9rem; line-height: 1.5;">
                                        {!! $msgBody !!}
                                    </div>
                                </div>
                                @if ($isMe)
                                    <button wire:click="deleteMessage({{ $msgId }})"
                                        wire:confirm="Are you sure you want to delete this message?"
                                        class="btn btn-sm p-0 rounded-circle delete-btn d-flex align-items-center justify-content-center shadow-premium"
                                        style="width: 22px; height: 22px; background: var(--bg-surface); border: 1px solid var(--border-main); color: #ef4444; position: absolute; top: -11px; right: -11px; z-index: 5; opacity: 0; transition: opacity 0.2s;"
                                        title="Delete Message">
                                        <i class="fas fa-trash" style="font-size: 10px;"></i>
                                    </button>
                                @endif
                            </div>
                        @endif
                        <div class="d-flex align-items-center gap-2 mt-1"
                            style="font-size: 0.65rem; color: var(--text-low);">
                            <span>{{ \Carbon\Carbon::parse($createdAt)->format('H:i') }}</span>
                            @if ($isMe)
                                @if ($isRead)
                                    <i class="fas fa-check-double text-primary"></i>
                                @elseif ($message['delivered_at'] ?? ($message->delivered_at ?? false))
                                    <i class="fas fa-check-double text-low"></i>
                                @else
                                    <i class="fas fa-check text-low"></i>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Typing Indicator -->
            <div x-show="isTyping" x-transition class="ms-5 mb-3"
                style="color: var(--text-low); font-size: 0.75rem;">
                <span class="fw-bold" x-text="typingUser"></span> <span class="fst-italic">is typing...</span>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 border-top border-main" wire:key="chat-input-{{ $conversation->id }}"
            style="background: var(--bg-surface);">
            @if ($isBlocked || $isBlockedBy)
                <div class="text-center py-3 text-low" style="font-size: 0.85rem;">
                    <i class="fas fa-lock me-2"></i> Chat is disabled.
                </div>
            @else
                @if ($attachment)
                    <div class="chat-media-preview d-flex flex-wrap gap-2 mb-3 p-2 border-main rounded-premium"
                        style="background: var(--bg-input);">
                        @php
                            $files = is_array($attachment) ? $attachment : [$attachment];
                        @endphp
                        @foreach ($files as $file)
                            <div class="position-relative d-inline-block border-main rounded-premium"
                                style="width: 70px; height: 70px; background: var(--bg-surface); overflow: hidden;">
                                @if (is_object($file) && method_exists($file, 'temporaryUrl') && str_starts_with($file->getMimeType(), 'image/'))
                                    <img src="{{ $file->temporaryUrl() }}"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 w-100">
                                        <i class="fas fa-file-alt fa-lg text-low"></i>
                                    </div>
                                @endif

                                <button type="button"
                                    class="btn-premium btn-premium-secondary p-0 rounded-circle position-absolute top-0 end-0 m-1 d-flex align-items-center justify-content-center shadow-premium"
                                    style="width: 20px; height: 20px; background: var(--bg-surface); font-size: 0.6rem;"
                                    wire:click="$set('attachment', null)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form wire:submit.prevent="sendMessage">
                    <div class="d-flex align-items-end gap-2">
                        <div class="flex-grow-1 position-relative">
                            <div wire:ignore wire:key="editor-{{ $conversation->id }}" x-data="chatEditor(@this, {
                                editorId: 'message-editor-{{ $conversation->id }}',
                                conversationId: '{{ $conversation->id }}',
                                userId: {{ auth()->id() }},
                                userName: '{{ auth()->user()->name }}'
                            })">
                                <textarea id="message-editor-{{ $conversation->id }}" class="form-premium-control py-3" rows="1"
                                    style="resize: none; min-height: 52px;" placeholder="Type a message..."></textarea>
                            </div>

                            <div class="position-absolute bottom-0 end-0 d-flex align-items-center gap-1 p-2"
                                style="z-index: 10;">
                                <label class="btn btn-link text-low p-1 mb-0 hover-text-high" style="cursor: pointer;"
                                    title="Attach file">
                                    <i class="fas fa-paperclip"></i>
                                    <input type="file" wire:model.live="attachment" class="d-none">
                                </label>

                                <div class="position-relative" x-data="{ showEmojiPicker: false }"
                                    @click.outside="showEmojiPicker = false">
                                    <button type="button" class="btn btn-link p-1 text-low hover-text-high"
                                        @click="showEmojiPicker = !showEmojiPicker" title="Emojis">
                                        <i class="far fa-smile-beam"></i>
                                    </button>

                                    <div x-show="showEmojiPicker" x-transition
                                        class="position-absolute bottom-100 end-0 mb-3 rounded-premium shadow-premium border-main overflow-hidden"
                                        style="z-index: 1050; width: 340px; height: 400px; background: var(--bg-surface);">
                                        <emoji-picker
                                            data-source="https://cdn.jsdelivr.net/npm/emoji-picker-element-data@1/en/emojibase/data.json"
                                            @emoji-click="
                                                let editor = tinymce.get('message-editor-{{ $conversation->id }}');
                                                if(editor) {
                                                    editor.insertContent($event.detail.unicode);
                                                }
                                                showEmojiPicker = false;
                                            "
                                            style="width: 100%; height: 100%; 
                                            --background: var(--bg-surface); 
                                            --border-color: var(--border-main);
                                            --input-border-color: var(--border-subtle);
                                            --input-font-color: var(--text-high);
                                            --button-hover-background: var(--bg-input);
                                            --category-font-color: var(--text-low);
                                            --indicator-color: var(--primary);
                                            --emoji-font-family: 'Outfit', sans-serif;">
                                        </emoji-picker>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button"
                            class="btn-premium btn-premium-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 52px; height: 52px;"
                            @click="
                                let editor = tinymce.get('message-editor-{{ $conversation->id }}');
                                if(editor) {
                                    let content = editor.getContent();
                                    const hasAttachment = document.querySelector('.chat-media-preview') !== null;
                                    if ((content && content.trim() !== '') || hasAttachment) {
                                        $wire.sendMessage(content);
                                        editor.resetContent();
                                    }
                                }
                            ">
                            <i class="fas fa-paper-plane" style="font-size: 1.1rem;"></i>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @else
        <div class="d-flex h-100 align-items-center justify-content-center flex-column p-5 text-center"
            style="background: var(--bg-surface);">
            <div class="mb-4 d-flex align-items-center justify-content-center"
                style="width: 100px; height: 100px; border-radius: var(--radius-full); background: var(--bg-input); color: var(--primary);">
                <i class="fas fa-comments fa-3x"></i>
            </div>
            <h4 class="fw-bold mb-2" style="color: var(--text-high);">Secure Team Hub</h4>
            <p style="color: var(--text-low); max-width: 320px; line-height: 1.6;">Select a contact or group from the
                sidebar to start a real-time conversation.</p>
        </div>
    @endif

    <!-- Group Info Sidebar -->
    @if ($showGroupInfo && $conversation && $conversation->type != 'private')
        <div class="position-absolute top-0 end-0 h-100 shadow-premium border-start border-main d-flex flex-column animate-slide-in-right"
            style="width: 320px; background: var(--bg-surface); backdrop-filter: blur(20px); z-index: 1000;">
            <div class="p-4 border-bottom border-main d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold" style="color: var(--text-high);">Group Info</h6>
                <button class="btn btn-link text-low p-0 hover-text-high" wire:click="$set('showGroupInfo', false)">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="flex-grow-1 overflow-auto p-4">
                <div class="text-center mb-4">
                    <div class="avatar-premium mx-auto mb-3"
                        style="width: 80px; height: 80px; background: var(--bg-input);">
                        <i class="fas fa-users fa-2x" style="color: var(--primary);"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: var(--text-high);">{{ $conversation->name }}</h5>
                    <span class="badge-premium"
                        style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border-color: rgba(var(--primary-rgb), 0.2);">
                        {{ $conversation->participants->count() }} Members
                    </span>
                </div>

                <div class="heading-label mb-3" style="font-size: 0.7rem;">Participants</div>
                <div class="d-flex flex-column gap-3">
                    @foreach ($conversation->participants as $participant)
                        <div class="d-flex align-items-center p-2 rounded-premium transition-base hover-bg-input">
                            <div class="avatar-premium flex-shrink-0" style="width: 38px; height: 38px;">
                                @if ($participant->profile_image)
                                    <img src="{{ asset('storage/' . $participant->profile_image) }}" alt="Avatar">
                                @else
                                    {{ substr($participant->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="ms-3 overflow-hidden">
                                <div class="fw-bold text-truncate"
                                    style="color: var(--text-high); font-size: 0.85rem;">
                                    {{ $participant->name }}
                                </div>
                                <div class="text-truncate" style="color: var(--text-low); font-size: 0.75rem;">
                                    {{ $participant->email }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <livewire:edit-group-modal />

    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(0);
            }
        }

        .animate-slide-in-right {
            animation: slideInRight 0.3s ease-out;
        }

        .hover-bg-input:hover {
            background: var(--bg-input) !important;
            cursor: pointer;
        }

        .transition-base {
            transition: all var(--transition-base);
        }

        .message-hover-container:hover .delete-btn {
            opacity: 1 !important;
        }

        /* Custom Scrollbar for Chat Box */
        #chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        #chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }
        #chat-messages::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.3);
            border-radius: 10px;
        }
        #chat-messages::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.5);
        }
    </style>
</div>
