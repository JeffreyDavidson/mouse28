<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\ViewModels\SearchViewModel;
use Illuminate\View\View;

class SearchController
{
    public function __invoke(SearchRequest $request, SearchViewModel $viewModel): View
    {
        $query = $request->string('q')->trim()->toString();

        return view('search', $viewModel->data($query));
    }
}
