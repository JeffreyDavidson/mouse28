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
                'title' => 'Understanding the DAS Pass',
                'slug' => 'understanding-das-pass',
                'excerpt' => 'Everything you need to know about Disney\'s Disability Access Service — how to apply, what to expect, and tips for your video interview.',
                'body' => '<h2>What is DAS?</h2><p>Disney\'s Disability Access Service (DAS) is designed for guests who, due to a developmental disability, are unable to wait in a conventional queue. It allows you to register for a virtual return time instead of standing in line.</p><h2>How to Apply</h2><p>As of 2024, DAS requires a video call interview before your trip. You\'ll speak with a trained Cast Member who will ask about your specific challenges with queuing.</p><ul><li>Be honest and specific about behaviors</li><li>Describe what happens in queue environments</li><li>You don\'t need a doctor\'s note</li><li>The call takes 15-30 minutes</li></ul><h2>Using DAS in the Parks</h2><p>Once approved, you can register for one attraction return time at a time through the My Disney Experience app. While waiting for your return time, you\'re free to enjoy other attractions, eat, or find a quiet spot.</p><h2>Tips for Success</h2><ul><li>Book your DAS interview well before your trip</li><li>Have your entire party\'s tickets linked</li><li>Combine DAS with Lightning Lane for maximum efficiency</li><li>Know that DAS is valid for 120 days at Walt Disney World</li></ul>',
                'park' => 'general',
                'category' => 'das-guide',
                'icon' => '🎫',
                'sort_order' => 1,
            ],
            [
                'title' => 'Sensory Tips for Magic Kingdom',
                'slug' => 'sensory-tips-magic-kingdom',
                'excerpt' => 'Magic Kingdom is the most stimulating park. Here\'s how to manage sensory overload and make the most of your visit.',
                'body' => '<h2>Know the Sensory Hot Spots</h2><p>Magic Kingdom packs more stimulation per square foot than any other park. Fireworks, parades, character meet-and-greets, and dense crowds all converge here.</p><h2>Timing Matters</h2><ul><li><strong>Rope drop (first 2 hours)</strong> — Lowest crowd levels, minimal noise</li><li><strong>11 AM - 3 PM</strong> — Peak sensory overload; consider a break</li><li><strong>Parade times</strong> — Crowds gather on Main Street; use this to ride with shorter waits elsewhere</li></ul><h2>Rides by Sensory Level</h2><h3>Low Stimulation</h3><p>Haunted Mansion, PeopleMover, Carousel of Progress, Country Bear Jamboree</p><h3>Moderate Stimulation</h3><p>Pirates of the Caribbean, Jungle Cruise, It\'s a Small World, Buzz Lightyear</p><h3>High Stimulation</h3><p>Space Mountain (dark + loud), Big Thunder Mountain (sudden drops), Tomorrowland Speedway (engine noise), fireworks shows</p><h2>Must-Have Gear</h2><ul><li>Noise-canceling headphones</li><li>Sunglasses (even indoors for bright rides)</li><li>A familiar comfort item</li><li>Chewy or fidget sensory tools</li></ul>',
                'park' => 'magic-kingdom',
                'category' => 'sensory-tips',
                'icon' => '🏰',
                'sort_order' => 1,
            ],
            [
                'title' => 'Quiet Spots at Magic Kingdom',
                'slug' => 'quiet-spots-magic-kingdom',
                'excerpt' => 'When you need to decompress, these hidden gems offer a break from the Magic Kingdom crowds.',
                'body' => '<h2>Our Tested Quiet Spots</h2><p>After three years of weekly visits, these are the places we go when Viola needs a break.</p><h3>1. Tom Sawyer Island</h3><p>Take the raft over and the noise disappears. Shaded paths, caves to explore, and almost no crowds. Our #1 decompression spot.</p><h3>2. Garden Behind the Castle</h3><p>Walk through Cinderella Castle and look for the garden area to the right. A peaceful pocket most guests walk right past.</p><h3>3. Back of Tomorrowland</h3><p>Past the Speedway, there\'s a quiet stretch with benches and shade. Most people rush to Space Mountain and never see this area.</p><h3>4. Liberty Square Riverboat</h3><p>The upper deck of the riverboat is a floating quiet zone. The ride takes about 15 minutes — perfect for a reset.</p><h3>5. First Aid / Baby Care Center</h3><p>Located near Crystal Palace. Has quiet rooms with dim lighting specifically for situations like sensory overload. Don\'t hesitate to use them.</p><h2>Pro Tips</h2><ul><li>Leave BEFORE the meltdown, not during</li><li>Have a designated "break spot" your child knows about</li><li>Keep a timer — sometimes 15 minutes is all they need</li></ul>',
                'park' => 'magic-kingdom',
                'category' => 'quiet-spots',
                'icon' => '🤫',
                'sort_order' => 2,
            ],
            [
                'title' => 'EPCOT: The Best Park for Neurodiverse Families',
                'slug' => 'epcot-best-park-neurodiverse',
                'excerpt' => 'Why EPCOT\'s layout, pacing, and attractions make it ideal for families navigating autism and sensory differences.',
                'body' => '<h2>Why EPCOT Works</h2><p>EPCOT is the most spread out of all four parks. That alone makes a huge difference for sensory management. But there\'s more:</p><ul><li><strong>World Showcase transitions are gradual</strong> — You move between countries at your own pace with open-air walkways</li><li><strong>Fewer sudden loud noises</strong> — Compared to Magic Kingdom\'s fireworks cannon and Hollywood Studios\' action shows</li><li><strong>Water everywhere</strong> — Fountains, the lagoon, The Seas aquarium — incredibly calming for many kids</li></ul><h2>Our EPCOT Routine</h2><ol><li>Arrive at park open → The Seas with Nemo (aquarium time)</li><li>Ride Frozen Ever After while crowds are thin</li><li>Walk World Showcase counter-clockwise (less crowded direction)</li><li>Lunch in Japan pavilion</li><li>End at the entrance fountain for decompression</li></ol><h2>Best EPCOT Attractions for Sensory-Sensitive Guests</h2><ul><li><strong>The Seas with Nemo</strong> — Gentle ride + amazing aquarium</li><li><strong>Spaceship Earth</strong> — Slow-moving, climate controlled, predictable</li><li><strong>Living with the Land</strong> — Peaceful boat ride through greenhouses</li><li><strong>Kidcot Fun Stops</strong> — Low-key craft activities in each World Showcase pavilion</li></ul>',
                'park' => 'epcot',
                'category' => 'sensory-tips',
                'icon' => '🌍',
                'sort_order' => 1,
            ],
            [
                'title' => 'Accessible Dining at Walt Disney World',
                'slug' => 'accessible-dining-walt-disney-world',
                'excerpt' => 'Tips for navigating Disney dining with sensory food issues, allergies, and the need for calm environments.',
                'body' => '<h2>For Sensory Eaters</h2><p>Disney is surprisingly accommodating for picky and sensory-sensitive eaters.</p><h3>Safe Bets Across All Parks</h3><ul><li><strong>Mickey Pretzels</strong> — Available everywhere, consistent texture and flavor</li><li><strong>French Fries</strong> — Cosmic Ray\'s (MK), Backlot Express (HS), Restaurantosaurus (AK)</li><li><strong>Plain Rice</strong> — Katsura Grill at EPCOT Japan pavilion</li><li><strong>Dole Whip</strong> — Aloha Isle (MK), Swirls on the Water (Disney Springs)</li><li><strong>Popcorn</strong> — Carts everywhere; consistent and familiar</li></ul><h2>Dining Environment Tips</h2><ul><li><strong>Mobile order everything</strong> — Skip the food line, which can be a meltdown trigger</li><li><strong>Eat at off-peak times</strong> — 11 AM lunch, 4:30 PM dinner</li><li><strong>Request outdoor seating</strong> — Less enclosed, easier to leave if needed</li><li><strong>Bring safe foods</strong> — Disney allows outside food and drink</li></ul><h2>Allergy-Friendly Dining</h2><p>Disney is a leader in allergy accommodations. Any table-service restaurant can have a chef come to your table to discuss options. Quick-service locations have allergy menus available — just ask at the register.</p>',
                'park' => 'general',
                'category' => 'dining',
                'icon' => '🍽️',
                'sort_order' => 2,
            ],
            [
                'title' => 'Hollywood Studios Ride Guide',
                'slug' => 'hollywood-studios-ride-guide',
                'excerpt' => 'A sensory breakdown of every major ride at Hollywood Studios, so you know what to expect before you queue.',
                'body' => '<h2>Ride-by-Ride Sensory Guide</h2><h3>Slinky Dog Dash — Moderate</h3><p>Outdoor coaster, smooth with gentle hills. The launch is sudden but not intense. Biggest concern: the queue can get very hot and loud with excited kids.</p><h3>Toy Story Mania — Low to Moderate</h3><p>Dark ride with 3D shooting game. Spinning between screens can cause motion sensitivity. The 3D glasses may bother some kids. Otherwise, engaging and fun.</p><h3>Tower of Terror — High</h3><p>Drop ride with dark themes and sudden falls. The pre-show area is intentionally scary. NOT recommended for most sensory-sensitive guests without preparation.</p><h3>Rock \'n\' Roller Coaster — Very High</h3><p>Indoor coaster with launch, inversions, and extremely loud music. Our most avoided ride.</p><h3>Mickey & Minnie\'s Runaway Railway — Low</h3><p>Trackless dark ride with no drops or sudden movements. Colorful and musical. Viola loves this one.</p><h3>Star Tours — Moderate to High</h3><p>Motion simulator. Can trigger motion sickness. The randomized storyline means you can\'t predict which scenes you\'ll get.</p><h3>Millennium Falcon: Smugglers Run — Moderate</h3><p>Interactive ride where guests "pilot" the Falcon. Pilot seats have the most motion; engineer seats are calmer. The queue is incredible and worth experiencing.</p>',
                'park' => 'hollywood-studios',
                'category' => 'rides',
                'icon' => '🎬',
                'sort_order' => 1,
            ],
            [
                'title' => 'Animal Kingdom: Sensory-Friendly Trails',
                'slug' => 'animal-kingdom-sensory-friendly-trails',
                'excerpt' => 'Animal Kingdom\'s walking trails are perfect for self-paced exploration. Here\'s how to make the most of them.',
                'body' => '<h2>Why Trails Work</h2><p>Animal Kingdom\'s walking trails are a gift for sensory-sensitive families. They\'re self-paced, have no time limits, and the crowds thin out naturally as people rush to rides.</p><h2>The Trails</h2><h3>Maharajah Jungle Trek</h3><p>Located in Asia, this trail features tigers, bats, and birds. The bat enclosure is Viola\'s favorite — it\'s dark, cool, and quiet. The trail is mostly shaded and you can spend as long as you want.</p><h3>Gorilla Falls Exploration Trail</h3><p>At the exit of Kilimanjaro Safaris. Gorillas, hippos, and exotic birds. The hippo underwater viewing area is mesmerizing and naturally calming.</p><h3>Discovery Island Trails</h3><p>Most guests walk right past these to get to rides. The trails wind through lush landscaping with views of animals. Quiet, shaded, and beautiful.</p><h2>Tips for Trail Days</h2><ul><li>Bring binoculars — extends engagement without crowds</li><li>Let your child set the pace</li><li>Visit trails during peak ride times (11 AM - 2 PM) when they\'re emptiest</li><li>The Rainforest Cafe fish tank at the entrance is a great pre-park calming stop</li></ul>',
                'park' => 'animal-kingdom',
                'category' => 'quiet-spots',
                'icon' => '🦁',
                'sort_order' => 1,
            ],
            [
                'title' => 'Planning Your First Accessible Disney Trip',
                'slug' => 'planning-first-accessible-disney-trip',
                'excerpt' => 'A step-by-step planning guide for families visiting Walt Disney World with accessibility needs for the first time.',
                'body' => '<h2>Before Your Trip</h2><h3>1. Apply for DAS (if applicable)</h3><p>Schedule your DAS video interview at least 30 days before your trip. See our full DAS guide for details.</p><h3>2. Research Sensory Guides</h3><p>Read through our park-specific guides to identify which attractions and areas will work for your family.</p><h3>3. Book Accommodations Strategically</h3><p>Consider a resort with easy park access. Pop Century and Art of Animation have Skyliner access to EPCOT and Hollywood Studios, reducing bus stress. If noise is a concern, request a room away from the pool.</p><h3>4. Create a Visual Schedule</h3><p>For many neurodiverse guests, knowing what comes next reduces anxiety enormously. Print or save pictures of your planned activities in order.</p><h2>Packing Essentials</h2><ul><li>Noise-canceling headphones (bring a backup pair)</li><li>Sunglasses</li><li>Comfort items / stim toys</li><li>Safe snacks</li><li>A small portable fan (Florida heat adds sensory load)</li><li>Rain poncho (sudden storms are common and stressful)</li></ul><h2>Day-Of Strategy</h2><ul><li>Arrive early — rope drop has the lowest sensory load</li><li>Plan a mid-day break (return to resort or find a quiet spot)</li><li>Have an exit plan — know where your car is or the bus stop</li><li>Set expectations: "We might leave early, and that\'s okay"</li></ul><h2>Remember</h2><p>There\'s no "right" way to do Disney with accessibility needs. A two-hour visit where everyone is happy is better than an eight-hour endurance test. Start small, learn what works, and build from there.</p>',
                'park' => 'general',
                'category' => 'planning',
                'icon' => '📋',
                'sort_order' => 0,
            ],
            [
                'title' => 'Quiet Spots at EPCOT',
                'slug' => 'quiet-spots-epcot',
                'excerpt' => 'EPCOT has some of the best decompression spots in all of Walt Disney World. Here are our favorites.',
                'body' => '<h2>World Showcase Quiet Spots</h2><h3>Japan Pavilion Gardens</h3><p>The koi pond area is genuinely calming. Viola will sit and watch the fish for 20 minutes straight. The gardens are beautifully maintained and have a natural sound buffer from the crowds.</p><h3>Morocco Pavilion Inner Courtyard</h3><p>The most underrated quiet spot in all of Walt Disney World. Beautifully tiled, shaded, and almost always empty. The Gallery of Arts and History inside is also quiet and cool.</p><h3>United Kingdom Garden</h3><p>Behind the shops, there\'s a lovely English garden area. Benches, shade, and surprisingly peaceful even on busy days.</p><h2>Future World Quiet Spots</h2><h3>The Seas Aquarium</h3><p>After the Nemo ride, you exit into the aquarium area. The lighting is dim, the temperature is cool, and watching the fish is naturally calming. You can stay as long as you want.</p><h3>Behind Imagination Pavilion</h3><p>Walk past the Imagination pavilion toward the water. There are benches with a beautiful lagoon view that most guests never discover.</p><h2>EPCOT-Specific Tips</h2><ul><li>The International Gateway (back entrance) is much calmer than the main entrance</li><li>World Showcase opens later than Future World — arrive early for a quieter Future World experience</li><li>Festival booths add crowds; check the calendar and plan accordingly</li></ul>',
                'park' => 'epcot',
                'category' => 'quiet-spots',
                'icon' => '🌸',
                'sort_order' => 2,
            ],
            [
                'title' => 'Magic Kingdom Rides: Accessibility Breakdown',
                'slug' => 'magic-kingdom-rides-accessibility',
                'excerpt' => 'A detailed accessibility breakdown of Magic Kingdom\'s most popular rides including transfer requirements and sensory levels.',
                'body' => '<h2>Fantasyland</h2><h3>It\'s a Small World — ♿ Wheelchair Accessible</h3><p>Boats accommodate wheelchairs and ECVs. Gentle, slow ride with repetitive music (which can be soothing OR overwhelming depending on the guest). Air conditioned.</p><h3>Peter Pan\'s Flight — Must Transfer</h3><p>Requires stepping into a moving vehicle. The ride is dark with some sudden scene changes. Long standby wait makes DAS especially valuable here.</p><h2>Adventureland</h2><h3>Pirates of the Caribbean — Must Transfer</h3><p>Two small drops (the second is bigger). Dark throughout with cannon fire sounds. The boat is stable. Most sensory-sensitive guests handle this well with headphones.</p><h3>Jungle Cruise — ♿ Wheelchair Accessible</h3><p>Boat ride with live skipper narration. Volume and jokes vary by skipper. Accessible boarding available — ask a Cast Member.</p><h2>Tomorrowland</h2><h3>PeopleMover — ♿ Wheelchair Accessible</h3><p>Our most-ridden attraction. Gentle, open-air, continuous loading means virtually no wait. The brief dark section through Space Mountain building can surprise first-timers.</p><h3>Space Mountain — Must Transfer, High Sensory</h3><p>Complete darkness, sudden turns, loud. Rider switch is available if one parent wants to ride while the other stays with your child.</p><h2>Accessibility Resources at Magic Kingdom</h2><ul><li>Guest Relations at City Hall — accessibility questions and DAS setup</li><li>First Aid near Crystal Palace — quiet rooms available</li><li>Companion restrooms throughout the park</li></ul>',
                'park' => 'magic-kingdom',
                'category' => 'rides',
                'icon' => '🎢',
                'sort_order' => 3,
            ],
        ];

        foreach ($guides as $guide) {
            Guide::create($guide);
        }
    }
}
