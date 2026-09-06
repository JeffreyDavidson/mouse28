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

        $staticPaths = ['/', '/blog', '/episodes', '/about', '/contact'];

        if (config('mouse28.guides_enabled')) {
            $staticPaths[] = '/guides';
        }

        foreach ($staticPaths as $path) {
            $xml .= '<url><loc>'.url($path).'</loc><changefreq>weekly</changefreq><priority>'.($path === '/' ? '1.0' : '0.8').'</priority></url>';
        }

        foreach ($posts as $post) {
            $xml .= '<url><loc>'.url("/blog/{$post->slug}").'</loc>';
            $xml .= '<lastmod>'.$post->updated_at->toW3cString().'</lastmod>';
            $xml .= '<changefreq>monthly</changefreq><priority>0.7</priority></url>';
        }

        foreach ($episodes as $episode) {
            $xml .= '<url><loc>'.url("/episodes/{$episode->slug}").'</loc>';
            $xml .= '<lastmod>'.$episode->updated_at->toW3cString().'</lastmod>';
            $xml .= '<changefreq>monthly</changefreq><priority>0.7</priority></url>';
        }

        foreach ($guides as $guide) {
            $xml .= '<url><loc>'.url("/guides/{$guide->slug}").'</loc>';
            $xml .= '<lastmod>'.$guide->updated_at->toW3cString().'</lastmod>';
            $xml .= '<changefreq>monthly</changefreq><priority>0.8</priority></url>';
        }

        return $xml.'</urlset>';
    }
}
