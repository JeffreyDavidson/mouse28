<x-layouts.app
    :title="$pageTitle"
    :description="$pageDescription"
    og-image="/images/hero-family.jpg"
    :canonical="$canonicalUrl"
    :robots="$robots"
    :dispatch-layout="true"
    :show-footer-newsletter="! ($hasAnyPosts || $search || $category)"
>
    <!--
        THESIS: The blog is an artwork-led family journal, not a widget sidebar wrapped around a post feed.
        OWN-WORLD: Navy cloth, cream paper, Besley headlines, gold rules, purple links, and published story artwork.
        STORY: Readers meet the newest useful story, narrow the archive, and browse visually distinct articles.
        FIRST VIEWPORT: A compact masthead opens into one large featured story with artwork and a direct reading path.
        FORM [seed: field-journal]: Editorial archive with an asymmetric story mosaic and one calm discovery band.
    -->

    <div data-editorial-blog>
        <livewire:blog-index />
    </div>
</x-layouts.app>
