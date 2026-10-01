<?php

namespace App\Support\Content;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

/**
 * Builds shareable preview links: a valid, unexpired signature is the only authorization.
 */
final class PreviewUrlGenerator
{
    public function for(Post|Guide|Episode|NewsletterIssue $content): string
    {
        $expiresAt = Date::now()->addHours(Config::integer('mouse28.preview_link_hours'));

        return match (true) {
            $content instanceof Post => URL::temporarySignedRoute('preview.post', $expiresAt, ['post' => $content]),
            $content instanceof Guide => URL::temporarySignedRoute('preview.guide', $expiresAt, ['guide' => $content]),
            $content instanceof Episode => URL::temporarySignedRoute('preview.episode', $expiresAt, ['episode' => $content]),
            $content instanceof NewsletterIssue => URL::temporarySignedRoute('preview.newsletter-issue', $expiresAt, ['newsletterIssue' => $content]),
        };
    }
}
