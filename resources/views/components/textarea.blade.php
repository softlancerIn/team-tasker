@props([
    'id' => null,
    'name' => null,
    'placeholder' => '',
    'required' => false,
    'rows' => 4,
    'texteditor' => false,
    'class' => '',
])

<textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => 'form-control ' . ($texteditor ? 'rich-editor ' : '') . $class]) }}>{{ $slot }}</textarea>
