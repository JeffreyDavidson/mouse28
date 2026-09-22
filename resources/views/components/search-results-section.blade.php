@props([
    'id',
    'title',
    'count',
])

<section aria-labelledby="{{ $id }}">
    <x-editorial-section-heading :id="$id" :title="$title" :count="$count" />
    <div class="grid gap-4 md:grid-cols-2">{{ $slot }}</div>
    @if (isset($pagination))
        <div class="episodes-pagination mt-8">{{ $pagination }}</div>
    @endif
</section>
