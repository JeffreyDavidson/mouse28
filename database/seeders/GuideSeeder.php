<?php

namespace Database\Seeders;

use App\Models\Guide;
use Illuminate\Database\Seeder;

class GuideSeeder extends Seeder
{
    public function run(): void
    {
        $guides = [
            [
                'title' => 'DAS Pass: Complete Application Guide',
                'slug' => 'das-pass-complete-application-guide',
                'excerpt' => "Everything you need to know about applying for Disney's Disability Access Service pass — the 2024 process, what to expect, and tips for approval.",
                'body' => '<p>The DAS (Disability Access Service) pass is Disney\'s primary accommodation for guests who cannot wait in conventional queues due to a developmental disability like autism.</p><h2>How to Apply</h2><p>As of 2024, all DAS applications are done via video call before your trip through the My Disney Experience app or website.</p><ul><li>Schedule your video call 2-30 days before your visit</li><li>Have the person who needs the accommodation on the call</li><li>Be prepared to explain specific challenges with queuing</li><li>Calls typically take 15-30 minutes</li></ul><h2>What They Ask</h2><p>The cast member will ask about your specific challenges with waiting in a traditional queue environment. Be honest and specific — describe what happens (meltdowns, elopement, self-harm) rather than just naming the diagnosis.</p><h2>How DAS Works</h2><p>Once approved, you can select a ride and receive a return time equal to the current standby wait. You wait somewhere comfortable (another ride, a quiet spot, getting food) and return at your time to enter through the Lightning Lane.</p>',
                'park' => 'general',
                'category' => 'das-guide',
                'icon' => '🎫',
                'sort_order' => 1,
            ],
            [
                'title' => 'Magic Kingdom Quiet Spots Map',
                'slug' => 'magic-kingdom-quiet-spots',
                'excerpt' => 'Our tested and trusted quiet spots at Magic Kingdom for when sensory overload hits.',
                'body' => '<p>Magic Kingdom is the most visited theme park in the world — which means it can be the most overwhelming. Here are the quiet spots we\'ve found after 150+ visits.</p><h2>Adventureland</h2><p><strong>Swiss Family Treehouse area</strong> — The walkway past the treehouse is often nearly empty. Great shade and minimal noise.</p><h2>Frontierland</h2><p><strong>Tom Sawyer Island</strong> — Take the raft over and the noise drops immediately. Self-paced exploration with zero crowd pressure.</p><h2>Fantasyland</h2><p><strong>Rapunzel restroom area</strong> — The corridor near the Rapunzel-themed restrooms has benches and is tucked away from main paths.</p><h2>Tomorrowland</h2><p><strong>PeopleMover queue area</strong> — Even when the ride has a wait, the surrounding area is calmer than the Space Mountain corridor.</p><h2>First Aid</h2><p>Located near the Crystal Palace. They have quiet rooms specifically for situations like sensory overload. Don\'t hesitate to ask.</p>',
                'park' => 'magic-kingdom',
                'category' => 'quiet-spots',
                'icon' => '🏰',
                'sort_order' => 2,
            ],
            [
                'title' => 'EPCOT Sensory Guide: Ride-by-Ride Ratings',
                'slug' => 'epcot-sensory-guide-ride-ratings',
                'excerpt' => 'Every EPCOT ride rated by sensory intensity — noise, darkness, motion, and crowds.',
                'body' => '<p>We\'ve rated every EPCOT attraction on a 1-5 scale for four sensory factors: noise, darkness, motion, and crowd density.</p><h2>Low Sensory (Great for sensitive visitors)</h2><ul><li><strong>The Seas with Nemo</strong> — Noise: 2, Dark: 2, Motion: 1, Crowds: 2. The aquarium afterwards is genuinely calming.</li><li><strong>Spaceship Earth</strong> — Noise: 2, Dark: 3, Motion: 1, Crowds: 2. Slow-moving, predictable, educational.</li><li><strong>Living with the Land</strong> — Noise: 1, Dark: 1, Motion: 1, Crowds: 1. Viola\'s favorite. Calm boat ride through greenhouses.</li></ul><h2>Moderate Sensory</h2><ul><li><strong>Frozen Ever After</strong> — Noise: 3, Dark: 2, Motion: 2, Crowds: 4. The backward drop surprises some kids.</li><li><strong>Remy\'s Ratatouille Adventure</strong> — Noise: 3, Dark: 3, Motion: 3, Crowds: 3. 3D glasses required. Trackless ride can be unpredictable.</li></ul><h2>High Sensory (Prepare accordingly)</h2><ul><li><strong>Test Track</strong> — Noise: 4, Dark: 2, Motion: 5, Crowds: 4. Very fast. Loud wind noise.</li><li><strong>Guardians of the Galaxy</strong> — Noise: 5, Dark: 5, Motion: 5, Crowds: 4. Intense roller coaster. Not recommended for most sensory-sensitive visitors.</li></ul>',
                'park' => 'epcot',
                'category' => 'rides',
                'icon' => '🎢',
                'sort_order' => 3,
            ],
            [
                'title' => 'Sensory-Friendly Dining at Disney World',
                'slug' => 'sensory-friendly-dining-disney-world',
                'excerpt' => 'The quietest restaurants, best mobile ordering tips, and safe foods for picky eaters.',
                'body' => '<p>Feeding a sensory-sensitive child at Disney can feel like its own adventure. Here\'s what we\'ve learned.</p><h2>Best Restaurants for Sensory Sensitivity</h2><ul><li><strong>Katsura Grill (EPCOT Japan)</strong> — Outdoor seating, calm atmosphere, simple menu options including plain rice.</li><li><strong>Columbia Harbour House (Magic Kingdom)</strong> — Upstairs seating is often nearly empty and much quieter than downstairs.</li><li><strong>Satu\'li Canteen (Animal Kingdom)</strong> — Mobile ordering means no waiting in loud lines. Customizable bowls.</li></ul><h2>Mobile Ordering Tips</h2><p>Always use mobile ordering when available. It eliminates the need to stand in a loud, crowded queue. Order 30 minutes before you want to eat, find your table first, then hit "I\'m Here" when you\'re settled.</p><h2>Safe Foods That Work for Us</h2><ul><li>Mickey pretzels (everywhere)</li><li>French fries (Cosmic Ray\'s, Pecos Bill)</li><li>Plain rice (Katsura Grill, Yak & Yeti)</li><li>Dole Whip (Adventureland, various festivals)</li><li>Popcorn (carts throughout every park)</li></ul><p>Pro tip: Disney allows outside food. Always bring backup safe foods.</p>',
                'park' => 'general',
                'category' => 'dining',
                'icon' => '🍽️',
                'sort_order' => 4,
            ],
            [
                'title' => 'Hollywood Studios: Planning for Sensory Success',
                'slug' => 'hollywood-studios-planning-sensory-success',
                'excerpt' => 'Hollywood Studios has the most intense attractions. Here\'s how to plan a successful day.',
                'body' => '<p>Hollywood Studios is the smallest park but arguably the most intense sensory experience. Planning is essential.</p><h2>Know Before You Go</h2><p>Galaxy\'s Edge and Toy Story Land are the busiest areas. If your child is sensitive to crowds, hit these areas at rope drop or in the last hour before close.</p><h2>Best Attractions for Sensitive Visitors</h2><ul><li><strong>Toy Story Mania</strong> — Indoor, air-conditioned, game-like. Most kids love it regardless of sensory sensitivity.</li><li><strong>Lightning McQueen\'s Racing Academy</strong> — Show format, seated, predictable. Good for a break.</li><li><strong>Animation Courtyard</strong> — When no character meets are happening, this area is practically empty.</li></ul><h2>Avoid or Prepare For</h2><ul><li><strong>Tower of Terror</strong> — Intense drops, darkness, loud. Not recommended without preparation.</li><li><strong>Rock \'n\' Roller Coaster</strong> — Extreme noise and speed in complete darkness.</li><li><strong>Star Tours</strong> — 3D motion simulator. Can cause disorientation.</li></ul><h2>Our Strategy</h2><p>We do 2-hour visits to Hollywood Studios. Arrive at rope drop, hit Toy Story Mania and Smugglers Run, walk through Galaxy\'s Edge for the theming, and leave before the afternoon crowd peak.</p>',
                'park' => 'hollywood-studios',
                'category' => 'planning',
                'icon' => '🎬',
                'sort_order' => 5,
            ],
            [
                'title' => 'Animal Kingdom\'s Hidden Sensory Sanctuaries',
                'slug' => 'animal-kingdom-hidden-sensory-sanctuaries',
                'excerpt' => 'Animal Kingdom is naturally calming — here are the best spots we\'ve found for decompression.',
                'body' => '<p>Animal Kingdom might be the most naturally sensory-friendly park. Its design emphasizes nature, shade, and winding paths — all of which naturally reduce overstimulation.</p><h2>Our Favorite Calm Spots</h2><ul><li><strong>Maharajah Jungle Trek</strong> — A self-paced walking trail with no time pressure. Viola loves the bat enclosure (dark and quiet).</li><li><strong>Discovery Island Trails</strong> — Most guests walk right past these. Shaded nature paths with animals.</li><li><strong>Gorilla Falls Exploration Trail</strong> — Another self-paced trail. The gorilla viewing area is particularly peaceful.</li></ul><h2>Best Rides</h2><ul><li><strong>Kilimanjaro Safaris</strong> — Open-air truck ride through animal habitats. Unpredictable animal sightings keep it engaging.</li><li><strong>Na\'vi River Journey</strong> — Slow boat ride, beautiful bioluminescent scenery. Very calming.</li></ul><h2>What to Watch For</h2><p>The Festival of the Lion King show can be very loud. Expedition Everest has a backwards section in the dark that can be frightening. Always preview ride videos at home first.</p>',
                'park' => 'animal-kingdom',
                'category' => 'quiet-spots',
                'icon' => '🌿',
                'sort_order' => 6,
            ],
            [
                'title' => 'First Visit Checklist for Autism Families',
                'slug' => 'first-visit-checklist-autism-families',
                'excerpt' => 'Taking your autistic child to Disney for the first time? This checklist covers everything from DAS to sensory kits.',
                'body' => '<h2>Before Your Trip</h2><ul><li>☐ Apply for DAS pass (2-30 days before visit)</li><li>☐ Watch ride-through videos at home with your child</li><li>☐ Create a visual schedule of the day</li><li>☐ Pack a sensory kit (noise-canceling headphones, fidgets, sunglasses)</li><li>☐ Download My Disney Experience app</li><li>☐ Identify 3-4 quiet spots in your target park</li><li>☐ Pack safe/comfort foods</li><li>☐ Bring a stroller (even for older kids — it\'s a safe retreat space)</li></ul><h2>Day-Of Tips</h2><ul><li>☐ Arrive at rope drop (least crowded)</li><li>☐ Locate First Aid and Baby Care Center immediately</li><li>☐ Set a timer to check in with your child every 30-60 minutes</li><li>☐ Use mobile ordering for all meals</li><li>☐ Have an exit plan — know where the car is, know the fastest route out</li><li>☐ Take breaks BEFORE meltdowns, not during</li></ul><h2>What to Tell Cast Members</h2><p>Cast Members are trained and want to help. You can say: "My child has autism and may need a moment" or "We\'re using DAS — could you help us with the return time process?"</p><p>You don\'t owe anyone a detailed explanation. A simple heads-up goes a long way.</p>',
                'park' => 'general',
                'category' => 'planning',
                'icon' => '✅',
                'sort_order' => 7,
            ],
            [
                'title' => 'Understanding Sensory Overload at Theme Parks',
                'slug' => 'understanding-sensory-overload-theme-parks',
                'excerpt' => 'What sensory overload looks like, what triggers it at Disney, and how to prevent and manage it.',
                'body' => '<h2>What Is Sensory Overload?</h2><p>Sensory overload happens when the brain receives more input than it can process. At theme parks, this can be triggered by noise, crowds, heat, bright lights, strong smells, unexpected touch, and constant stimulation.</p><h2>Signs to Watch For</h2><ul><li>Covering ears or eyes</li><li>Increased stimming (hand-flapping, rocking, spinning)</li><li>Becoming unusually quiet or withdrawn</li><li>Agitation, crying, or aggression</li><li>Refusing to move or dropping to the ground</li><li>Elopement (running away)</li></ul><h2>Disney-Specific Triggers</h2><ul><li>Fireworks (loud, unpredictable)</li><li>Parade music and crowds</li><li>Dark rides with sudden effects</li><li>Gift shop lighting and music</li><li>Character interactions (costumes, unexpected touch)</li><li>Crowd density at chokepoints</li></ul><h2>Prevention Strategies</h2><p>The best meltdown is the one that never happens. Schedule breaks every 60-90 minutes. Use noise-canceling headphones proactively, not reactively. Leave the park BEFORE your child hits their limit — not at it.</p><h2>When It Happens</h2><p>Stay calm. Find the nearest quiet spot. Don\'t try to reason or redirect — just be present. Let your child\'s nervous system reset. It\'s not a failure. It\'s just part of the experience.</p>',
                'park' => 'general',
                'category' => 'sensory-tips',
                'icon' => '🧠',
                'sort_order' => 8,
            ],
        ];

        foreach ($guides as $guide) {
            Guide::create($guide);
        }
    }
}
