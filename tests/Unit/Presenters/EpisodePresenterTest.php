<?php

use App\Models\Episode;
use App\Presenters\EpisodePresenter;

covers(EpisodePresenter::class);

test('an episode duration is shown as minutes and seconds', function (?int $seconds, string $duration): void {
    $episode = new Episode;
    $episode->duration_seconds = $seconds;

    expect(EpisodePresenter::from($episode)->duration())->toBe($duration);
})->with([
    'missing' => [null, ''],
    'zero' => [0, ''],
    'under a minute' => [42, '0:42'],
    'trailer' => [107, '1:47'],
    'just under an hour' => [3299, '54:59'],
    'over an hour' => [3723, '62:03'],
]);
