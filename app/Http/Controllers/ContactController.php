<?php

namespace App\Http\Controllers;

use App\Actions\SendContactMessage;
use App\Http\Requests\StoreContactRequest;
use App\Services\TurnstileVerifier;
use App\ViewModels\ContactViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContactController
{
    public function create(ContactViewModel $viewModel): View
    {
        return view('pages.contact', $viewModel->data());
    }

    public function store(
        StoreContactRequest $request,
        TurnstileVerifier $turnstile,
        SendContactMessage $sendContactMessage,
    ): RedirectResponse {
        if ($request->filled('website')) {
            return redirect()->route('contact.create')->with('success', true);
        }

        if (! $turnstile->passes($request, Config::string('services.turnstile.contact_action'))) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please verify that you are human and try again.',
            ])->errorBag('contact');
        }

        $sendContactMessage->handle($request->toData());

        return redirect()->route('contact.create')->with('success', true);
    }
}
