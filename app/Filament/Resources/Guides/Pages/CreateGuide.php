<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guides\Pages;

use App\Filament\Resources\Guides\GuideResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;

class CreateGuide extends CreateRecord
{
    #[\Override]
    protected static string $resource = GuideResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.guides.form-header', [
            'title' => 'Create Guide',
            'subtitle' => 'Add a new accessibility guide',
        ]);
    }
}
