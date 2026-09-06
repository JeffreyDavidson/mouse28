<?php

use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('missing hosted audio falls back to the external episode URL', function (): void {
    Storage::fake('public');

    $episode = Episode::factory()->create([
        'audio_path' => 'episodes/audio/missing.mp3',
        'audio_url' => 'https://cdn.example.com/fallback-episode.mp3',
    ]);

    expect($episode->audio_source_url)->toBe($episode->audio_url)
        ->and($episode->audioFileSize())->toBe(0);
});
