@props(['id', 'message'])

@if ($message)
    <p id="{{ $id }}" role="alert" {{ $attributes->class(['mt-2 text-sm text-red-800']) }}>{{ $message }}</p>
@endif
