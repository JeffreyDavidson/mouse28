@props(['id', 'message', 'colorClass' => 'text-red-800'])

@if ($message)
    <p id="{{ $id }}" role="alert" {{ $attributes->class(['mt-2 text-sm', $colorClass]) }}>{{ $message }}</p>
@endif
