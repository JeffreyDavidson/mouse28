<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Support\Str;

class BlogRssFeed
{
    public function content(): string
    {
        $posts = Post::published()
            ->select(['id', 'slug', 'title', 'excerpt', 'body', 'published_at', 'category'])
            ->latest('published_at')
            ->take(20)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>Mouse28 Blog</title>';
        $xml .= '<link>'.route('blog.index').'</link>';
        $xml .= '<description>Disney parks through the eyes of a family raising a daughter with autism. Practical tips and stories.</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<atom:link href="'.route('rss.blog').'" rel="self" type="application/rss+xml"/>';
        $xml .= '<image><url>'.url('/images/logo.jpg').'</url><title>Mouse28</title><link>'.route('home').'</link></image>';

        foreach ($posts as $post) {
            $xml .= '<item>';
            $xml .= '<title>'.htmlspecialchars($post->title).'</title>';
            $xml .= '<link>'.route('blog.show', $post).'</link>';
            $xml .= '<guid isPermaLink="true">'.route('blog.show', $post).'</guid>';
            $xml .= '<description>'.htmlspecialchars($post->excerpt ?? Str::limit(strip_tags($post->body), 300)).'</description>';
            $xml .= '<pubDate>'.$post->published_at->toRfc2822String().'</pubDate>';

            if ($post->category) {
                $xml .= '<category>'.htmlspecialchars($post->category_label).'</category>';
            }

            $xml .= '</item>';
        }

        return $xml.'</channel></rss>';
    }
}
