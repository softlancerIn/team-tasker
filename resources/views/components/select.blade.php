@props([
    'id' => null,
    'placeholder' => 'Select an option...',
    'options' => [],
    'selected' => null,
    'class' => '',
])

<div class="premium-select-wrapper {{ $class }}" wire:ignore x-data="{
    value: @if ($attributes->wire('model')->value()) @entangle($attributes->wire('model')) @else null @endif,
    selected: {{ json_encode($selected) }},
    instance: null,
    init() {
        this.instance = new TomSelect(this.$refs.select, {
            create: false,
            plugins: ['clear_button'],
            allowEmptyOption: true,
            dropdownParent: 'body',
            onItemAdd: () => this.instance.setTextboxValue(''),
            onChange: (val) => {
                this.value = val;
                this.$refs.select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        if (this.value !== null) {
            this.$watch('value', (val) => {
                if (this.instance.getValue() !== val) {
                    this.instance.setValue(val);
                }
            });
        }

        // Always set initial value if present, but priority to Livewire
        const initialValue = this.value || this.selected;
        if (initialValue !== null && initialValue !== undefined) {
            this.instance.setValue(initialValue);
        }
    }
}">
    <select x-ref="select" id="{{ $id }}"
        {{ $attributes->whereDoesntStartWith('wire:model')->merge(['placeholder' => $placeholder, 'autocomplete' => 'off']) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $val => $label)
            <option value="{{ $val }}" {{ $selected == $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
        {{ $slot }}
    </select>
</div>
