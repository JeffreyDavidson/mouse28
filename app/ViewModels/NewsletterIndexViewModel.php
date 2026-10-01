<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\NewsletterIssue;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

class NewsletterIndexViewModel
{
    /**
     * @return array{
     *     issues: LengthAwarePaginator<int, NewsletterIssue>,
     *     pageTitle: string,
     *     pageDescription: string,
     *     canonicalUrl: string
     * }
     */
    public function data(Request $request): array
    {
        $issues = NewsletterIssue::query()
            ->published()
            ->latest('published_at')
            ->latest('id')
            ->paginate(Config::integer('mouse28.newsletter_issues_per_page'))
            ->withQueryString();

        abort_if($issues->currentPage() > $issues->lastPage(), 404);

        $onFirstPage = $issues->onFirstPage();

        return [
            'issues' => $issues,
            'pageTitle' => $onFirstPage ? 'Newsletter Archive | Mouse28' : "Newsletter Archive — Page {$issues->currentPage()} | Mouse28",
            'pageDescription' => 'Every Mouse28 newsletter issue: Disney park accessibility notes, family experiences, and practical planning from Jeffrey and Cassie Davidson.',
            'canonicalUrl' => route('newsletter.index', $onFirstPage ? [] : ['page' => $issues->currentPage()]),
        ];
    }
}
