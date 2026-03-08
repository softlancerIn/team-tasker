<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component {
    public $selectedConversationId = null;

    #[On('conversationSelected')]
    public function handleConversationSelected($conversationId)
    {
        $this->selectedConversationId = $conversationId;
    }

    #[On('backToUserList')]
    public function handleBackToUserList()
    {
        $this->selectedConversationId = null;
    }
}; ?>

<div class="row g-0 h-100" style="height: calc(100vh - 120px) !important;">
    <div class="row g-0 h-100 overflow-hidden" style="height: calc(100vh - 120px) !important;">
        <div class="col-12 col-md-4 col-lg-3 border-end h-100 d-flex flex-column {{ $selectedConversationId ? 'd-none d-md-flex' : '' }}"
            style="border-color: var(--border-color) !important;">
            <livewire:chat-list wire:key="chat-list-sidebar" />
        </div>
        <div
            class="col-12 col-md-8 col-lg-9 h-100 d-flex flex-column {{ $selectedConversationId ? '' : 'd-none d-md-flex' }}">
            <livewire:chat-box :selectedConversationId="$selectedConversationId" wire:key="chat-box-details-{{ $selectedConversationId }}" />
        </div>
    </div>
