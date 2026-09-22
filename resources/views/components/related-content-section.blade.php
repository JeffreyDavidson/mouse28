@props([
    'title',
    'labelId',
    'gridClass' => 'md:grid-cols-3',
    'headingClass' => 'font-heading text-navy text-4xl/[1.1] [font-weight:640] tracking-[-0.025em] text-balance sm:text-5xl',
    'containerClass' => 'mx-auto max-w-[86rem] px-4 sm:px-6',
])

<section {{ $attributes->class(['border-navy/12 border-y py-8']) }} aria-labelledby="{{ $labelId }}">
    <div class="{{ $containerClass }}">
        <h2 id="{{ $labelId }}" class="{{ $headingClass }}">{{ $title }}</h2>
        <div class="mt-9 grid gap-x-8 gap-y-12 {{ $gridClass }}">{{ $slot }}</div>
    </div>
</section>
