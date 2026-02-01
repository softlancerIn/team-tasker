<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" {{ $attributes }}>
    <div class="modal-dialog modal-dialog-centered {{ $size ?? '' }}">
        <div class="modal-content bg-dark border-{{ $variant ?? 'secondary' }} border-opacity-25 shadow-lg">
            <div class="modal-header border-{{ $variant ?? 'secondary' }} border-opacity-25">
                <h5 class="modal-title font-weight-500 text-white">{{ $title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            @if (isset($formAction))
                <form action="{{ $formAction }}" method="POST"
                    @if (isset($enctype)) enctype="{{ $enctype }}" @endif>
                    @csrf
                    @if (isset($method))
                        @method($method)
                    @endif
            @endif
            <div class="modal-body {{ $bodyClass ?? '' }}">
                {{ $slot }}
            </div>
            <div class="modal-footer border-{{ $variant ?? 'secondary' }} border-opacity-25">
                <button type="button" class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">{{ $cancelText ?? 'Cancel' }}</button>
                @if (isset($submitText))
                    <button type="submit" class="btn btn-{{ $variant ?? 'primary' }}">{{ $submitText }}</button>
                @endif
            </div>
            @if (isset($formAction))
                </form>
            @endif
        </div>
    </div>
</div>
