<?php

namespace App\Http\Controllers;

use App\ViewModels\HomeViewModel;
use Illuminate\View\View;

class HomeController
{
    public function index(HomeViewModel $viewModel): View
    {
        return view('pages.home', $viewModel->data());
    }
}
