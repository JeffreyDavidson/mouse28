@props(['url', 'message' => 'Policies can change. Review the official source before your visit.'])

<div {{ $attributes->class(['border-navy/12 border-y py-6']) }}>
    <p class="text-navy/65 text-sm/6">{{ $message }}</p>
    <a
        href="{{ $url }}"
        target="_blank"
        rel="noopener noreferrer"
        class="text-purple hover:text-navy mt-2 inline-flex min-h-12 items-center font-semibold underline underline-offset-8"
    >View official source</a>
</div>
