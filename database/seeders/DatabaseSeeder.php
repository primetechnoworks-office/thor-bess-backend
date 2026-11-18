<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('thor.seed_admin');

        if (! User::where('email', $admin['email'])->exists()) {
            // No password in .env → generate one and print it once.
            $password = $admin['password'] ?: Str::password(14, symbols: false);

            User::create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => $password,
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->command?->info("Admin account: {$admin['email']}");
            if (! $admin['password']) {
                $this->command?->warn("Generated password: {$password}  (change it from Profile after first login)");
            }
        }

        $this->call(PostSeeder::class);
    }
}
