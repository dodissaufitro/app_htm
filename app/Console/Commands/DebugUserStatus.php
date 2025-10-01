<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DebugUserStatus extends Command
{
    protected $signature = 'debug:user-status {email}';
    protected $description = 'Debug user allowed status';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        $this->info("User: {$user->name} ({$user->email})");
        $this->info("Raw allowed_status: " . $user->getAttributes()['allowed_status'] ?? 'NULL');
        $this->info("Cast allowed_status type: " . gettype($user->allowed_status));
        $this->info("Cast allowed_status value: " . json_encode($user->allowed_status));

        if (is_array($user->allowed_status)) {
            $this->info("Array count: " . count($user->allowed_status));
            foreach ($user->allowed_status as $index => $status) {
                $this->info("  [{$index}] => {$status}");
            }
        }
    }
}
