<?php

namespace App\Http\Controllers;

use App\Actions\UnsubscribeFromNewsletter;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsletterUnsubscriptionController
{
    public function create(Subscriber $subscriber): View
    {
        return view('pages.newsletter.unsubscribe', [
            'actionUrl' => url()->signedRoute('newsletter.unsubscribe.store', $subscriber),
            'subscriber' => $subscriber,
        ]);
    }

    public function destroy(Subscriber $subscriber, UnsubscribeFromNewsletter $unsubscribeFromNewsletter): RedirectResponse
    {
        $unsubscribeFromNewsletter->handle($subscriber);

        return redirect(route('home').'#newsletter')
            ->with('newsletter_success', 'You have been unsubscribed.');
    }
}
