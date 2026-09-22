@props([
    'title',
    'count' => null,
    'id' => null,
])

<div class="border-navy/10 mb-5 flex items-end justify-between gap-4 border-b pb-3">
    <h2 @if ($id) id="{{ $id }}" @endif class="font-heading text-navy text-3xl font-bold">{{ $title }}</h2>
    @if ($count !== null)
        <span class="text-navy/65 text-sm">{{ $count }} found</span>
    @endif
</div>
