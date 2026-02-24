<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Http\Response;

class RssController extends Controller
{
    public function blog(): Response
    {
        $posts = Post::published()->latest('published_at')->take(20)->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>Mouse28 Blog</title>';
        $xml .= '<link>' . url('/blog') . '</link>';
        $xml .= '<description>Disney parks through the eyes of a family raising a daughter with autism. Tips, accessibility guides, and stories.</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<atom:link href="' . url('/rss/blog') . '" rel="self" type="application/rss+xml"/>';
        $xml .= '<image><url>' . url('/images/logo.jpg') . '</url><title>Mouse28</title><link>' . url('/') . '</link></image>';

        foreach ($posts as $post) {
            $xml .= '<item>';
            $xml .= '<title>' . htmlspecialchars($post->title) . '</title>';
            $xml .= '<link>' . url("/blog/{$post->slug}") . '</link>';
            $xml .= '<guid isPermaLink="true">' . url("/blog/{$post->slug}") . '</guid>';
            $xml .= '<description>' . htmlspecialchars($post->excerpt ?? Str::limit(strip_tags($post->body), 300)) . '</description>';
            $xml .= '<pubDate>' . $post->published_at->toRfc2822String() . '</pubDate>';
            if ($post->category) {
                $xml .= '<category>' . htmlspecialchars($post->category_label) . '</category>';
            }
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml']);
    }

    public function podcast(): Response
    {
        $podcast = Podcast::info();
        $episodes = Episode::published()->latest('published_at')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>' . htmlspecialchars($podcast->name ?? 'Mouse28') . '</title>';
        $xml .= '<link>' . url('/episodes') . '</link>';
        $xml .= '<description>' . htmlspecialchars($podcast->description ?? 'Disney parks through the eyes of a family raising a daughter with autism.') . '</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<atom:link href="' . url('/rss/podcast') . '" rel="self" type="application/rss+xml"/>';
        $xml .= '<itunes:author>Jeffrey &amp; Cassie Davidson</itunes:author>';
        $xml .= '<itunes:owner><itunes:name>Jeffrey Davidson</itunes:name><itunes:email>jdavidsonwebdev@gmail.com</itunes:email></itunes:owner>';
        $xml .= '<itunes:category text="Society &amp; Culture"><itunes:category text="Documentary"/></itunes:category>';
        $xml .= '<itunes:category text="Kids &amp; Family"/>';
        $xml .= '<itunes:explicit>false</itunes:explicit>';
        $xml .= '<itunes:image href="' . url('/images/logo.jpg') . '"/>';
        $xml .= '<image><url>' . url('/images/logo.jpg') . '</url><title>Mouse28</title><link>' . url('/') . '</link></image>';

        foreach ($episodes as $episode) {
            $xml .= '<item>';
            $xml .= '<title>' . htmlspecialchars($episode->title) . '</title>';
            $xml .= '<link>' . url("/episodes/{$episode->slug}") . '</link>';
            $xml .= '<guid isPermaLink="true">' . url("/episodes/{$episode->slug}") . '</guid>';
            $xml .= '<description>' . htmlspecialchars($episode->description ?? '') . '</description>';
            $xml .= '<pubDate>' . $episode->published_at->toRfc2822String() . '</pubDate>';
            $xml .= '<itunes:episode>' . $episode->episode_number . '</itunes:episode>';
            if ($episode->season_number) {
                $xml .= '<itunes:season>' . $episode->season_number . '</itunes:season>';
            }
            if ($episode->duration_seconds) {
                $xml .= '<itunes:duration>' . $episode->duration_seconds . '</itunes:duration>';
            }
            if ($episode->audio_url) {
                $xml .= '<enclosure url="' . htmlspecialchars($episode->audio_url) . '" type="audio/mpeg"/>';
            }
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml']);
    }
}
