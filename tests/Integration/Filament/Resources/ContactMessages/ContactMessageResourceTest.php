<?php

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('the unread badge reuses its count including zero within the request', function (bool $isRead, ?string $badge): void {
    ContactMessage::query()->create([
        'name' => 'Dale',
        'email' => 'dale@example.com',
        'subject' => 'general',
        'message' => 'Hello',
        'is_read' => $isRead,
    ]);
    Cache::store('array')->flush();
    DB::enableQueryLog();
    DB::flushQueryLog();

    $first = ContactMessageResource::getNavigationBadge();
    $second = ContactMessageResource::getNavigationBadge();

    expect($first)->toBe($badge)
        ->and($second)->toBe($badge)
        ->and(DB::getQueryLog())->toHaveCount(1);
    DB::disableQueryLog();
})->with([
    'unread' => [false, '1'],
    'read' => [true, null],
]);
