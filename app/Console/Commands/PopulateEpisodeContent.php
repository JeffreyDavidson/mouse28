<?php

namespace App\Console\Commands;

use App\Models\Episode;
use Illuminate\Console\Command;

class PopulateEpisodeContent extends Command
{
    protected $signature = 'episodes:populate-content';
    protected $description = 'Populate Episode 1 with rich show notes and transcript';

    public function handle(): void
    {
        $episode = Episode::where('slug', 'welcome-to-mouse28')->first();

        if (!$episode) {
            $this->error('Episode not found.');
            return;
        }

        $episode->update([
            'show_notes' => $this->getShowNotes(),
            'transcript' => $this->getTranscript(),
        ]);

        $this->info('Episode 1 content updated!');
    }

    private function getShowNotes(): string
    {
        return <<<'HTML'
<h2>What We Cover</h2>
<p>This is our very first episode, and we wanted to start by introducing ourselves and sharing the story behind Mouse28. We talk about our family, how Disney became such a central part of our lives, and what you can expect from this podcast.</p>

<h3>Topics in This Episode</h3>
<ul>
<li><strong>Meet the Davidsons</strong> &mdash; Jeffrey, Cassie, and our daughter Viola</li>
<li><strong>Why &ldquo;Mouse28&rdquo;?</strong> &mdash; The name comes from Mickey&rsquo;s 1928 debut in <em>Steamboat Willie</em></li>
<li><strong>Our weekly Disney habit</strong> &mdash; Living 20 minutes from the parks changed everything</li>
<li><strong>Viola&rsquo;s story</strong> &mdash; How autism shapes our Disney experience (and makes it better)</li>
<li><strong>The DAS pass</strong> &mdash; A quick intro to what it is and why it matters</li>
<li><strong>Our favorite park</strong> &mdash; Spoiler: it might surprise you</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://disneyworld.disney.go.com/guest-services/disability-access-service/">Disney&rsquo;s DAS Pass Info</a></li>
<li><a href="https://www.autismspeaks.org/">Autism Speaks</a> &mdash; Resource hub for families</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li><strong>0:00</strong> &mdash; Intro &amp; welcome</li>
<li><strong>2:15</strong> &mdash; Meet the family</li>
<li><strong>8:30</strong> &mdash; How we became weekly Disney visitors</li>
<li><strong>15:45</strong> &mdash; Viola&rsquo;s story and our autism journey</li>
<li><strong>24:10</strong> &mdash; What is the DAS pass?</li>
<li><strong>31:00</strong> &mdash; Our favorite park reveal</li>
<li><strong>36:20</strong> &mdash; What to expect from Mouse28</li>
<li><strong>38:00</strong> &mdash; Wrap up</li>
</ul>

<blockquote><p>&ldquo;Every family experiences Disney differently. Ours just happens to include a lot more planning, a lot more patience, and honestly? A lot more magic because of it.&rdquo;</p></blockquote>
HTML;
    }

    private function getTranscript(): string
    {
        return <<<'HTML'
<p><strong>Jeffrey:</strong> Hey everybody, welcome to Mouse28. I&rsquo;m Jeffrey.</p>
<p><strong>Cassie:</strong> And I&rsquo;m Cassie.</p>
<p><strong>Jeffrey:</strong> And this is our very first episode. We are so excited to finally be doing this. We&rsquo;ve been talking about starting a podcast for &mdash; how long has it been?</p>
<p><strong>Cassie:</strong> Like a year? At least a year. Every time we&rsquo;d leave a park, one of us would say &ldquo;we should really be recording this.&rdquo;</p>
<p><strong>Jeffrey:</strong> Exactly. So here we are. Mouse28 &mdash; our Disney podcast. And the name, if you&rsquo;re wondering, comes from 1928, which is the year Mickey Mouse first appeared in <em>Steamboat Willie</em>.</p>
<p><strong>Cassie:</strong> Which Viola loves, by the way. She watches that short on repeat.</p>
<p><strong>Jeffrey:</strong> She really does. So for those who don&rsquo;t know us, we&rsquo;re the Davidsons. We live in Davenport, Florida, which is about twenty minutes from Walt Disney World. And we go to the parks basically every single week.</p>
<p><strong>Cassie:</strong> Sometimes twice a week if we&rsquo;re being honest.</p>
<p><strong>Jeffrey:</strong> <em>(laughing)</em> Yeah, sometimes twice. And we have a daughter named Viola, she&rsquo;s eight years old, and she is autistic and nonverbal. And that really shapes how we experience Disney in a huge way.</p>
<p><strong>Cassie:</strong> It shapes everything, really. But Disney especially. Because there&rsquo;s so much sensory input at the parks &mdash; the sounds, the crowds, the lights &mdash; and we&rsquo;ve learned over the years how to navigate all of that in a way that works for our family.</p>
<p><strong>Jeffrey:</strong> And that&rsquo;s really what this podcast is about. It&rsquo;s not just a Disney fan podcast &mdash; although we are absolutely Disney fans &mdash; it&rsquo;s about experiencing Disney through the lens of a family with a special needs child. The planning, the DAS pass, the quiet spots, the meltdowns in Fantasyland&hellip;</p>
<p><strong>Cassie:</strong> <em>(laughing)</em> We&rsquo;ve had a few of those.</p>
<p><strong>Jeffrey:</strong> We&rsquo;ve had more than a few. And we want to share all of it &mdash; the good, the hard, and the magical. Because there are so many families out there who might be thinking &ldquo;can we even do Disney?&rdquo; And the answer is absolutely yes.</p>
<p><strong>Cassie:</strong> One hundred percent yes. It just takes some extra planning and knowing what to expect. And that&rsquo;s what we&rsquo;re here to help with.</p>
<p><strong>Jeffrey:</strong> So in upcoming episodes, we&rsquo;re going to dive deep into the DAS pass, sensory-friendly spots at each park, our favorite rides ranked by how sensory-intense they are, character interactions that work for kids with sensory sensitivities &mdash; all of it.</p>
<p><strong>Cassie:</strong> And food! We&rsquo;ll definitely talk about food.</p>
<p><strong>Jeffrey:</strong> Oh, we will absolutely talk about food. Cassie has opinions about every single restaurant on property.</p>
<p><strong>Cassie:</strong> I do. And I&rsquo;m right about all of them.</p>
<p><strong>Jeffrey:</strong> <em>(laughing)</em> She usually is. Alright, so that&rsquo;s what you can expect from us. New episodes every week. We&rsquo;re the Davidsons, this is Mouse28, and we&rsquo;ll see you real soon.</p>
<p><strong>Cassie:</strong> See ya real soon!</p>
HTML;
    }
}
