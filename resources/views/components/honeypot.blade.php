@props(['name', 'id' => null])

{{-- Hidden from people and assistive technology; a filled field marks the submission as a bot's. --}}
<div {{ $attributes->merge(['aria-hidden' => 'true']) }}>
    <label for="{{ $id ?? $name }}">Website</label>
    <input id="{{ $id ?? $name }}" type="text" name="{{ $name }}" tabindex="-1" autocomplete="off" />
</div>
