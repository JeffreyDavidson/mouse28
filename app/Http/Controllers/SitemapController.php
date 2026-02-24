<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()->latest('published_at')->get();
        $episodes = Episode::published()->latest('published_at')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Static pages
        foreach (['/', '/blog', '/episodes', '/about', '/contact', '/guides', '/stories'] as $path) {
            $xml .= '<url><loc>' . url($path) . '</loc><changefreq>weekly</changefreq><priority>' . ($path === '/' ? '1.0' : '0.8') . '</priority></url>';
        }

        // Blog posts
        foreach ($posts as $post) {
            $xml .= '<url><loc>' . url("/blog/{$post->slug}") . '</loc>';
            $xml .= '<lastmod>' . $post->updated_at->toW3cString() . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq><priority>0.7</priority></url>';
        }

        // Episodes
        foreach ($episodes as $episode) {
            $xml .= '<url><loc>' . url("/episodes/{$episode->slug}") . '</loc>';
            $xml .= '<lastmod>' . $episode->updated_at->toW3cString() . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq><priority>0.7</priority></url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\n\nSitemap: " . url('/sitemap.xml');
        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
