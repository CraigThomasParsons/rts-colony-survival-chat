<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure users table exists before inserting
        if (!DB::getSchemaBuilder()->hasTable('users')) {
            return; // silently skip if users table not present
        }

        $email = 'copilot@example.com';
        $exists = DB::table('users')->where('email', $email)->exists();

        $schema = DB::getSchemaBuilder();
        $hasAvatarColumn = $schema->hasColumn('users', 'avatar_url');

        if (!$exists) {
            $payload = [
                'name' => 'Copilot Admin',
                'email' => $email,
                'password' => Hash::make('TempP@ssw0rd!'),
                'is_admin' => true,
                'email_verified_at' => now(),
                'remember_token' => str()->random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($hasAvatarColumn) {
                $payload['avatar_url'] = null;
            }

            DB::table('users')->insert($payload);
        } else {
            // Update existing to ensure admin privileges
            DB::table('users')->where('email', $email)->update([
                'name' => 'Copilot Admin',
                'is_admin' => true,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('users')) {
            return;
        }
        DB::table('users')->where('email', 'copilot@example.com')->delete();
    }
};
