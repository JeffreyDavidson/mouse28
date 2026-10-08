<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\SocialProfile;
use Illuminate\View\View;

class SocialProfilesComposer
{
    public function compose(View $view): void
    {
        $view->with('footerSocialProfiles', SocialProfile::query()
            ->forFooter()
            ->get());
    }
}
