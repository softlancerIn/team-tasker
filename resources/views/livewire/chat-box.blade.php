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

    public $conversation;
    public $body = '';
    public $attachment;
    public $messages = [];
    public $searchQuery = '';
    public $showSearch = false;
    public $isBlocked = false;
    public $isBlockedBy = false;
    public $receiver; // Added for the other participant in a private chat
    public $perPage = 10; // Added for pagination

    #[On('conversationSelected')]
    public function loadConversation($conversationId)
    {
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
            Auth::user()
                ->conversations()
                ->updateExistingPivot($this->conversation->id, ['last_read_at' => now()]);
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

        // Broadcast to Socket.IO
        $this->dispatch('send-message-to-node', [
            'room' => 'chat.' . $this->conversation->id,
            'message' => [
                'id' => $message->id,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'user_avatar' => auth()->user()->profile_image,
                'body' => $message->body,
                'created_at' => $message->created_at->toISOString(),
                'attachments' => $message->attachments()->get(), // Fetch fresh attachments
            ],
        ]);

        $this->messages[] = $message->load('user', 'attachments')->toArray(); // Load attachments for local display
        $this->body = '';
        $this->dispatch('scroll-bottom');
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

            broadcast(new MessageRead($message->id, $message->user_id))->toOthers();

            $this->dispatch('messageReceived'); // For unread counts update in list
        } else {
            // Mark as delivered
            if (!$message->delivered_at) {
                $message->update(['delivered_at' => now()]);
                broadcast(new MessageDelivered($message->id, $message->user_id))->toOthers();
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

<div class="d-flex flex-column h-100">
    @if ($conversation)

        <!-- Header -->
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center"
            style="min-height: 73px; background: var(--card-bg); border-bottom: 1px solid var(--border-color) !important;"
            x-data="{
                status: 'Offline',
                userId: {{ $conversation->type == 'private' && $receiver ? $receiver->id : 'null' }},
                init() {
                    if (this.userId && window.socket) {
                        window.socket.on('user_connected', (uid) => {
                            if (uid == this.userId) this.status = 'Online';
                        });
                        window.socket.on('disconnect_user', (uid) => { // check exact event name
                            if (uid == this.userId) this.status = 'Offline';
                        });
                        setInterval(() => {
                            const sidebarUser = document.querySelector(`.user-item[data-user-id='${this.userId}'] .status-dot`);
                            if (sidebarUser && sidebarUser.classList.contains('bg-success')) {
                                this.status = 'Online';
                            } else {
                                this.status = 'Offline';
                            }
                        }, 2000);
                    }
                }
            }">
            <div class="d-flex align-items-center">
                @if ($conversation->type == 'group')
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white me-3"
                        style="width: 45px; height: 45px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--text-main);">{{ $conversation->name }}</h6>
                        <small style="color: var(--text-muted);">{{ $conversation->participants->count() }}
                            members</small>
                    </div>
                @else
                    @php
                        $otherParticipant = $conversation->participants->where('id', '!=', auth()->id())->first();
                    @endphp
                    @if ($otherParticipant)
                        <div class="d-flex align-items-center">
                            @if ($otherParticipant->profile_image)
                                <img src="{{ asset('storage/' . $otherParticipant->profile_image) }}"
                                    class="rounded-circle" width="45" height="45" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                    style="width: 45px; height: 45px; font-size: 1.2rem;">
                                    {{ substr($otherParticipant->name ?? 'U', 0, 1) }}
                                </div>
                            @endif
                            <div class="ms-3">
                                <h6 class="mb-0 fw-bold" style="color: var(--text-main);">
                                    {{ $otherParticipant->name ?? 'User' }}</h6>
                                <small :class="status == 'Online' ? 'text-success' : 'text-secondary'"
                                    x-text="status"></small>
                            </div>
                        </div>
                    @else
                        <h6 class="mb-0" style="color: var(--text-main);">Chat</h6>
                    @endif
                @endif
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-2"
                        style="color: var(--text-muted);"></i>
                    <input type="text" wire:model.live="searchQuery"
                        class="form-control form-control-sm rounded-pill ps-4" placeholder="Search Message"
                        style="width: 200px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm rounded-circle" type="button" data-bs-toggle="dropdown"
                        style="background: var(--input-bg); color: var(--text-muted);">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                        style="background: var(--card-bg); border: 1px solid var(--border-color);">
                        <li>
                            <a class="dropdown-item text-danger" href="#" wire:click.prevent="clearChatHistory"
                                wire:confirm="Are you sure? This will delete all messages for everyone in this chat.">
                                <i class="fas fa-trash-alt me-2"></i> Clear Chat History
                            </a>
                        </li>
                        <li>
                            @if ($isBlocked)
                                <a class="dropdown-item" href="#" wire:click.prevent="unblockUser"
                                    style="color: var(--text-main);">
                                    <i class="fas fa-user-check me-2"></i> Unblock User
                                </a>
                            @else
                                <a class="dropdown-item" href="#" wire:click.prevent="blockUser"
                                    wire:confirm="Are you sure you want to block this user?"
                                    style="color: var(--text-main);">
                                    <i class="fas fa-user-slash me-2"></i> Block User
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
                <div class="alert alert-danger text-center mx-4 mt-3">
                    <i class="fas fa-ban me-2"></i> You have blocked this user.
                </div>
            @elseif($isBlockedBy)
                <div class="alert alert-warning text-center mx-4 mt-3">
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
                <div class="d-flex mb-3 {{ $isMe ? 'justify-content-end' : '' }}">
                    @if (!$isMe)
                        <img src="{{ isset($msgUser['profile_image']) && $msgUser['profile_image'] ? asset('storage/' . $msgUser['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($msgUser['name'] ?? 'User') }}"
                            class="rounded-circle me-2" width="35" height="35" alt="User">
                    @endif

                    <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }}"
                        style="max-width: 75%;">
                        <div class="p-3 rounded-3"
                            style="{{ $isMe ? 'background: var(--primary); color: white;' : 'background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-main);' }}">
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
                                        <div class="position-relative d-inline-block group-hover-show-download mb-2">
                                            <img src="{{ asset('storage/' . $filePath) }}" class="img-fluid rounded"
                                                style="max-height: 200px;">
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
                                                download="{{ $fileName }}" class="ms-2 text-decoration-none"
                                                style="color: {{ $isMe ? 'rgba(255,255,255,0.7)' : 'var(--text-muted)' }};"
                                                title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <div class="message-body">
                                {!! $msgBody !!}
                            </div>
                        </div>
                        <small class="mt-1" style="font-size: 0.75rem; color: var(--text-muted);">
                            {{ \Carbon\Carbon::parse($createdAt)->format('d-m-Y H:i') }}
                            @if ($isMe)
                                @if ($isRead)
                                    <i class="fas fa-check-double text-primary ms-1"></i>
                                    <!-- Blue Double Tick (Read) -->
                                @elseif ($message['delivered_at'] ?? ($message->delivered_at ?? false))
                                    <i class="fas fa-check-double text-secondary ms-1"></i>
                                    <!-- Grey Double Tick (Delivered) -->
                                @else
                                    <i class="fas fa-check text-secondary ms-1"></i>
                                    <!-- Grey Single Tick (Sent) -->
                                @endif
                            @endif
                        </small>
                    </div>
                </div>
            @endforeach

            <!-- Typing Indicator -->
            <div x-show="isTyping" x-transition class="small ms-5 fst-italic" style="color: var(--text-muted);">
                <span x-text="typingUser"></span> is typing...
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 border-top" style="background: var(--card-bg); border-color: var(--border-color) !important;">
            @if ($isBlocked || $isBlockedBy)
                <div class="text-center py-3" style="color: var(--text-muted);">
                    <i class="fas fa-lock me-1"></i> Chat is disabled.
                </div>
            @else
                @if ($attachment)
                    <div class="chat-media-preview d-flex flex-wrap gap-2 mb-2 p-2 border rounded"
                        style="background: var(--input-bg); border-color: var(--border-color) !important;">
                        @php
                            $files = is_array($attachment) ? $attachment : [$attachment];
                        @endphp
                        @foreach ($files as $file)
                            <div class="position-relative d-inline-block border rounded"
                                style="width: 60px; height: 60px; background: var(--card-bg); border-color: var(--border-color) !important;">
                                @if (is_object($file) && method_exists($file, 'temporaryUrl') && str_starts_with($file->getMimeType(), 'image/'))
                                    <img src="{{ $file->temporaryUrl() }}" class="rounded"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 w-100">
                                        <i class="fas fa-file fa-lg" style="color: var(--text-muted);"></i>
                                    </div>
                                @endif

                                <button type="button"
                                    class="btn btn-danger btn-sm rounded-circle position-absolute top-0 start-100 translate-middle p-0 d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 20px; height: 20px;" wire:click="$set('attachment', null)">
                                    <i class="fas fa-times" style="font-size: 10px;"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form wire:submit.prevent="sendMessage">
                    <div class="position-relative border rounded overflow-hidden"
                        style="border-color: var(--border-color) !important;">
                        <div wire:ignore wire:key="editor-{{ $conversation->id }}" x-data="chatEditor(@this, {
                            editorId: 'message-editor-{{ $conversation->id }}',
                            conversationId: '{{ $conversation->id }}',
                            userId: {{ auth()->id() }},
                            userName: '{{ auth()->user()->name }}'
                        })">
                            <textarea id="message-editor-{{ $conversation->id }}" class="form-control" rows="1" style="resize: none;"
                                placeholder="Type a message..."></textarea>
                        </div>

                        <!-- Icons Positioned Absolute Bottom Right -->
                        <div class="position-absolute bottom-0 end-0 d-flex gap-1 p-2" style="z-index: 10;">
                            <label class="btn btn-link text-muted p-1 mb-0" style="cursor: pointer;"
                                title="Attach File">
                                <i class="fas fa-paperclip"></i>
                                <input type="file" wire:model.live="attachment" class="d-none">
                            </label>
                            <!-- Emoji Picker -->
                            <div class="position-relative" x-data="{ showEmojiPicker: false }"
                                @click.outside="showEmojiPicker = false">
                                <button type="button" class="btn btn-link p-1" style="color: var(--text-muted);"
                                    @click="showEmojiPicker = !showEmojiPicker" title="Emojis">
                                    <i class="far fa-smile"></i>
                                </button>

                                <div x-show="showEmojiPicker" x-transition
                                    class="position-absolute bottom-100 end-0 mb-2 rounded shadow p-0"
                                    style="z-index: 1050; width: 350px; height: 400px; background: var(--card-bg); border: 1px solid var(--border-color);">
                                    <emoji-picker
                                        @emoji-click="
                                        $event.detail.unicode;
                                        let editor = tinymce.get('message-editor-{{ $conversation->id }}');
                                        if(editor) {
                                            editor.insertContent($event.detail.unicode);
                                        }
                                        showEmojiPicker = false;
                                    "
                                        style="width: 100%; height: 100%; 
                                        --background: var(--card-bg); 
                                        --border-color: var(--border-color);
                                        --input-border-color: var(--border-color);
                                        --input-font-color: var(--text-main);
                                        --button-hover-background: rgba(var(--primary-rgb), 0.1);
                                        --category-font-color: var(--text-muted);
                                        --indicator-color: var(--primary);
                                        --emoji-font-family: 'Outfit', sans-serif;">
                                    </emoji-picker>
                                </div>
                            </div>

                            <!-- Send Button -->
                            <button type="button"
                                class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1"
                                style="width: 30px; height: 30px;"
                                @click="
                                            let editor = tinymce.get('message-editor-{{ $conversation->id }}');
                                            if(editor) {
                                                let content = editor.getContent();
                                                // Check if the preview element exists (indicates attachment is present)
                                                const hasAttachment = document.querySelector('.chat-media-preview') !== null;

                                                if ((content && content.trim() !== '') || hasAttachment) {
                                                    $wire.sendMessage(content);
                                                    editor.resetContent();
                                                }
                                            }
                                        ">
                                <i class="fas fa-paper-plane" style="font-size: 0.8rem;"></i>
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    @else
        <div class="d-flex h-100 align-items-center justify-content-center flex-column"
            style="color: var(--text-muted);">
            <div class="p-4 rounded-circle mb-3" style="background: rgba(100, 116, 139, 0.1);">
                <i class="fas fa-comments fa-3x opacity-50"></i>
            </div>
            <h5>Welcome to Team Chat</h5>
            <p class="text-center mb-0" style="color: var(--text-muted);">Select a team member or group from the
                list<br>to start a
                conversation.</p>
        </div>
    @endif

    <livewire:edit-group-modal />
</div>
