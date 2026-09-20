<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdministratorSeeder extends Seeder
{
    public function run(): void
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
