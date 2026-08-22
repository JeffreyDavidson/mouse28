<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Http\Response;

class PodcastRssController extends Controller
{
    public function __invoke(): Response
    {
        $podcast = Podcast::info();
        $episodes = Episode::published()->latest('published_at')->get();
        $coverImageUrl = $podcast->cover_image
            ? url('/storage/'.ltrim($podcast->cover_image, '/'))
            : url('/images/podcast/mouse28-cover.jpg');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>'.htmlspecialchars($podcast->name ?? 'Mouse28').'</title>';
        $xml .= '<link>'.url('/episodes').'</link>';
        $xml .= '<description>'.htmlspecialchars($podcast->description ?? 'Disney parks through the eyes of a family raising a daughter with autism.').'</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<atom:link href="'.url('/rss/podcast').'" rel="self" type="application/rss+xml"/>';
        $author = $this->xml((string) config('podcast.author'));
        $ownerName = $this->xml((string) config('podcast.owner_name'));
        $ownerEmail = $this->xml((string) ($podcast->email ?: config('podcast.owner_email')));
        $xml .= "<itunes:author>{$author}</itunes:author>";
        $xml .= "<itunes:owner><itunes:name>{$ownerName}</itunes:name><itunes:email>{$ownerEmail}</itunes:email></itunes:owner>";
        $xml .= '<itunes:category text="Society &amp; Culture"><itunes:category text="Documentary"/></itunes:category>';
        $xml .= '<itunes:category text="Kids &amp; Family"/>';
        $xml .= '<itunes:explicit>false</itunes:explicit>';
        $xml .= '<itunes:image href="'.$coverImageUrl.'"/>';
        $xml .= '<image><url>'.$coverImageUrl.'</url><title>Mouse28</title><link>'.url('/').'</link></image>';

        foreach ($episodes as $episode) {
            $xml .= '<item>';
            $xml .= '<title>'.htmlspecialchars($episode->title).'</title>';
            $xml .= '<link>'.url("/episodes/{$episode->slug}").'</link>';
            $xml .= '<guid isPermaLink="true">'.url("/episodes/{$episode->slug}").'</guid>';
            $xml .= '<description>'.htmlspecialchars($episode->description ?? '').'</description>';
            $xml .= '<pubDate>'.$episode->published_at->toRfc2822String().'</pubDate>';
            $xml .= '<itunes:episode>'.$episode->episode_number.'</itunes:episode>';
            if ($episode->season_number) {
                $xml .= '<itunes:season>'.$episode->season_number.'</itunes:season>';
            }
            if ($episode->duration_seconds) {
                $xml .= '<itunes:duration>'.$episode->duration_seconds.'</itunes:duration>';
            }
            if ($episode->audio_url) {
                $xml .= '<enclosure url="'.htmlspecialchars($episode->audio_url).'" type="audio/mpeg"/>';
            }
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml']);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
