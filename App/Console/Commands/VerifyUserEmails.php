<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyUserEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:verify-emails {--all : Verify all users} {--user= : Verify specific user by email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify user emails automatically for development/testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $this->verifyAllUsers();
        } elseif ($this->option('user')) {
            $this->verifySpecificUser($this->option('user'));
        } else {
            $this->error('Please specify --all or --user=email@example.com');
            return 1;
        }

        return 0;
    }

    private function verifyAllUsers()
    {
        $users = User::whereNull('email_verified_at')->get();
        
        if ($users->isEmpty()) {
            $this->info('All users already have verified emails.');
            return;
        }

        $this->info("Found {$users->count()} users with unverified emails.");
        
        foreach ($users as $user) {
            $user->update(['email_verified_at' => now()]);
            $this->line("✅ Verified: {$user->email}");
        }

        $this->info("✅ All users verified successfully!");
    }

    private function verifySpecificUser(string $email)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return;
        }

        if ($user->email_verified_at) {
            $this->info("User {$email} already has verified email.");
            return;
        }

        $user->update(['email_verified_at' => now()]);
        $this->info("✅ User {$email} verified successfully!");
    }
}
