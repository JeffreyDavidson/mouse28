<?php

namespace App\Http\Controllers;

use App\Actions\SendContactMessage;
use App\Http\Requests\StoreContactRequest;
use App\ViewModels\ContactViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController
{
    public function create(ContactViewModel $viewModel): View
    {
        return view('pages.contact', $viewModel->data());
    }

    public function store(StoreContactRequest $request, SendContactMessage $sendContactMessage): RedirectResponse
    {
        $sendContactMessage->handle($request->toData());

        return redirect()->route('contact.create')
            ->with('success', true);
    }
}
