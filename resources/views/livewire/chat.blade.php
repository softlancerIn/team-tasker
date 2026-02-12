<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="row g-0 h-100" style="height: calc(100vh - 120px) !important;">
    <div class="col-md-3 col-lg-3 border-end border-secondary border-opacity-10 h-100 d-flex flex-column">
        <livewire:chat-list />
    </div>
    <div class="col-md-9 col-lg-9 h-100 d-flex flex-column">
        <livewire:chat-box />
    </div>
</div>
