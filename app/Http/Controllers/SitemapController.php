<?php

namespace App\Http\Controllers;

use App\ViewModels\SitemapViewModel;
use Illuminate\Http\Response;
use JeffreyDavidson\CreatorKit\Support\Feeds\SitemapRenderer;

class SitemapController
{
    public function __invoke(SitemapViewModel $viewModel, SitemapRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()), 200, ['Content-Type' => 'application/xml']);
    }
}
