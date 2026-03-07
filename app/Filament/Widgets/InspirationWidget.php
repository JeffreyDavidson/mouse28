<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class InspirationWidget extends Widget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.inspiration';

    public function getPrompt(): string
    {
        $prompts = [
            "What's your family's favorite hidden Mickey?",
            "Share a sensory-friendly tip from your last visit",
            "Review the last thing you ate at the parks",
            "What ride has the best queue experience?",
            "Describe your perfect Disney day from rope drop to fireworks",
            "What's one thing first-timers always miss?",
            "Share a moment that made the magic real for your family",
        ];

        return $prompts[array_rand($prompts)];
    }
}
