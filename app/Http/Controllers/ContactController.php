<?php

namespace App\Http\Controllers;

use App\Actions\SubmitContactMessage;
use App\Http\Requests\StoreContactRequest;
use App\Support\Turnstile;
use App\ViewModels\ContactViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContactController
{
    public function show(ContactViewModel $viewModel): View
    {
        return view('contact', $viewModel->data());
    }

    public function store(
        StoreContactRequest $request,
        Turnstile $turnstile,
        SubmitContactMessage $submitContactMessage,
    ): RedirectResponse {
        if ($request->filled('website_url')) {
            return redirect()->route('contact.show')->with('success', true);
        }

        if (! $turnstile->passes($request, config('services.turnstile.contact_action'))) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please verify that you are human and try again.',
            ])->errorBag('contact');
        }

        $submitContactMessage($request->messageAttributes());

        return redirect()->route('contact.show')->with('success', true);
    }
}
