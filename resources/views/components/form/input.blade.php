@props([
    'id',
    'name',
    'label',
    'value' => null,
    'type' => 'text',
    'error' => null,
    'errorId' => null,
    'errorClass' => null,
    'inputClass' => 'min-h-12 w-full rounded-xl border px-4 py-3 text-base transition-colors focus:ring-2 focus:outline-none',
    'labelClass' => 'sr-only',
])

@php($errorId ??= $id.'-error')

<label for="{{ $id }}" class="{{ $labelClass }}">{{ $label }}</label>
<input
    {{ $attributes->merge(['id' => $id, 'name' => $name, 'type' => $type, 'value' => $value, 'class' => $inputClass]) }}
    @if ($error) aria-invalid="true" aria-describedby="{{ $errorId }}" autofocus @endif
/>

@if ($error)
    @if ($errorClass)
        <x-form.error :id="$errorId" :message="$error" :color-class="$errorClass" />
    @else
        <x-form.error :id="$errorId" :message="$error" />
    @endif
@endif
