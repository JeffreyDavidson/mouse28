<?php

namespace App\Filament\Resources\AssetReviews\Schemas;

use App\Models\AssetReview;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AssetReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.resources.asset-reviews.previews')
                ->viewData(fn (?AssetReview $record): array => ['review' => $record])
                ->visible(fn (?AssetReview $record): bool => $record instanceof AssetReview)
                ->columnSpanFull(),
            Section::make('Review set')
                ->description('Save your edits before reviewing. Changing the artwork, title, version or source article clears the previous approval.')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('version')
                        ->required()
                        ->maxLength(255)
                        ->default('v1'),
                    Select::make('source_post_id')
                        ->label('Source article')
                        ->relationship('sourcePost', 'title')
                        ->searchable()
                        ->preload(),
                    FileUpload::make('files')
                        ->label('Review images')
                        ->helperText('Private PNG, JPEG or WebP files. Reorder them to match the reading order; open saved previews above to read at full size.')
                        ->required()
                        ->image()
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                        ->maxSize(Config::integer('asset-reviews.max_file_size_kb'))
                        ->maxFiles(Config::integer('asset-reviews.max_files'))
                        ->multiple()
                        ->reorderable()
                        ->appendFiles()
                        ->disk('local')
                        ->directory('asset-reviews')
                        ->visibility('private')
                        ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => Str::ulid().'.'.$file->guessExtension())
                        ->previewable(false)
                        ->preventFilePathTampering()
                        ->getUploadedFileUsing(function (string $file, ?AssetReview $record): ?array {
                            if (! $record instanceof AssetReview) {
                                return null;
                            }

                            $index = array_search($file, $record->files, true);

                            if (! is_int($index) || $record->filePath($index) !== $file) {
                                return null;
                            }

                            return [
                                'name' => basename($file),
                                'size' => Storage::disk('local')
                                    ->size($file),
                                'type' => Storage::disk('local')
                                    ->mimeType($file),
                                // Use the authenticated saved-file route, never a disk temporary URL.
                                'url' => route('filament.admin.assetReviews.file', [
                                    'assetReview' => $record,
                                    'file' => $index,
                                ]),
                            ];
                        }),
                ])
                ->columnSpanFull(),
        ]);
    }
}
