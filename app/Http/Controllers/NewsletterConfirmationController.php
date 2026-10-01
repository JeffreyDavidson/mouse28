<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterConfirmationController
{
    public function create(Request $request, Subscriber $subscriber): View
    {
        return view('pages.newsletter.confirm', [
            'actionUrl' => $request->fullUrl(),
            'subscriber' => $subscriber,
        ]);
    }

    public function store(Subscriber $subscriber, ConfirmNewsletterSubscription $confirmNewsletterSubscription): RedirectResponse
    {
        $confirmNewsletterSubscription->handle($subscriber);

        return redirect(route('home').'#newsletter')
            ->with('newsletter_success', 'You\'re subscribed. Thanks for confirming!');
    }
}
