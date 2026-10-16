<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\ViewModels\ContactViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use JeffreyDavidson\CreatorKit\Actions\SendContactMessage;

class ContactController
{
    public function create(ContactViewModel $viewModel): View
    {
        return view('pages.contact', $viewModel->data());
    }

    public function store(StoreContactRequest $request, SendContactMessage $sendContactMessage): RedirectResponse
    {
        $sendContactMessage->handle($request->toData()
            ->toAttributes());

        return redirect()->route('contact.create')
            ->with('success', true);
    }
}
