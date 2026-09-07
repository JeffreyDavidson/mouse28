<?php

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('contact topics and free text retain readable admin labels', function (string $subject, string $label): void {
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => $subject,
        'message' => 'A question about the parks.',
    ]);
    actingAs(User::factory()->admin()->create());

    get(ContactMessageResource::getUrl('view', ['record' => $message]))
        ->assertOk()
        ->assertSee($label);
})->with([
    'recognized topic' => ['guest', 'Podcast Guest'],
    'free text' => ['A custom question', 'A custom question'],
]);
