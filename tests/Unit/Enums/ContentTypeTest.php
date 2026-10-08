<?php

use App\Enums\ContentType;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use Filament\Support\Icons\Heroicon;

test('content types keep their stored values', function (): void {
    expect(array_column(ContentType::cases(), 'value'))->toBe(['post', 'episode', 'guide']);
});

test('content types expose their label, colour, icon, text class and resource', function (ContentType $type, string $label, string $pluralLabel, string $color, Heroicon $icon, string $textClass, string $resource): void {
    expect($type->getLabel())->toBe($label)
        ->and($type->pluralLabel())
        ->toBe($pluralLabel)
        ->and($type->getColor())
        ->toBe($color)
        ->and($type->getIcon())
        ->toBe($icon)
        ->and($type->textClass())
        ->toBe($textClass)
        ->and($type->resource())
        ->toBe($resource);
})->with([
    'post' => [ContentType::Post, 'Post', 'Blog Posts', 'primary', Heroicon::OutlinedDocumentText, 'text-mouse-purple', PostResource::class],
    'episode' => [ContentType::Episode, 'Episode', 'Episodes', 'warning', Heroicon::OutlinedMicrophone, 'text-mouse-gold-dark', EpisodeResource::class],
    'guide' => [ContentType::Guide, 'Guide', 'Guides', 'info', Heroicon::OutlinedBookOpen, 'text-mouse-teal', GuideResource::class],
]);
