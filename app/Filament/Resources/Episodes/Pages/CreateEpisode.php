<?php

namespace App\Filament\Resources\Episodes\Pages;

use App\Filament\Resources\Episodes\EpisodeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;

class CreateEpisode extends CreateRecord
{
    protected static string $resource = EpisodeResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.episodes.form-header', [
            'title' => 'Create Episode',
            'subtitle' => 'Add a new podcast episode',
        ]);
    }
}
