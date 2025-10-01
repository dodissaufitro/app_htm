<?php

namespace App\Console\Commands;

use App\Filament\Resources\PersetujuanResource;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestNavigationBadge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:navigation-badge {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test navigation badge count for a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User '{$email}' not found.");
            return 1;
        }

        // Simulate login as this user
        Auth::login($user);

        $this->line('');
        $this->line("=== NAVIGATION BADGE TEST FOR: {$user->name} ({$user->email}) ===");

        // Get navigation badge count
        $badgeCount = PersetujuanResource::getNavigationBadge();
        $badgeColor = PersetujuanResource::getNavigationBadgeColor();

        $this->line('');
        $this->info("Navigation Badge Count: {$badgeCount}");
        $this->info("Navigation Badge Color: {$badgeColor}");

        // Show user context
        $roles = $user->roles->pluck('name')->toArray();
        $this->line('');
        $this->line("User Context:");
        $this->line("  Roles: " . (empty($roles) ? 'None' : implode(', ', $roles)));

        if (empty($user->allowed_status)) {
            $this->line("  Status Access: ALL status allowed");
        } else {
            $statusNames = \App\Models\Status::whereIn('kode', $user->allowed_status)
                ->pluck('nama_status')
                ->toArray();
            $this->line("  Status Access: " . implode(', ', $statusNames));
        }

        // Verify with manual count
        $manualQuery = \App\Models\DataPemohon::forPersetujuan();
        if (!empty($user->allowed_status)) {
            $manualQuery->whereIn('status_permohonan', $user->allowed_status);
        }
        $manualCount = $manualQuery->count();

        $this->line('');
        $this->line("Verification:");
        $this->line("  Manual count: {$manualCount}");
        $this->line("  Badge count: {$badgeCount}");

        if ($manualCount == $badgeCount) {
            $this->info("✅ Badge count is correct!");
        } else {
            $this->error("❌ Badge count mismatch!");
        }

        // Logout
        Auth::logout();

        return 0;
    }
}
