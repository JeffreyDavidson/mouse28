<?php

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

test('administrators can read and delete contact messages but never create or edit them', function (): void {
    actingAs(User::factory()->admin()->create());
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => 'general',
        'message' => 'A park question.',
    ]);

    expect(ContactMessageResource::canViewAny())->toBeTrue()
        ->and(ContactMessageResource::canView($message))->toBeTrue()
        ->and(ContactMessageResource::canDelete($message))->toBeTrue()
        ->and(ContactMessageResource::canCreate())->toBeFalse()
        ->and(ContactMessageResource::canEdit($message))->toBeFalse();
});
