<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JeffreyDavidson\CreatorKit\Actions\ConfirmNewsletterSubscription;

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

        return redirect()->route('newsletter.confirmed');
    }
}
