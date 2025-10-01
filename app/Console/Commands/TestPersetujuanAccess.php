<?php

namespace App\Console\Commands;

use App\Models\DataPemohon;
use App\Models\Status;
use Illuminate\Console\Command;

class TestPersetujuanAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:persetujuan-access {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test persetujuan access for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if ($email) {
            $this->testUserAccess($email);
        } else {
            $this->showGeneralInfo();
        }

        return 0;
    }

    private function testUserAccess(string $email): void
    {
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            $this->error("User '{$email}' not found.");
            return;
        }

        $this->line('');
        $this->line("=== PERSETUJUAN ACCESS TEST FOR: {$user->name} ({$user->email}) ===");

        // Show user roles
        $roles = $user->roles->pluck('name')->toArray();
        if (empty($roles)) {
            $this->warn("Roles: No roles assigned");
        } else {
            $this->info("Roles: " . implode(', ', $roles));
        }

        // Show allowed status
        if (empty($user->allowed_status)) {
            $this->info("Status Access: ALL status allowed");
        } else {
            $statusNames = Status::whereIn('kode', $user->allowed_status)
                ->pluck('nama_status')
                ->toArray();
            $this->info("Status Access: " . implode(', ', $statusNames));
        }

        // Show persetujuan data access
        $persetujuanQuery = DataPemohon::forPersetujuan();

        // Apply user status restrictions if any
        if (!empty($user->allowed_status)) {
            $persetujuanQuery->whereIn('status_permohonan', $user->allowed_status);
        }

        $persetujuanCount = $persetujuanQuery->count();
        $totalPersetujuan = DataPemohon::forPersetujuan()->count();

        $this->line('');
        $this->line("PERSETUJUAN ACCESS RESULTS:");
        $this->line("Total data persetujuan (urut=1): {$totalPersetujuan}");
        $this->line("Data accessible by user: {$persetujuanCount}");

        if ($persetujuanCount < $totalPersetujuan) {
            $this->warn("User has restricted access due to allowed_status configuration.");
        } else {
            $this->info("User has full access to all persetujuan data.");
        }

        // Show navigation access
        $hasRoleAccess = $user->hasRole(['Super Admin', 'Admin', 'Approver', 'Verifikator']);
        $this->line('');
        $this->line("NAVIGATION ACCESS:");
        if ($hasRoleAccess) {
            $this->info("✅ User can see Persetujuan navigation menu");
        } else {
            $this->error("❌ User CANNOT see Persetujuan navigation menu");
        }
    }

    private function showGeneralInfo(): void
    {
        $this->line('');
        $this->line("=== PERSETUJUAN SYSTEM INFO ===");

        // Show status with urut = 1
        $status = Status::where('urut', 1)->first();
        if ($status) {
            $this->info("Status for Persetujuan (urut=1): {$status->kode} - {$status->nama_status}");
        } else {
            $this->error("No status found with urut=1");
            return;
        }

        // Show total persetujuan data
        $totalPersetujuan = DataPemohon::forPersetujuan()->count();
        $this->info("Total DataPemohon with status urut=1: {$totalPersetujuan}");

        // Show roles that can access
        $this->line('');
        $this->line("ROLES THAT CAN ACCESS PERSETUJUAN:");
        $allowedRoles = ['Super Admin', 'Admin', 'Approver', 'Verifikator'];
        foreach ($allowedRoles as $role) {
            $this->line("  ✅ {$role}");
        }

        $this->line('');
        $this->line("USAGE:");
        $this->line("  php artisan test:persetujuan-access user@example.com");
    }
}
