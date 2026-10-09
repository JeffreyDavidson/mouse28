{{--
    X and Facebook share links for a public page. Pass the public route URL,
    never request()->url(), so previews share the published address.
    The "icon" variant renders round icon buttons; "text" renders underlined links.
--}}
@props(['url', 'text', 'variant' => 'text'])

@if ($variant === 'icon')
    <a
        href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($text) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="border-cream/20 text-cream/70 hover:border-gold hover:text-gold inline-flex size-12 items-center justify-center rounded-full border transition-colors"
        aria-label="Share on X (opens in a new tab)"
    >
        <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
    </a>
    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="border-cream/20 text-cream/70 hover:border-gold hover:text-gold inline-flex size-12 items-center justify-center rounded-full border transition-colors"
        aria-label="Share on Facebook (opens in a new tab)"
    >
        <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
    </a>
@else
    <a
        href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($text) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="text-purple inline-flex min-h-12 items-center underline underline-offset-8"
        >Post on X<x-new-tab-notice
    /></a>
    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="text-purple inline-flex min-h-12 items-center underline underline-offset-8"
        >Share on Facebook<x-new-tab-notice
    /></a>
@endif
