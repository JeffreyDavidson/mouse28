<?php

use App\Filament\Widgets\InspirationWidget;

test('the prompt is always one of the writing prompts', function (): void {
    $prompts = collect(range(1, 50))
        ->map(fn (): string => (new InspirationWidget)->getPrompt())
        ->unique();

    expect($prompts->count())->toBeGreaterThan(1)
        ->and($prompts->every(fn (string $prompt): bool => $prompt !== ''))
        ->toBeTrue();
});
