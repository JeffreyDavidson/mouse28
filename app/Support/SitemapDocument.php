<?php

namespace App\Support;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;

class SitemapDocument
{
    public function content(): string
    {
        $posts = Post::published()->latest('published_at')->get();
        $episodes = Episode::published()->latest('published_at')->get();
        $guides = config('mouse28.guides_enabled')
            ? Guide::published()->latest('published_at')->get()
            : collect();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $staticRoutes = ['home', 'blog.index', 'episodes.index', 'about', 'contact.show'];

        if (config('mouse28.guides_enabled')) {
            $staticRoutes[] = 'guides.index';
        }

        foreach ($staticRoutes as $routeName) {
            $xml .= '<url><loc>'.route($routeName).'</loc><changefreq>weekly</changefreq><priority>'.($routeName === 'home' ? '1.0' : '0.8').'</priority></url>';
        }

        foreach ($posts as $post) {
            $xml .= '<url><loc>'.route('blog.show', $post).'</loc>';
            $xml .= '<lastmod>'.$post->updated_at->toW3cString().'</lastmod>';
            $xml .= '<changefreq>monthly</changefreq><priority>0.7</priority></url>';
        }

        foreach ($episodes as $episode) {
            $xml .= '<url><loc>'.route('episodes.show', $episode).'</loc>';
            $xml .= '<lastmod>'.$episode->updated_at->toW3cString().'</lastmod>';
            $xml .= '<changefreq>monthly</changefreq><priority>0.7</priority></url>';
        }

        foreach ($guides as $guide) {
            $xml .= '<url><loc>'.route('guides.show', $guide).'</loc>';
            $xml .= '<lastmod>'.$guide->updated_at->toW3cString().'</lastmod>';
            $xml .= '<changefreq>monthly</changefreq><priority>0.8</priority></url>';
        }

        return $xml.'</urlset>';
    }
}
