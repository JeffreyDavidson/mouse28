<?php

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;

class CreateNewsletterIssue extends CreateRecord
{
    #[\Override]
    protected static string $resource = NewsletterIssueResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.newsletter-issues.form-header', [
            'title' => 'Create Issue',
            'subtitle' => 'Draft a new newsletter issue',
        ]);
    }
}
