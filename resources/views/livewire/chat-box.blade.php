<?php

use Livewire\Volt\Component;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
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

    #[On('conversationSelected')]
    public function loadConversation($conversationId)
    {
        $this->conversation = Conversation::with(['participants'])->find($conversationId);
        $this->loadMessages();
        $this->showSearch = false;
        $this->searchQuery = '';
        $this->attachment = null;
    }

    public function loadMessages()
    {
        if ($this->conversation) {
            $this->messages = $this->conversation->messages()->with('user')->latest()->take(50)->get()->sortBy('created_at')->values()->toArray();
        }
    }

    public function sendMessage()
    {
        if ((empty($this->body) && !$this->attachment) || !$this->conversation) {
            return;
        }

        $data = [
            'user_id' => auth()->id(),
            'body' => $this->body,
        ];

        if ($this->attachment) {
            $path = $this->attachment->store('chat-attachments', 'public');
            $data['attachment_path'] = $path;
            $data['attachment_original_name'] = $this->attachment->getClientOriginalName();

            $mime = $this->attachment->getMimeType();
            if (str_starts_with($mime, 'image/')) {
                $data['attachment_type'] = 'image';
            } elseif (str_starts_with($mime, 'video/')) {
                $data['attachment_type'] = 'video';
            } else {
                $data['attachment_type'] = 'file';
            }
        }

        $message = $this->conversation->messages()->create($data);

        broadcast(new MessageSent($message))->toOthers();

        $this->messages[] = $message->load('user')->toArray();
        $this->body = '';
        $this->attachment = null;
    }

    public function receiveMessage($messageId)
    {
        $message = Message::with('user')->find($messageId);
        if ($message && $message->conversation_id == $this->conversation->id) {
            $this->messages[] = $message->toArray();
            $this->dispatch('messageReceived'); // For unread counts update in list
        } else {
            $this->dispatch('messageReceived'); // Update unread counts globally even if not in this chat
        }
    }

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
        <div class="p-3 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center bg-dark bg-opacity-25"
            style="min-height: 73px;">
            <div class="d-flex align-items-center">
                @if ($conversation->type == 'group')
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white me-3"
                        style="width: 40px; height: 40px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $conversation->name }}</h6>
                        <small class="text-muted">{{ $conversation->participants->count() }} members</small>
                    </div>
                @else
                    @php
                        $otherParticipant = $conversation->participants->where('id', '!=', auth()->id())->first();
                    @endphp
                    @if ($otherParticipant)
                        <div class="d-flex align-items-center">
                            @if ($otherParticipant->profile_image)
                                <img src="{{ asset('storage/' . $otherParticipant->profile_image) }}"
                                    class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white"
                                    style="width: 40px; height: 40px;">
                                    {{ substr($otherParticipant->name ?? 'U', 0, 1) }}
                                </div>
                            @endif
                            <div class="ms-3">
                                <h6 class="mb-0">{{ $otherParticipant->name ?? 'User' }}</h6>
                                <small class="text-muted">{{ $otherParticipant->role->name ?? 'Member' }}</small>
                            </div>
                        </div>
                    @else
                        <h6 class="mb-0">Chat</h6>
                    @endif
                @endif
            </div>

            <div class="d-flex align-items-center gap-2">
                @if ($showSearch)
                    <input type="text" wire:model.live="searchQuery" class="form-control form-control-sm"
                        placeholder="Search in chat..." style="width: 200px;">
                @endif
                <button class="btn btn-sm btn-outline-secondary" wire:click="toggleSearch" title="Search">
                    <i class="fas fa-search"></i>
                </button>
                @if ($conversation->type == 'group')
                    <button class="btn btn-sm btn-outline-primary"
                        x-on:click="Livewire.dispatch('openEditGroupModal', { conversationId: {{ $conversation->id }} })"
                        title="Edit Group">
                        <i class="fas fa-edit"></i>
                    </button>
                @endif
                <button class="btn btn-sm btn-outline-danger" wire:click="closeChat" title="Exit Chat">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="flex-grow-1 overflow-auto p-3" id="chat-messages" x-data="{
            scrollToBottom() {
                const el = document.getElementById('chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            }
        }" x-init="scrollToBottom();
        $watch('$wire.messages', () => { setTimeout(scrollToBottom, 100) });
        
        // Reverb Listener
        if (typeof Echo !== 'undefined') {
            Echo.private('chat.{{ $conversation->id }}')
                .listen('MessageSent', (e) => {
                    console.log('Message received:', e);
                    $wire.receiveMessage(e.message.id);
                });
        }">
            <div class="d-flex flex-column gap-3">
                @foreach ($this->filteredMessages as $msg)
                    <div
                        class="d-flex {{ $msg['user_id'] == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                        @if ($conversation->type == 'group' && $msg['user_id'] != auth()->id())
                            <div class="me-2 d-flex align-items-end mb-1">
                                <small class="text-muted" style="font-size: 0.6rem;">{{ $msg['user']['name'] }}</small>
                            </div>
                        @endif

                        <div class="card border-0 {{ $msg['user_id'] == auth()->id() ? 'bg-primary text-white' : 'bg-secondary bg-opacity-10 text-main' }}"
                            style="max-width: 70%; border-radius: 12px; {{ $msg['user_id'] == auth()->id() ? 'border-bottom-right-radius: 2px;' : 'border-bottom-left-radius: 2px;' }}">
                            <div class="card-body p-2 px-3">
                                @if (isset($msg['attachment_path']) && $msg['attachment_path'])
                                    <div class="mb-2">
                                        @if ($msg['attachment_type'] == 'image')
                                            <img src="{{ asset('storage/' . $msg['attachment_path']) }}"
                                                class="img-fluid rounded" style="max-height: 200px;">
                                        @elseif($msg['attachment_type'] == 'video')
                                            <video src="{{ asset('storage/' . $msg['attachment_path']) }}" controls
                                                class="img-fluid rounded" style="max-height: 200px;"></video>
                                        @else
                                            <a href="{{ asset('storage/' . $msg['attachment_path']) }}" target="_blank"
                                                class="d-flex align-items-center gap-2 text-decoration-none {{ $msg['user_id'] == auth()->id() ? 'text-white' : 'text-dark' }}">
                                                <i class="fas fa-file"></i>
                                                <span class="text-truncate"
                                                    style="max-width: 150px;">{{ $msg['attachment_original_name'] ?? 'File' }}</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                                @if (!empty($msg['body']))
                                    <p class="mb-0 text-break">{{ $msg['body'] }}</p>
                                @endif
                                <small
                                    class="{{ $msg['user_id'] == auth()->id() ? 'text-white-50' : 'text-muted' }} d-block text-end mt-1"
                                    style="font-size: 0.7rem;">
                                    {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if (count($this->filteredMessages) == 0 && !empty($searchQuery))
                    <div class="text-center text-muted mt-5">
                        No messages found matching "{{ $searchQuery }}"
                    </div>
                @endif
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 border-top border-secondary border-opacity-10">
            @if ($attachment)
                <div class="mb-2 position-relative d-inline-block">
                    @if (str_starts_with($attachment->getMimeType(), 'image/'))
                        <img src="{{ $attachment->temporaryUrl() }}" class="rounded border"
                            style="height: 60px; width: 60px; object-fit: cover;">
                    @else
                        <div class="rounded border d-flex align-items-center justify-content-center bg-light"
                            style="height: 60px; width: 60px;">
                            <i class="fas fa-file fa-lg text-secondary"></i>
                        </div>
                    @endif
                    <button type="button"
                        class="btn btn-danger btn-sm rounded-circle position-absolute top-0 start-100 translate-middle p-0 d-flex align-items-center justify-content-center"
                        style="width: 20px; height: 20px;" wire:click="$set('attachment', null)">
                        <i class="fas fa-times" style="font-size: 10px;"></i>
                    </button>
                </div>
            @endif
            <form wire:submit.prevent="sendMessage" class="d-flex gap-2 align-items-end">
                <label class="btn btn-outline-secondary mb-0" style="cursor: pointer;">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" wire:model="attachment" class="d-none">
                </label>
                <textarea wire:model="body" class="form-control" placeholder="Type a message..." rows="1"
                    style="resize: none; min-height: 38px; max-height: 100px;" x-data="{
                        resize() {
                            $el.style.height = '38px';
                            $el.style.height = $el.scrollHeight + 'px'
                        }
                    }" x-init="resize()"
                    @input="resize()"></textarea>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    @else
        <div class="d-flex h-100 align-items-center justify-content-center text-muted flex-column">
            <div class="bg-secondary bg-opacity-10 p-4 rounded-circle mb-3">
                <i class="fas fa-comments fa-3x opacity-50"></i>
            </div>
            <h5>Welcome to Team Chat</h5>
            <p class="text-center text-muted mb-0">Select a team member or group from the list<br>to start a
                conversation.</p>
        </div>
    @endif

    <livewire:edit-group-modal />
</div>
