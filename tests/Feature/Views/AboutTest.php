<?php

use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('public index page renders', function (): void {
    get(route('about'))
        ->assertOk()
        ->assertSee('Disney looks different');
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
