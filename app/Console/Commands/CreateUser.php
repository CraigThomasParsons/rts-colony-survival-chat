<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example usage:
     *  php artisan user:create
     *  php artisan user:create --name="Alice" --email="alice@example.com" --password="secret" --admin
     */
    protected $signature = 'user:create
                            {--name= : User display name}
                            {--email= : Email address (unique)}
                            {--password= : Plaintext password}
                            {--admin : Set user as admin (is_admin=1)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new application user via prompts or options.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Gather inputs; prompt if missing
        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password (input hidden)');
        $isAdmin = (bool) $this->option('admin');

        // Basic validation
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required','string','min:2'],
            'email' => ['required','email'],
            'password' => ['required','string','min:6'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $err) {
                $this->error($err);
            }
            return Command::FAILURE;
        }

        // Check uniqueness of email
        if (User::where('email', $email)->exists()) {
            $this->error("A user with email '{$email}' already exists.");
            return Command::FAILURE;
        }

        // Build and save user
        $user = new User();
        // Common columns; adapt to your schema
        if (property_exists($user, 'name')) {
            $user->name = $name;
        }
        if (property_exists($user, 'email')) {
            $user->email = $email;
        }
        // password
        if (property_exists($user, 'password')) {
            $user->password = Hash::make($password);
        }
        // optional is_admin / role columns
        if (SchemaHasColumn('users','is_admin')) {
            $user->is_admin = $isAdmin ? 1 : 0;
        }
        if (SchemaHasColumn('users','role')) {
            $user->role = $isAdmin ? 'admin' : 'user';
        }

        $user->save();

        $this->info("User created: id={$user->id}, email={$user->email}" . ($isAdmin ? ' (admin)' : ''));
        return Command::SUCCESS;
    }
}

// Helper: check if a column exists without importing Schema directly (avoid failing if Schema not loaded)
if (!function_exists('SchemaHasColumn')) {
    function SchemaHasColumn(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
