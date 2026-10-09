<?php

use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

covers(OptimizedImageUpload::class);

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()
    ->admin()
    ->create()));

dataset('cover image pages', [
    'post' => [CreatePost::class, 'posts'],
    'guide' => [CreateGuide::class, 'guides'],
    'episode' => [CreateEpisode::class, 'episodes'],
]);

test('cover uploads store on the public disk in the content type directory', function (string $page, string $directory): void {
    livewire($page)
        ->assertFormFieldExists('featured_image_path', fn (OptimizedImageUpload $field): bool => $field->getLabel() === 'Cover image'
            && $field->getDiskName() === 'public'
            && $field->getDirectory() === $directory);
})->with('cover image pages');

test('cover uploads accept only JPEG, PNG and WebP images up to 5 MB', function (string $page): void {
    livewire($page)
        ->assertFormFieldExists('featured_image_path', fn (OptimizedImageUpload $field): bool => $field->getAcceptedFileTypes() === ['image/jpeg', 'image/png', 'image/webp']
            && $field->getMaxSize() === 5120);
})->with('cover image pages');

test('cover uploads are cropped to the share ratio and resized', function (string $page): void {
    livewire($page)
        ->assertFormFieldExists('featured_image_path', fn (OptimizedImageUpload $field): bool => $field->getAutomaticallyCropImagesAspectRatio() === '1200:630'
            && $field->getAutomaticallyResizeImagesMode() === 'cover'
            && $field->getAutomaticallyResizeImagesWidth() === '1600'
            && $field->getAutomaticallyResizeImagesHeight() === '840');
})->with('cover image pages');

test('a cover upload that is not an image is rejected', function (string $page): void {
    Storage::fake('public');

    livewire($page)
        ->fillForm(['featured_image_path' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')])
        ->call('create')
        ->assertHasFormErrors(['featured_image_path']);
})->with('cover image pages');
