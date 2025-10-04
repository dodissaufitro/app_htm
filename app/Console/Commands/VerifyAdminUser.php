<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerifyAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify admin user details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = \App\Models\User::where('email', 'admin@gmail.com')->first();

        if ($user) {
            $this->info('Admin User Found:');
            $this->line('Name: ' . $user->name);
            $this->line('Email: ' . $user->email);
            $this->line('Roles: ' . implode(', ', $user->getRoleNames()->toArray()));
            $this->line('Email Verified: ' . ($user->email_verified_at ? 'Yes' : 'No'));
            $this->line('Created: ' . $user->created_at);

            if ($user->allowed_status) {
                $allowedStatus = json_decode($user->allowed_status, true);
                if (is_array($allowedStatus)) {
                    $this->line('Allowed Status: ' . implode(', ', $allowedStatus));
                } else {
                    $this->line('Allowed Status: ' . $user->allowed_status);
                    $this->warn('⚠️  Allowed status is not a valid JSON array');
                }
            }

            $this->newLine();
            $this->info('✅ Admin user is properly configured!');
        } else {
            $this->error('❌ Admin user not found!');
        }
    }
}
