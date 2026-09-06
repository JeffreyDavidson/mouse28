<?php

use App\Models\Episode;
use Illuminate\Support\Facades\Storage;

test('missing hosted audio falls back to the external episode URL', function (): void {
    Storage::fake('public');

    $episode = Episode::factory()->make([
        'audio_path' => 'episodes/audio/missing.mp3',
        'audio_url' => 'https://cdn.example.com/fallback-episode.mp3',
    ]);

    $audioUrl = $episode->audio_source_url;
    $fileSize = $episode->audioFileSize();

    expect($audioUrl)->toBe('https://cdn.example.com/fallback-episode.mp3')
        ->and($fileSize)->toBe(0);
});
