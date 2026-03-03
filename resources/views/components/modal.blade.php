<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" {{ $attributes }} wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered {{ $size ?? '' }}">
        <div class="modal-content bg-surface border-main shadow-lg" style="backdrop-filter: blur(20px);">
            <div class="modal-header border-subtle py-3 px-4">
                <h5 class="modal-title fw-bold text-high" style="font-size: 1.1rem;">{{ $title }}</h5>
                <button type="button" class="btn-close opacity-50" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if (isset($formAction))
                <form action="{{ $formAction }}" method="POST"
                    @if (isset($enctype)) enctype="{{ $enctype }}" @endif>
                    @csrf
                    @if (isset($method))
                        @method($method)
                    @endif
            @endif
            <div class="modal-body p-4 {{ $bodyClass ?? '' }}">
                {{ $slot }}
            </div>
            <div class="modal-footer border-subtle py-3 px-4">
                @if (isset($footer))
                    {{ $footer }}
                @else
                    <button type="button" class="btn-premium btn-premium-secondary py-2 px-4" data-bs-dismiss="modal"
                        style="font-size: 0.85rem;">{{ $cancelText ?? 'Cancel' }}</button>
                    @if (isset($submitText))
                        <button type="submit" class="btn-premium btn-premium-{{ $variant ?? 'primary' }} py-2 px-4"
                            style="font-size: 0.85rem;">{{ $submitText }}</button>
                    @endif
                @endif
            </div>
            @if (isset($formAction))
                </form>
            @endif
        </div>
    </div>
</div>
