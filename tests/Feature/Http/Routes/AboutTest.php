<?php

use App\Models\Podcast;
use Dom\HTMLDocument;
use Dom\XPath;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('public index page renders', function (): void {
    get(route('about'))
        ->assertOk()
        ->assertSee('Disney looks different');
});

test('primary navigation identifies About as the current destination', function (): void {
    $response = get(route('about'))
        ->assertOk();

    $document = HTMLDocument::createFromString($response->getContent(), LIBXML_NOERROR);
    $xpath = new XPath($document);
    $links = $xpath->query('//*[local-name()="a"][contains(concat(" ", normalize-space(@class), " "), " dispatch-nav-link ") and @aria-current="page"]');

    expect($links)->toHaveCount(1)
        ->and(trim($links->item(0)->textContent))->toBe('About')
        ->and($links->item(0)->getAttribute('href'))->toBe(route('about'));
});

test('about page uses an editorial family story with separate host profiles', function (): void {
    get(route('about'))
        ->assertOk()
        ->assertSee('data-about-editorial', false)
        ->assertSee('Jeffrey Davidson')
        ->assertSee('Cassie Davidson')
        ->assertDontSee('Park Visits')
        ->assertDontSee('Chapter One')
        ->assertDontSee('8 years old');
});

test('landing page provides search and social metadata', function (): void {
    Podcast::query()->create([
        'name' => 'Mouse28 Weekly',
        'description' => 'A weekly Disney parks podcast for accessibility-minded families.',
        'cover_image' => 'podcasts/show-cover.jpg',
    ]);

    get(route('about'))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="About the Davidson Family | Mouse28">', false);
});

test('page copy and metadata avoid em dashes', function (): void {
    get(route('about'))
        ->assertOk()
        ->assertDontSee('—');
});

test('page uses the dispatch editorial system', function (): void {
    get(route('about'))
        ->assertOk()
        ->assertSee('data-brand-wordmark', false)
        ->assertSee('data-about-editorial', false)
        ->assertSee('js-dispatch-pages', false);
});
