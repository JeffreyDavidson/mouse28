<?php

namespace App\Actions;

use App\Models\NewsletterIssue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

class GenerateNewsletterRssFeed
{
    public function handle(): string
    {
        $issues = NewsletterIssue::published()
            ->latest('published_at')
            ->latest('id')
            ->take(Config::integer('mouse28.newsletter_feed_items'))
            ->get();

        $lastBuild = $issues->first()
            ?->published_at?->toRssString() ?? Date::now()->toRssString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
        $xml .= "<channel>\n";
        $xml .= "<title>Mouse28 Newsletter</title>\n";
        $xml .= '<link>'.$this->escape(route('newsletter.index'))."</link>\n";
        $xml .= "<description>Disney parks through the eyes of a family raising a daughter with autism. Notes and updates from Jeffrey and Cassie.</description>\n";
        $xml .= "<language>en-us</language>\n";
        $xml .= "<lastBuildDate>{$lastBuild}</lastBuildDate>\n";
        $xml .= '<atom:link href="'.$this->escape(route('newsletter.rss')).'" rel="self" type="application/rss+xml" />'."\n";

        foreach ($issues as $issue) {
            $link = route('newsletter.issue', $issue);

            $xml .= "<item>\n";
            $xml .= '<title>'.$this->escape($issue->title)."</title>\n";
            $xml .= '<link>'.$this->escape($link)."</link>\n";
            $xml .= '<guid isPermaLink="true">'.$this->escape($link)."</guid>\n";
            $xml .= '<description>'.$this->escape($issue->excerpt ?? '')."</description>\n";
            $xml .= '<pubDate>'.($issue->published_at?->toRssString() ?? $lastBuild)."</pubDate>\n";
            $xml .= "</item>\n";
        }

        return $xml."</channel>\n</rss>";
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }
}
