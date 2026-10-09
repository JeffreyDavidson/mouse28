<?php

namespace App\Http\Controllers;

use App\Support\Feeds\SitemapRenderer;
use App\ViewModels\SitemapViewModel;
use Illuminate\Http\Response;

class SitemapController
{
    public function __invoke(SitemapViewModel $viewModel, SitemapRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()), 200, ['Content-Type' => 'application/xml']);
    }
}
