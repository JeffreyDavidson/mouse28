<?php

use App\Models\ContactMessage;
use App\Models\User;
use App\Policies\ContactMessagePolicy;

covers(ContactMessagePolicy::class);

test('contact message editorial abilities are limited to administrators', function (): void {
    $policy = new ContactMessagePolicy;
    $message = new ContactMessage;
    $admin = new User;
    $admin->setRawAttributes(['is_admin' => true]);
    $visitor = new User;
    $visitor->setRawAttributes(['is_admin' => false]);

    $adminResults = [
        $policy->viewAny($admin),
        $policy->view($admin, $message),
        $policy->update($admin, $message),
        $policy->delete($admin, $message),
        $policy->deleteAny($admin),
    ];
    $visitorResults = [
        $policy->viewAny($visitor),
        $policy->view($visitor, $message),
        $policy->update($visitor, $message),
        $policy->delete($visitor, $message),
        $policy->deleteAny($visitor),
    ];

    expect($adminResults)->each->toBeTrue()
        ->and($visitorResults)->each->toBeFalse();
});
