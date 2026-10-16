<?php

use App\Enums\ContentType;
use App\Filament\Concerns\ListsContentByStatus;
use App\Filament\Resources\Guides\Pages\ListGuides;
use App\Models\Guide;
use Database\Factories\GuideFactory;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    actingAsAdmin();
});

dataset('content list pages', [
    'guides' => [ListGuides::class, fn (): GuideFactory => Guide::factory()],
]);

test('the guide list page builds its tabs and header from the shared concern', function (string $listPage): void {
    expect(class_uses_recursive($listPage))->toContain(ListsContentByStatus::class);
})->with([
    'guides' => ListGuides::class,
]);

/** @param  array<string, string>  $tabs */
test('the guide list offers status tabs with a review due tab', function (ListGuides $page, array $tabs): void {
    expect(array_map(fn (Tab $tab): string|Htmlable|null => $tab->getLabel(), $page->getTabs()))->toBe($tabs);
})->with([
    'guides' => [fn (): ListGuides => app(ListGuides::class), ['all' => 'All', 'attention' => 'Needs attention', 'drafts' => 'Drafts', 'scheduled' => 'Scheduled', 'published' => 'Published', 'review-due' => 'Review due']],
]);

test('the published tab leaves out scheduled content', function (string $listPage, GuideFactory $factory): void {
    $published = $factory->create();
    $scheduled = $factory
        ->scheduled()
        ->create();

    livewire($listPage)
        ->set('activeTab', 'published')
        ->assertCanSeeTableRecords([$published])
        ->assertCanNotSeeTableRecords([$scheduled]);
})->with('content list pages');

test('the header shows the content type title, description and create link', function (ContentType $type, string $subtitle, string $createLabel): void {
    get($type->resource()::getUrl())
        ->assertOk()
        ->assertSeeInOrder([$type->pluralLabel(), $subtitle, 'Published', 'Drafts', $type->resource()::getUrl('create'), $createLabel]);
})->with([
    'guides' => [ContentType::Guide, 'Manage your accessibility guides', 'New Guide'],
]);
