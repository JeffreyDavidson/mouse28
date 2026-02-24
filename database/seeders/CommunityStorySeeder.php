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
                'name' => 'Sarah M.',
                'email' => 'sarah.m@example.com',
                'location' => 'Austin, TX',
                'child_name' => 'Ethan',
                'child_age' => 6,
                'title' => 'The Day Ethan Met Baymax',
                'story' => "We were terrified to take Ethan to Disney for the first time. He was 5, nonverbal, and had never been in a crowd that big. But we applied for DAS and planned everything down to the minute.\n\nThe moment that changed everything was meeting Baymax at EPCOT. Ethan walked right up — he never walks up to anyone — and hugged him. He just stood there hugging this big white character for what felt like five minutes. The cast member didn't rush us. She just smiled.\n\nEthan is 6 now and points at the Baymax photo on our fridge every morning. It's his happy place. And honestly, it's become ours too.",
                'favorite_park' => 'EPCOT',
                'favorite_tip' => 'Bring noise-canceling headphones and put them on BEFORE you need them, not after the meltdown starts.',
                'photo_consent' => true,
                'is_approved' => true,
                'is_featured' => true,
                'approved_at' => now()->subDays(5),
            ],
            [
                'name' => 'Marcus & Tanya',
                'email' => 'marcus.t@example.com',
                'location' => 'Atlanta, GA',
                'child_name' => 'Zoe',
                'child_age' => 10,
                'title' => 'Zoe Found Her Voice on the Teacups',
                'story' => "Our daughter Zoe has been mostly nonverbal since age 3. She has a handful of words but usually communicates through her AAC device. We've been to Disney maybe 20 times.\n\nLast month, on the Mad Tea Party (teacups), Zoe started laughing — really laughing — and then clear as day said 'again!' when the ride stopped. We both cried right there in Fantasyland.\n\nDisney doesn't fix anything. But it creates these moments where joy breaks through in ways you don't expect. And those moments sustain you through all the hard days.",
                'favorite_park' => 'Magic Kingdom',
                'favorite_tip' => 'Don\'t try to do everything. Pick 2-3 rides and one meal. A successful 3-hour trip beats a miserable 8-hour one.',
                'is_approved' => true,
                'is_featured' => true,
                'approved_at' => now()->subDays(3),
            ],
            [
                'name' => 'Jennifer L.',
                'email' => 'jen.l@example.com',
                'location' => 'Orlando, FL',
                'child_name' => 'Max',
                'child_age' => 12,
                'title' => 'A Cast Member Who Changed Our Day',
                'story' => "Max was having a terrible day at Hollywood Studios. Full meltdown near Star Tours. I was sitting on the ground with him, people walking around us, feeling like the worst parent in the world.\n\nA cast member came over, knelt down, and quietly said 'Take all the time you need. Can I bring you some water?' She came back with two waters and a Mickey sticker. She didn't ask what was wrong. She didn't judge.\n\nThat was three years ago and I still think about her. One person showing kindness in a moment of crisis changed our entire relationship with Disney.",
                'favorite_park' => 'Hollywood Studios',
                'favorite_tip' => 'If your kid has a meltdown, remember: the people judging you are wrong, and the people helping you are angels. Focus on the angels.',
                'is_approved' => true,
                'is_featured' => false,
                'approved_at' => now()->subDays(8),
            ],
            [
                'name' => 'David K.',
                'email' => 'david.k@example.com',
                'location' => 'Minneapolis, MN',
                'child_name' => 'Lily',
                'child_age' => 8,
                'title' => 'How We Made Disney Work for Our Whole Family',
                'story' => "We have three kids — Lily (8, autistic), James (11), and Sophie (5). For years we didn't go to Disney because we thought it would be too hard to balance everyone's needs.\n\nThis year we finally went. We used DAS for Lily, gave James a watch so he could have some independence, and my wife and I took turns — one parent with Lily doing calm activities, one with the other kids on bigger rides.\n\nWas it perfect? No. Did Lily eat nothing but popcorn for three days? Yes. But all three kids said it was the best vacation ever. And that's enough.",
                'favorite_park' => 'Animal Kingdom',
                'favorite_tip' => 'If you have multiple kids with different needs, take turns. Not every family member needs to do everything together every minute.',
                'is_approved' => true,
                'approved_at' => now()->subDays(12),
            ],
            [
                'name' => 'Priya S.',
                'email' => 'priya.s@example.com',
                'location' => 'San Diego, CA',
                'child_name' => 'Arjun',
                'child_age' => 4,
                'title' => 'Our First DAS Experience',
                'story' => "Arjun was diagnosed at 3 and we applied for DAS right before his 4th birthday trip. I was so nervous about the video call — would they believe us? Would I say the wrong thing?\n\nThe cast member was wonderful. She asked what happens when Arjun has to wait in a confined space, and I just described a normal grocery store trip. She approved us in 15 minutes.\n\nUsing DAS at the parks felt like a weight lifting off our shoulders. Instead of dreading every queue, we could enjoy the experience. Arjun rode Buzz Lightyear four times in a row and I got to actually watch him enjoy it instead of managing a crisis.",
                'favorite_park' => 'Magic Kingdom',
                'favorite_tip' => 'For the DAS video call, write down specific examples beforehand. Not medical terms — real moments. That\'s what they need to hear.',
                'is_approved' => true,
                'approved_at' => now()->subDays(2),
            ],
            [
                'name' => 'Anonymous',
                'email' => 'anon1@example.com',
                'child_name' => null,
                'child_age' => 15,
                'title' => 'Disney as a Teenager on the Spectrum',
                'story' => "I'm 15 and autistic. I wanted to write this because most stories are from parents, but I have my own perspective.\n\nDisney is one of the only places where I feel like the world was designed for me. Everything is predictable. The rides do the same thing every time. The music plays on a loop. The cast members follow scripts. That predictability is what my brain craves.\n\nYeah, the crowds are hard. I wear my headphones and have my DAS pass. But the feeling of walking down Main Street and knowing exactly what's around every corner? That's better than any therapy I've ever had.\n\nTo other autistic teenagers: it's okay to love Disney. It's not childish. It's your happy place.",
                'favorite_park' => 'EPCOT',
                'favorite_tip' => 'Don\'t be embarrassed about needing accommodations. DAS exists because Disney knows not everyone experiences the parks the same way.',
                'is_approved' => false,
            ],
        ];

        foreach ($stories as $story) {
            CommunityStory::create($story);
        }
    }
}
