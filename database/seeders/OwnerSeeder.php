<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Create (or update) the single owner account.
     *
     * Credentials come from .env so no password is ever committed:
     *
     *   OWNER_NAME="Anmol Owner"
     *   OWNER_EMAIL=owner@anmol.test
     *   OWNER_PASSWORD=change-this-now
     */
    public function run(): void
    {
        $email = env('OWNER_EMAIL');
        $password = env('OWNER_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->warn('OWNER_EMAIL / OWNER_PASSWORD missing in .env — owner account not created.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('OWNER_NAME', 'Owner'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );

        $this->command?->info("Owner account ready: {$email}");
    }
}
