<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Jeffrey Davidson',
            'email' => 'thelaravelarchitect@gmail.com',
        ]);

        // Podcast metadata
        Podcast::firstOrCreate(['id' => 1], [
            'name' => 'Mouse28',
            'description' => 'Disney parks through the eyes of a family raising a daughter with autism. Hosted by Jeffrey & Cassie Davidson.',
            'apple_url' => null,
            'spotify_url' => null,
            'youtube_url' => null,
            'instagram_url' => null,
            'tiktok_url' => null,
        ]);

        // Episodes
        $episodes = [
            [
                'title' => 'Welcome to Mouse28',
                'slug' => 'welcome-to-mouse28',
                'description' => "Our very first episode! We introduce ourselves, share why we started Mouse28, and talk about what it's like visiting Disney World every single week with our daughter Viola.",
                'show_notes' => "In this episode:\n- Who are the Davidsons?\n- How we became weekly Disney visitors\n- Viola's story and how autism shapes our Disney experience\n- What to expect from Mouse28\n- Our favorite park (spoiler: it might surprise you)",
                'episode_number' => 1,
                'season_number' => 1,
                'duration_seconds' => 2340,
                'is_published' => true,
                'published_at' => now()->subDays(21),
            ],
            [
                'title' => 'The DAS Pass: Everything You Need to Know',
                'slug' => 'das-pass-everything-you-need-to-know',
                'description' => "A deep dive into Disney's Disability Access Service pass — how to apply, what to expect, and our honest review after 3+ years of using it.",
                'show_notes' => "In this episode:\n- What is the DAS pass?\n- The new 2024 application process\n- Tips for your DAS interview\n- How we plan our park days around DAS\n- Common misconceptions debunked",
                'episode_number' => 2,
                'season_number' => 1,
                'duration_seconds' => 3120,
                'is_published' => true,
                'published_at' => now()->subDays(14),
            ],
            [
                'title' => 'Sensory-Friendly Spots at Magic Kingdom',
                'slug' => 'sensory-friendly-spots-magic-kingdom',
                'description' => "Magic Kingdom can be overwhelming for sensory-sensitive kids (and adults!). We share our tried-and-true quiet spots, calm rides, and decompression strategies.",
                'show_notes' => "In this episode:\n- The best quiet spots in each land\n- Rides ranked by sensory intensity\n- Our secret decompression routine\n- Cast member interactions that surprised us\n- Viola's favorite Magic Kingdom moments",
                'episode_number' => 3,
                'season_number' => 1,
                'duration_seconds' => 2760,
                'is_published' => true,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'EPCOT With Kids Who Think Differently',
                'slug' => 'epcot-with-kids-who-think-differently',
                'description' => "EPCOT is secretly the best park for neurodiverse kids. We break down why World Showcase is a sensory paradise and which Future World rides are worth the wait.",
                'show_notes' => "Coming soon...",
                'episode_number' => 4,
                'season_number' => 1,
                'duration_seconds' => 2580,
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($episodes as $ep) {
            Episode::create($ep);
        }

        // Blog posts
        $posts = [
            [
                'title' => '10 Quiet Spots at Disney World When Your Kid Needs a Break',
                'slug' => '10-quiet-spots-disney-world-kid-needs-break',
                'excerpt' => "Overstimulated? Meltdown incoming? Here are our go-to decompression spots at all four parks — tested by a family who visits every single week.",
                'body' => "<p>If you have a child who gets overstimulated, you know the signs. The hand-flapping gets faster. The covering of ears. The look in their eyes that says \"I need out of here NOW.\"</p><p>After three years of weekly Disney visits with our daughter Viola, we've mapped out the best quiet spots at every park. These aren't just \"less crowded areas\" — these are places where the noise drops, the crowds thin, and your kid can actually breathe.</p><h2>Magic Kingdom</h2><p><strong>1. Tom Sawyer Island</strong> — This is our #1 pick. You take a raft over and suddenly the noise just... disappears. Viola can explore at her own pace with zero crowd pressure.</p><p><strong>2. The back of Tomorrowland near the Speedway</strong> — Most people rush to Space Mountain. Walk past the Speedway and there's a quiet stretch with benches and shade.</p><p><strong>3. The garden behind the castle</strong> — Not many people know about this. Walk through the castle and look for the garden area to the right. It's a peaceful pocket.</p><h2>EPCOT</h2><p><strong>4. Japan Pavilion gardens</strong> — The koi pond area is genuinely calming. Viola will sit and watch the fish for 20 minutes straight.</p><p><strong>5. Morocco Pavilion inner courtyard</strong> — The most underrated quiet spot in all of Disney World. Beautifully tiled, shaded, and almost always empty.</p><h2>Hollywood Studios</h2><p><strong>6. Animation Courtyard</strong> — When it's not a character meet time, this area empties out completely.</p><h2>Animal Kingdom</h2><p><strong>7. The Maharajah Jungle Trek</strong> — A self-paced walking trail. No time limits, no crowds pushing behind you. Viola loves the bat enclosure.</p><p><strong>8. Discovery Island trails</strong> — Most people walk right past these to get to rides. The trails are shaded and quiet.</p><h2>Pro Tips</h2><p><strong>9. First Aid stations</strong> — Every park has one. They have quiet rooms specifically for situations like this. Don't be afraid to ask.</p><p><strong>10. Baby Care Centers</strong> — Not just for babies! These have quiet rooms with dim lighting. We've used them countless times.</p><p>The most important thing? Know your kid's limits and leave BEFORE the meltdown, not during. We've learned this the hard way more times than we'd like to admit.</p>",
                'category' => 'park-accessibility',
                'author' => 'both',
                'episode_id' => 3,
                'is_published' => true,
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'How We Applied for the DAS Pass (And What We Wish We Knew)',
                'slug' => 'how-we-applied-das-pass-what-we-wish-we-knew',
                'excerpt' => "The DAS application process changed in 2024. Here's our experience applying, what questions they asked, and the tips that actually helped.",
                'body' => "<p>The DAS (Disability Access Service) pass has been a game-changer for our family. But the application process? It can be anxiety-inducing, especially with the 2024 changes.</p><p>Here's what we wish someone had told us before we applied.</p><h2>What is DAS?</h2><p>DAS is Disney's accommodation for guests who can't wait in conventional queues due to a developmental disability. It essentially lets you \"wait\" for a ride without physically standing in line.</p><h2>The Application</h2><p>As of 2024, you apply via a video call before your trip. Here's what to expect:</p><ul><li>The call takes 15-30 minutes</li><li>They'll ask about your child's specific challenges with queuing</li><li>Be honest and specific — \"crowds cause sensory overload leading to meltdowns\" is better than \"my kid doesn't like waiting\"</li><li>You don't need a doctor's note, but be prepared to explain the diagnosis</li></ul><h2>What Actually Helped</h2><p>Before the call, we wrote down specific examples of what happens when Viola is in a queue too long. Not clinical terms — real moments. \"She starts hitting herself.\" \"She drops to the ground and can't be moved.\" \"She runs.\"</p><p>The cast member was incredibly understanding. They approved us within 20 minutes.</p><h2>How We Use It</h2><p>We've developed a system: check wait times, book a DAS return for the longest-wait ride, then do shorter-wait rides while we wait. It's not \"skip the line\" — it's \"wait somewhere else.\"</p><p>Game-changer? Absolutely. Perfect system? No. But it makes Disney accessible for our family in a way that nothing else could.</p>",
                'category' => 'disney-tips',
                'author' => 'jeffrey',
                'episode_id' => 2,
                'is_published' => true,
                'published_at' => now()->subDays(13),
            ],
            [
                'title' => "Why We Go to Disney Every Week (No, We're Not Crazy)",
                'slug' => 'why-we-go-disney-every-week',
                'excerpt' => "People think we're nuts. 52 weeks a year, we're at Disney World. Here's why it's the best decision we ever made for our family.",
                'body' => "<p>\"You go EVERY week?\" We get this a lot. And yes, we do. Here's why.</p><h2>Routine is Everything</h2><p>For Viola, routine isn't a preference — it's a necessity. Autism means her brain craves predictability. And Disney? Disney is the most predictable place on Earth. The rides do the same thing every time. The characters are in the same spots. The music plays the same songs.</p><p>For most people, that repetition would get boring. For Viola, it's safety.</p><h2>Annual Passes Make It Affordable</h2><p>People assume weekly Disney trips mean we're wealthy. We're not. We have annual passes, and since we live 30 minutes away, there's no hotel cost. A typical visit costs us maybe $30-50 in food (we eat before we go most of the time).</p><p>Compare that to any other family activity — movie theater, bowling, trampoline park — and Disney is actually competitive.</p><h2>It's Our Family Time</h2><p>We both work from home. Life gets busy. Disney forces us to be present with each other. No screens (well, except for Lightning Lane), no chores, just family time in a place that makes all three of us happy.</p><h2>Viola Thrives There</h2><p>This is the real reason. Viola is different at Disney. She's more engaged, more communicative (in her way), more joyful. The cast members know her by name. She has her routines — the fountain at EPCOT, the fish at Nemo, the teacups at Magic Kingdom.</p><p>Disney is where Viola is most herself. And honestly? That's worth any price.</p>",
                'category' => 'family-life',
                'author' => 'jeffrey',
                'is_published' => true,
                'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'Understanding Autism at Disney: What Other Families Should Know',
                'slug' => 'understanding-autism-disney-what-families-should-know',
                'excerpt' => "If you're bringing a child with autism to Disney for the first time — or if you just want to understand the families around you better — this is for you.",
                'body' => "<p>April is Autism Awareness Month, and we wanted to write something for two audiences: families who are bringing a child with autism to Disney, and families who want to be better allies to the neurodiverse families around them at the parks.</p><h2>For Autism Families: What We've Learned</h2><ul><li><strong>Start small.</strong> Don't try to do a full park day your first visit. We started with 2-hour visits and built up.</li><li><strong>Bring noise-canceling headphones.</strong> Non-negotiable for us.</li><li><strong>Learn the quiet spots.</strong> (We wrote a whole post about this!)</li><li><strong>Use visual schedules.</strong> We show Viola pictures of where we're going next.</li><li><strong>Don't force character meets.</strong> Some days Viola wants to hug Mickey. Some days she screams. Both are okay.</li></ul><h2>For Everyone Else: How to Be a Good Neighbor</h2><ul><li><strong>Don't stare.</strong> Yes, that kid is flapping their hands. Yes, that teenager has a lanyard full of pins and is talking to themselves. They're having a great time.</li><li><strong>Be patient in DAS return lines.</strong> Those families aren't \"cutting.\" They're using an accommodation they had to fight for.</li><li><strong>Smile at the parents.</strong> You have no idea how much a sympathetic smile means when your kid is having a meltdown in Fantasyland.</li></ul><p>Disney calls itself the most magical place on Earth. For families like ours, it really can be — when the world around us makes room.</p>",
                'category' => 'autism-awareness',
                'author' => 'both',
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Our Top 5 Character Interactions for Sensory-Sensitive Kids',
                'slug' => 'top-5-character-interactions-sensory-sensitive-kids',
                'excerpt' => "Not all character meets are created equal. Here are the ones that consistently work for Viola — and why.",
                'body' => "<p>Character meets can be magical or disastrous for sensory-sensitive kids. After hundreds of interactions (literally), here are the five that consistently work for us.</p><h2>1. Winnie the Pooh (Magic Kingdom)</h2><p>Pooh is gentle, slow-moving, and patient. He'll wait for your child to approach on their own terms. We've had interactions where Viola just stood six feet away and waved, and Pooh waved back for a full minute. No pressure.</p><h2>2. Baymax (EPCOT)</h2><p>Baymax's costume is designed for hugs — he's soft and round. The interaction area is usually quieter than other meets. And something about that big white shape is incredibly calming for Viola.</p><h2>3. Mickey at Town Square Theater (Magic Kingdom)</h2><p>The cast members at this location are specifically trained for accessibility interactions. They'll adjust the experience — dimmer photo flash, more time, quieter voice. Just let them know at the door.</p><h2>4. Chip & Dale (Various)</h2><p>These guys are hilarious and they read the room. If your kid is shy, they'll do silly dances from a distance. If your kid is engaged, they'll get up close. Viola LOVES them.</p><h2>5. Stitch (Magic Kingdom)</h2><p>Stitch is mischievous and playful. He communicates a lot through gestures and sounds rather than words, which actually works great for nonverbal kids. Viola and Stitch have a thing.</p><h2>Tips for Character Meets</h2><ul><li>Go at rope drop — shorter waits, calmer environment</li><li>Let cast members know about your child's needs</li><li>Don't force photos — the interaction matters more</li><li>Have an exit strategy if it's not working</li></ul>",
                'category' => 'disney-tips',
                'author' => 'cassie',
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => "Recap: EPCOT With Kids Who Think Differently (Ep. 4)",
                'slug' => 'recap-epcot-kids-think-differently-ep4',
                'excerpt' => "Show notes and expanded thoughts from our latest episode about why EPCOT might be the best park for neurodiverse families.",
                'body' => "<p>In our latest episode, we dove into why EPCOT has become our favorite park for Viola. Here's the expanded version with all the details we couldn't fit in the show.</p><h2>Why EPCOT Works</h2><p>EPCOT is more spread out than any other park. That alone makes a huge difference for sensory management. But there's more to it:</p><ul><li><strong>World Showcase is a sensory paradise</strong> — Different sights, sounds, and smells in each pavilion, but the transitions are gradual, not jarring.</li><li><strong>Fewer sudden loud noises</strong> — Compared to Magic Kingdom's fireworks cannon and Hollywood Studios' action shows, EPCOT is relatively gentle.</li><li><strong>Water everywhere</strong> — Viola is obsessed with water. The fountains, the lagoon, the aquarium at The Seas... she could spend all day.</li></ul><h2>Our EPCOT Routine</h2><p>We've dialed in a routine that works:</p><ol><li>Arrive at park open, head straight to The Seas with Nemo (aquarium time)</li><li>Ride Frozen Ever After while crowds are still thin</li><li>Walk World Showcase counter-clockwise (less crowded direction)</li><li>Lunch in Japan (Viola loves the rice)</li><li>End at the fountain near the entrance for decompression</li></ol><p>Listen to the full episode for our ride-by-ride sensory ratings and the story about Viola's first time on Remy's Ratatouille Adventure (spoiler: she loved it).</p>",
                'category' => 'episode-recap',
                'author' => 'both',
                'episode_id' => 4,
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'The Meltdown in Fantasyland: Why We Share the Hard Parts',
                'slug' => 'meltdown-in-fantasyland-why-we-share-hard-parts',
                'excerpt' => "It's not all pixie dust. Last week Viola had a full meltdown near It's a Small World. Here's what happened and why we think sharing matters.",
                'body' => "<p>We post a lot of magical Disney moments. But last week wasn't one of them.</p><p>We were near It's a Small World when Viola went from fine to full meltdown in about 30 seconds. She dropped to the ground, started screaming, and refused to move. In the middle of the walkway. At 2 PM. On a Saturday.</p><h2>What Triggered It</h2><p>Honestly? We're not sure. That's the thing about autism — sometimes there's no obvious trigger. It might have been the cumulative noise. It might have been a transition she wasn't ready for. It might have been something we'll never identify.</p><h2>What We Did</h2><p>Jeffrey sat on the ground next to her. Not trying to move her, not trying to reason with her, just... being there. Cassie ran interference — politely waving people around us, giving Viola space.</p><p>It lasted about 15 minutes. Then Viola stood up, grabbed Jeffrey's hand, and walked to the teacups. Like nothing happened.</p><h2>Why We Share This</h2><p>Because other autism families need to see that this happens to everyone. That you can go to Disney 50 times a year and still have a day where nothing goes according to plan.</p><p>And because the non-autism families need to see it too. That family you walked past, the one with the kid screaming on the ground? They're not bad parents. They're not failing. They're doing the hardest thing they've ever done, in public, while strangers watch.</p><p>A smile goes a long way. Judgment doesn't go anywhere good.</p>",
                'category' => 'family-life',
                'author' => 'cassie',
                'is_published' => true,
                'published_at' => now()->subDays(17),
            ],
            [
                'title' => 'Best Disney Snacks for Kids with Sensory Food Issues',
                'slug' => 'best-disney-snacks-sensory-food-issues',
                'excerpt' => "Picky eating meets Disney dining. Here are the snacks that work for our sensory-sensitive daughter — and where to find them.",
                'body' => "<p>Feeding a sensory-sensitive kid at Disney is its own adventure. Viola has about 8 foods she'll reliably eat, and exactly zero of them are \"adventurous.\"</p><h2>Viola-Approved Disney Snacks</h2><ul><li><strong>Mickey pretzels</strong> (everywhere) — Safe, predictable, salty. Her #1.</li><li><strong>Plain rice from Katsura Grill</strong> (EPCOT Japan) — No sauce, no seasoning. Just rice. She'll eat two bowls.</li><li><strong>French fries from Cosmic Ray's</strong> (Magic Kingdom) — Consistent every time.</li><li><strong>Dole Whip</strong> (Adventureland) — The texture works for her. Pineapple only.</li><li><strong>Popcorn</strong> (carts everywhere) — Reliable and she loves the buckets.</li></ul><h2>Tips for Sensory Eaters at Disney</h2><ul><li><strong>Bring safe foods.</strong> Disney allows outside food. We always pack Goldfish crackers and apple sauce pouches.</li><li><strong>Mobile order everything.</strong> Standing in a food line is a meltdown trigger. Mobile ordering means grab-and-go.</li><li><strong>Eat at off-peak times.</strong> 11 AM or 2 PM. Crowded restaurants are sensory nightmares.</li><li><strong>Don't try new foods on a park day.</strong> Park days are stressful enough. New foods can wait.</li></ul><p>The most important thing: fed is best. If your kid eats nothing but Mickey pretzels for an entire Disney day, that's a win.</p>",
                'category' => 'disney-tips',
                'author' => 'jeffrey',
                'is_published' => true,
                'published_at' => now()->subDays(24),
            ],
        ];

        foreach ($posts as $post) {
            Post::create($post);
        }

        $this->call(GuideSeeder::class);
        $this->call(CommunityStorySeeder::class);
    }
}
