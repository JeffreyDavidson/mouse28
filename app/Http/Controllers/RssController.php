<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class RssController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::published()->latest('published_at')->take(20)->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>Mouse28 Blog</title>';
        $xml .= '<link>'.url('/blog').'</link>';
        $xml .= '<description>Disney parks through the eyes of a family raising a daughter with autism. Tips, accessibility guides, and stories.</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<atom:link href="'.url('/rss/blog').'" rel="self" type="application/rss+xml"/>';
        $xml .= '<image><url>'.url('/images/logo.jpg').'</url><title>Mouse28</title><link>'.url('/').'</link></image>';

        foreach ($posts as $post) {
            $xml .= '<item>';
            $xml .= '<title>'.htmlspecialchars($post->title).'</title>';
            $xml .= '<link>'.url("/blog/{$post->slug}").'</link>';
            $xml .= '<guid isPermaLink="true">'.url("/blog/{$post->slug}").'</guid>';
            $xml .= '<description>'.htmlspecialchars($post->excerpt ?? Str::limit(strip_tags($post->body), 300)).'</description>';
            $xml .= '<pubDate>'.$post->published_at->toRfc2822String().'</pubDate>';
            if ($post->category) {
                $xml .= '<category>'.htmlspecialchars($post->category_label).'</category>';
            }
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml']);
    }
}
