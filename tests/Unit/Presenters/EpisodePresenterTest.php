<?php

use App\Models\Episode;
use App\Presenters\EpisodePresenter;
use App\Services\SquareResponsiveImageVariants;

covers(EpisodePresenter::class);

function presenterFor(Episode $episode): EpisodePresenter
{
    return new EpisodePresenter($episode, new SquareResponsiveImageVariants);
}

test('an episode duration is shown as minutes and seconds', function (?int $seconds, string $duration): void {
    $episode = new Episode;
    $episode->duration_seconds = $seconds;

    expect(presenterFor($episode)->duration())->toBe($duration);
})->with([
    'missing' => [null, ''],
    'zero' => [0, ''],
    'under a minute' => [42, '0:42'],
    'trailer' => [107, '1:47'],
    'just under an hour' => [3299, '54:59'],
    'over an hour' => [3723, '62:03'],
]);

test('an episode is sparse only without a player, a transcript and substantial show notes', function (?string $showNotes, ?string $transcript, ?string $transistorUrl, bool $sparse): void {
    $episode = new Episode;
    $episode->show_notes = $showNotes;
    $episode->transcript = $transcript;
    $episode->transistor_url = $transistorUrl;

    expect(presenterFor($episode)->isSparse())->toBe($sparse);
})->with([
    'nothing at all' => [null, null, null, true],
    'short show notes' => ['A short note.', null, null, true],
    'markup does not count as show notes' => ['<p><strong>'.str_repeat('x', 100).'</strong></p>'.str_repeat('<br>', 100), null, null, true],
    'whitespace is squished' => [str_repeat("word \n\n ", 20), null, null, true],
    'show notes of 160 characters' => [str_repeat('x', 160), null, null, false],
    'a transcript' => [null, 'Hello', null, false],
    'a Transistor player' => [null, null, 'https://share.transistor.fm/s/abc123', false],
    'a link that is not a Transistor share link' => [null, null, 'https://example.com/s/abc123', true],
]);
