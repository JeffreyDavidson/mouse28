@props(['field', 'id', 'bag' => null])

@php
    $messageBag = $bag ? $errors->getBag($bag) : $errors;
@endphp

@if ($messageBag->has($field))
    <p id="{{ $id }}" role="alert" {{ $attributes->class(['mt-2 text-sm text-red-800']) }}>
        {{ $messageBag->first($field) }}
    </p>
@endif
