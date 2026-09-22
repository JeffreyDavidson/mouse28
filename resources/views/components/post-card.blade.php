@props([
    'post',
    'variant' => 'archive',
])

@if ($variant === 'related')
    <article {{ $attributes->class(['group min-w-0']) }}>
        <a
            href="{{ route('blog.show', $post) }}"
            aria-label="Read {{ $post->title }}"
            class="block overflow-hidden rounded-xl"
        >
            <x-post-artwork
                :post="$post"
                :compact="true"
                class="aspect-[4/3] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.025]"
            />
        </a>
        <p class="text-purple mt-4 text-sm font-semibold">{{ $post->category_label }}</p>
        <h3 class="font-heading text-navy group-hover:text-purple mt-1 text-2xl [font-weight:600] tracking-[-0.015em] transition-colors">
            <a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
        </h3>
        <p class="text-navy/60 mt-2 text-sm">{{ $post->reading_time }} min read</p>
    </article>
@else
    <article {{ $attributes->class(['group min-w-0']) }}>
        <a
            href="{{ route('blog.show', $post) }}"
            aria-label="Read {{ $post->title }}"
            class="block overflow-hidden rounded-xl"
        >
            <x-post-artwork
                :post="$post"
                :compact="true"
                class="aspect-[4/3] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]"
            />
        </a>
        <div class="pt-5">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                <span class="text-purple font-semibold">{{ $post->category_label }}</span>
                <span class="text-navy/60">{{ $post->published_at->format('M j, Y') }}</span>
                <span class="text-navy/60">{{ $post->reading_time }} min read</span>
            </div>
            <h3 class="font-heading text-navy group-hover:text-purple mt-2 text-2xl [font-weight:600] tracking-[-0.015em] text-balance transition-colors sm:text-3xl">
                <a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
            </h3>
            @if ($post->excerpt)
                <p class="text-navy/70 mt-3 max-w-[58ch] text-base/7 text-pretty">
                    {{ Str::limit($post->excerpt, 180) }}
                </p>
            @endif
            <p class="text-navy/60 mt-4 text-sm">By {{ $post->author_name }}</p>
        </div>
    </article>
@endif
