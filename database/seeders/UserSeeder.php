<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed initial users.
     */
    public function run(): void
    {
        // Check if users already exist
        if (User::where('email', 'craigpars0061@gmail.com')->exists()) {
            $this->command->info('Users already exist. Skipping...');
            return;
        }

        // Create regular user
        User::create([
            'name' => 'Craig Parsons',
            'email' => 'craigpars0061@gmail.com',
            'password' => Hash::make('Radiohead9!'),
            'is_admin' => false,
        ]);

        $this->command->info('✅ Created user: craigpars0061@gmail.com');

        // Create administrator
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@rts-colony.local',
            'password' => Hash::make('Radiohead9!'),
            'is_admin' => true,
        ]);

        $this->command->info('✅ Created admin: admin@rts-colony.local');
    }
}
