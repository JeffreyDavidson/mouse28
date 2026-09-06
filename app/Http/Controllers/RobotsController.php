<?php

namespace App\Http\Controllers;

use App\Support\RobotsDocument;
use Illuminate\Http\Response;

class RobotsController
{
    public function __invoke(RobotsDocument $robots): Response
    {
        return response($robots->content(), 200, ['Content-Type' => 'text/plain']);
    }
}
