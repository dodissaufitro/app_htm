<?php

namespace App\Console\Commands;

use App\Filament\Resources\DataPemohonResource;
use App\Filament\Resources\PersetujuanResource;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestAllNavigationBadges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:all-navigation-badges {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all navigation badges for a specific user';

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
        $this->line("=== ALL NAVIGATION BADGES TEST FOR: {$user->name} ({$user->email}) ===");

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

        $this->line('');
        $this->line(str_repeat('=', 60));

        // Test DataPemohonResource badge
        $this->testResourceBadge('DataPemohon', DataPemohonResource::class, function () use ($user) {
            $query = \App\Models\DataPemohon::query();
            if (!empty($user->allowed_status)) {
                $query->whereIn('status_permohonan', $user->allowed_status);
            }
            return $query->count();
        });

        // Test PersetujuanResource badge
        $this->testResourceBadge('Persetujuan', PersetujuanResource::class, function () use ($user) {
            $query = \App\Models\DataPemohon::forPersetujuan();
            if (!empty($user->allowed_status)) {
                $query->whereIn('status_permohonan', $user->allowed_status);
            }
            return $query->count();
        });

        // Show current data distribution
        $this->line('');
        $this->line(str_repeat('=', 60));
        $this->line('CURRENT DATA DISTRIBUTION:');

        $distribution = \App\Models\DataPemohon::selectRaw('status_permohonan, COUNT(*) as count')
            ->with('status')
            ->groupBy('status_permohonan')
            ->get();

        foreach ($distribution as $item) {
            $statusName = $item->status?->nama_status ?? 'Unknown';
            $urut = $item->status?->urut ?? 'N/A';
            $accessible = empty($user->allowed_status) || in_array($item->status_permohonan, $user->allowed_status) ? '✅' : '❌';
            $this->line("  {$item->status_permohonan}: {$statusName} (urut: {$urut}) - {$item->count} records {$accessible}");
        }

        // Logout
        Auth::logout();

        return 0;
    }

    private function testResourceBadge(string $resourceName, string $resourceClass, callable $manualCountCallback): void
    {
        $this->line('');
        $this->info("Testing {$resourceName}Resource:");

        // Get navigation badge
        $badgeCount = $resourceClass::getNavigationBadge();
        $badgeColor = $resourceClass::getNavigationBadgeColor();

        // Get manual count
        $manualCount = $manualCountCallback();

        $this->line("  Navigation Badge: {$badgeCount} ({$badgeColor})");
        $this->line("  Manual Count: {$manualCount}");

        if ($badgeCount == $manualCount) {
            $this->line("  Status: ✅ Badge count is correct!");
        } else {
            $this->line("  Status: ❌ Badge count mismatch!");
        }

        // Check if navigation should be visible
        if (method_exists($resourceClass, 'shouldRegisterNavigation')) {
            $shouldShow = $resourceClass::shouldRegisterNavigation();
            $this->line("  Navigation Visible: " . ($shouldShow ? "✅ Yes" : "❌ No"));
        }
    }
}
