<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DiagnoseCountError extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:count-error';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose count() errors in the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Diagnosing Count() Errors...');
        $this->newLine();

        // Check all users and their allowed_status
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $this->line("User: {$user->name} ({$user->email})");
            $this->line("allowed_status type: " . gettype($user->allowed_status));

            if ($user->allowed_status) {
                $this->line("allowed_status raw: " . $user->allowed_status);

                try {
                    $decoded = json_decode($user->allowed_status, true);
                    $this->line("JSON decode result type: " . gettype($decoded));

                    if (is_array($decoded)) {
                        $this->line("Decoded count: " . count($decoded));
                        $this->line("Decoded content: " . implode(', ', $decoded));
                    } else {
                        $this->error("❌ allowed_status is not a valid JSON array!");
                    }
                } catch (\Exception $e) {
                    $this->error("❌ JSON decode error: " . $e->getMessage());
                }
            } else {
                $this->line("allowed_status is null/empty");
            }
            $this->newLine();
        }

        // Test problematic operations
        $this->line('🧪 Testing Specific Operations...');

        try {
            // Test DataPemohonResource navigation badge
            $user = \App\Models\User::first();
            if ($user && !empty($user->allowed_status)) {
                $allowedStatus = json_decode($user->allowed_status, true);
                if (is_array($allowedStatus)) {
                    $query = \App\Models\DataPemohon::query();
                    $query->whereIn('status_permohonan', $allowedStatus);
                    $count = $query->count();
                    $this->info("✅ DataPemohon badge count: {$count}");
                } else {
                    $this->error("❌ allowed_status is not an array: " . gettype($allowedStatus));
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Error in DataPemohon badge: " . $e->getMessage());
            $this->line("File: " . $e->getFile() . ":" . $e->getLine());
        }

        $this->newLine();
        $this->info('🏁 Diagnosis Complete!');
    }
}
