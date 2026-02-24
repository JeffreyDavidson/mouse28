<?php

namespace Database\Seeders;

use App\Models\CommunityStory;
use Illuminate\Database\Seeder;

class CommunityStorySeeder extends Seeder
{
    public function run(): void
    {
        $stories = [
            [
                'name' => 'Sarah Mitchell',
                'email' => 'sarah.m@example.com',
                'location' => 'Tampa, FL',
                'child_name' => 'Ethan',
                'child_age' => 7,
                'title' => 'The Day Ethan Hugged Mickey',
                'story' => "Ethan has been non-verbal since birth, and we were honestly terrified of our first Disney trip. What if the noise was too much? What if he melted down in front of thousands of people? But something magical happened at Town Square Theater. Mickey crouched down to Ethan's level, didn't rush, didn't push. He just waited. And after about two minutes, Ethan walked up and hugged him. It was the first time he'd voluntarily hugged anyone outside our family. I'm crying typing this. That moment changed everything for us — we've been annual passholders ever since.",
                'favorite_park' => 'Magic Kingdom',
                'favorite_tip' => 'Let cast members know about your child before character meets. They are trained and incredibly patient.',
                'photo_consent' => true,
                'is_approved' => true,
                'is_featured' => true,
                'approved_at' => now()->subDays(10),
            ],
            [
                'name' => 'Marcus & Tanya Williams',
                'email' => 'williamsFamily@example.com',
                'location' => 'Atlanta, GA',
                'child_name' => 'Jaylen',
                'child_age' => 11,
                'title' => 'Finding Peace at the Japan Pavilion',
                'story' => "Jaylen is on the spectrum and has intense sensory needs. EPCOT's World Showcase has become his safe space. He can spend an hour at the koi pond in Japan, just watching the fish and the water. The cast members there recognize him now and always wave. We tried so many activities over the years — sports, camps, you name it. Disney is the only place where Jaylen truly relaxes and is himself. The predictability of it, the water features, the gentle pace of World Showcase — it just works for our family.",
                'favorite_park' => 'EPCOT',
                'favorite_tip' => 'Explore World Showcase counter-clockwise. It\'s less crowded and the transitions between countries are smoother.',
                'photo_consent' => false,
                'is_approved' => true,
                'is_featured' => true,
                'approved_at' => now()->subDays(7),
            ],
            [
                'name' => 'Jennifer Olsen',
                'email' => 'jen.olsen@example.com',
                'location' => 'Minneapolis, MN',
                'child_name' => 'Lily',
                'child_age' => 5,
                'title' => 'Our First Trip with the DAS Pass',
                'story' => "We flew from Minnesota for our first ever Disney trip, armed with our newly approved DAS pass and a binder full of visual schedules. I overprepared. I researched every quiet spot, mapped every bathroom, timed every route. And you know what? It went beautifully. Not perfectly — Lily had a tough moment near It's a Small World — but beautifully. She rode the teacups six times. She ate her weight in Mickey pretzels. She fell asleep in the stroller under the fireworks. It was the best vacation we've ever taken.",
                'favorite_park' => 'Magic Kingdom',
                'favorite_tip' => 'Create a visual schedule with pictures of rides and locations. Show your child what\'s coming next — it reduces anxiety enormously.',
                'photo_consent' => true,
                'is_approved' => true,
                'is_featured' => true,
                'approved_at' => now()->subDays(5),
            ],
            [
                'name' => 'David Chen',
                'email' => 'dchen@example.com',
                'location' => 'Orlando, FL',
                'child_name' => 'Max',
                'child_age' => 9,
                'title' => 'Weekly Disney Visits Changed Our Lives',
                'story' => "We live 20 minutes from Disney and got annual passes when Max was diagnosed at age 4. Five years later, we go almost every week. People think we're crazy, but the routine is everything for Max. He knows exactly where the water fountains are, which bathroom has the hand dryers (he hates those), and the exact order to ride things at Hollywood Studios. Disney gave him a framework for the world that makes sense to him. His therapist says his social skills have improved dramatically since we started going regularly.",
                'favorite_park' => 'Hollywood Studios',
                'favorite_tip' => 'If your child hates hand dryers, carry a small pack of tissues. Most Disney bathrooms have both options but knowing in advance helps.',
                'photo_consent' => false,
                'is_approved' => true,
                'is_featured' => false,
                'approved_at' => now()->subDays(3),
            ],
            [
                'name' => 'Amanda Reyes',
                'email' => 'amanda.r@example.com',
                'location' => 'San Diego, CA',
                'child_name' => 'Sofia',
                'child_age' => 6,
                'title' => 'Disneyland Through Sofia\'s Eyes',
                'story' => "Sofia was diagnosed with ASD at 3. We took her to Disneyland last spring not knowing what to expect. She was overwhelmed at first — the entrance area is SO loud and crowded. But once we got to Fantasyland, she transformed. She rode the carousel four times, laughing the whole way. She waved at every single character from a safe distance. She discovered churros and declared them her new favorite food. We learned so much about what she can handle and what she needs. We're going back next month.",
                'favorite_park' => 'Disneyland',
                'favorite_tip' => 'Arrive early and start in the back of the park. Let your child warm up to the environment gradually instead of hitting Main Street crowds first.',
                'photo_consent' => true,
                'is_approved' => true,
                'is_featured' => false,
                'approved_at' => now()->subDays(2),
            ],
            [
                'name' => 'Rachel Thompson',
                'email' => 'rachel.t@example.com',
                'location' => 'Nashville, TN',
                'child_name' => 'Oliver',
                'child_age' => 8,
                'title' => 'The Stranger Who Changed Our Day',
                'story' => "Oliver had a massive meltdown at Animal Kingdom. Full screaming, on the ground, the works. I was trying to stay calm but I could feel every eye on us. Then a woman walked up, handed me a bottle of water, smiled, and said 'You're doing great, mama.' That was it. She walked away. I burst into tears. That small act of kindness from a stranger reminded me that most people are good, and that we belong at Disney just as much as anyone else. Oliver recovered 10 minutes later and had the best afternoon on the safari ride.",
                'favorite_park' => 'Animal Kingdom',
                'favorite_tip' => 'If you see a family struggling with a meltdown, a kind word or even just a sympathetic smile goes further than you know.',
                'photo_consent' => false,
                'is_approved' => false,
                'is_featured' => false,
            ],
            [
                'name' => 'Tom & Karen Blackwell',
                'email' => 'blackwells@example.com',
                'location' => 'Boston, MA',
                'child_name' => 'Nate',
                'child_age' => 13,
                'title' => 'Disney as a Teenager on the Spectrum',
                'story' => "Everyone told us Nate would outgrow Disney. He's 13 now and it's still his favorite place on earth. The difference is how he experiences it — he's become obsessed with the engineering behind the rides. He can tell you exactly how the Haunted Mansion doom buggies work, the physics of Space Mountain, how the animatronics in Pirates move. Disney has become his special interest in the best possible way. He's talking about studying imagineering in college. Who are we to say that's not real magic?",
                'favorite_park' => 'Magic Kingdom',
                'favorite_tip' => 'Don\'t assume your child will outgrow Disney. The way they experience it will evolve, and that evolution can be beautiful.',
                'photo_consent' => true,
                'is_approved' => false,
                'is_featured' => false,
            ],
        ];

        foreach ($stories as $story) {
            CommunityStory::create($story);
        }
    }
}
