<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createConfiguredAdministrator();

        Podcast::query()->firstOrCreate(['id' => 1], [
            'name' => 'Mouse28',
            'description' => 'Disney parks through the eyes of a family raising a daughter with autism. Hosted by Jeffrey & Cassie Davidson.',
        ]);

        if (app()->isProduction()) {
            return;
        }

        $episodes = Episode::factory()->count(4)->create();
        Episode::factory()->draft()->create();
        Episode::factory()->scheduled()->create();

        Post::factory()
            ->count(6)
            ->state(fn (): array => ['episode_id' => $episodes->random()->getKey()])
            ->create();
        Post::factory()->count(2)->draft()->create();
        Post::factory()->scheduled()->create();

        Guide::factory()->count(4)->create();
        Guide::factory()->draft()->create();
        Guide::factory()->scheduled()->create();
    }

    private function createConfiguredAdministrator(): void
    {
        $email = config('mouse28.seed_admin.email');
        $password = config('mouse28.seed_admin.password');

        if (! is_string($email) || blank($email) || ! is_string($password) || blank($password)) {
            return;
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => config('mouse28.seed_admin.name'),
                'password' => $password,
            ],
        );

        $user->forceFill(['is_admin' => true])->save();
    }
}
