<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {email? : The email of the user to grant admin privileges}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant admin privileges to a user by email or interactive selection';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        if (!$email) {
            $users = User::all(['id', 'name', 'email', 'is_admin']);

            if ($users->isEmpty()) {
                $this->error('No users found in database. Please register a user first.');
                return Command::FAILURE;
            }

            $choices = $users->mapWithKeys(function ($user) {
                $status = $user->is_admin ? '[ALREADY ADMIN]' : '[REGULAR USER]';
                return [$user->email => "{$user->name} ({$user->email}) {$status}"];
            })->toArray();

            $email = $this->choice('Select a user to make Admin', array_keys($choices));
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return Command::FAILURE;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("✅ Success! '{$user->name}' ({$user->email}) is now an Administrator.");
        $this->line("Admin Panel URL: " . url('/admin'));

        return Command::SUCCESS;
    }
}
