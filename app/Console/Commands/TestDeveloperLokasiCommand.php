<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DataHunian;
use Spatie\Permission\Models\Role;

class TestDeveloperLokasiCommand extends Command
{
    protected $signature = 'test:developer-lokasi {user_id?}';
    protected $description = 'Test developer lokasi hunian functionality';

    public function handle()
    {
        $this->info("🧪 Testing Developer Lokasi Hunian Feature");
        $this->newLine();

        // Test 1: Check DataHunian availability
        $this->info("📋 Test 1: DataHunian Availability");
        $hunianCount = DataHunian::count();
        $this->line("   Total DataHunian records: {$hunianCount}");

        if ($hunianCount > 0) {
            $sampleHunian = DataHunian::take(3)->get();
            $this->line("   Sample locations:");
            foreach ($sampleHunian as $hunian) {
                $this->line("     - ID: {$hunian->id}, Name: {$hunian->nama_pemukiman}");
            }
            $this->info("   ✅ DataHunian records available");
        } else {
            $this->warn("   ⚠️  No DataHunian records found");
        }

        // Test 2: Check Developer role
        $this->info("📋 Test 2: Developer Role Check");
        $developerRole = Role::where('name', 'Developer')->first();

        if ($developerRole) {
            $this->line("   Developer role found: {$developerRole->name}");
            $developersCount = User::role('Developer')->count();
            $this->line("   Users with Developer role: {$developersCount}");
            $this->info("   ✅ Developer role available");
        } else {
            $this->error("   ❌ Developer role not found");
        }

        // Test 3: Test specific user if provided
        $userId = $this->argument('user_id');
        if ($userId) {
            $this->info("📋 Test 3: User Specific Test");
            $user = User::find($userId);

            if (!$user) {
                $this->error("   ❌ User with ID {$userId} not found");
                return 1;
            }

            $this->line("   User: {$user->name} (ID: {$user->id})");
            $this->line("   Roles: " . $user->roles->pluck('name')->join(', '));
            $this->line("   Urutan: {$user->urutan}");

            // Check lokasi_hunian field
            $lokasiHunian = $user->lokasi_hunian;
            $this->line("   Lokasi Hunian (raw): " . json_encode($lokasiHunian));
            $this->line("   Is Developer: " . ($user->hasRole('Developer') ? 'Yes' : 'No'));
            $this->line("   Has Locations: " . (!empty($lokasiHunian) ? 'Yes' : 'No'));

            // Test helper methods
            $isDeveloperWithLocations = $user->isDeveloperWithLocations();
            $lokasiNames = $user->lokasi_hunian_names;

            $this->line("   isDeveloperWithLocations(): " . ($isDeveloperWithLocations ? 'Yes' : 'No'));
            $this->line("   Lokasi Names: " . json_encode($lokasiNames));

            if ($user->hasRole('Developer')) {
                $this->info("   ✅ User is Developer");

                if (!empty($lokasiHunian)) {
                    $this->info("   ✅ User has location assignments");
                    $this->line("   Managed locations:");
                    $lokasiRecords = $user->lokasiHunian();
                    foreach ($lokasiRecords as $lokasi) {
                        $this->line("     - {$lokasi->nama_pemukiman}");
                    }
                } else {
                    $this->warn("   ⚠️  User has no location assignments (can handle all)");
                }
            } else {
                $this->warn("   ⚠️  User is not a Developer");
            }
        }

        // Test 4: Form options test
        $this->info("📋 Test 4: Form Options Test");
        $hunianOptions = DataHunian::select('nama_pemukiman', 'id')
            ->distinct()
            ->orderBy('nama_pemukiman')
            ->pluck('nama_pemukiman', 'id')
            ->toArray();

        $this->line("   Available options for form:");
        $count = 0;
        foreach ($hunianOptions as $id => $name) {
            if ($count < 5) { // Show only first 5
                $this->line("     - ID: {$id}, Name: {$name}");
                $count++;
            }
        }

        if (count($hunianOptions) > 5) {
            $remaining = count($hunianOptions) - 5;
            $this->line("     ... and {$remaining} more");
        }

        $this->info("   ✅ Form options ready: " . count($hunianOptions) . " locations");

        $this->newLine();
        $this->info("🎯 Testing completed!");

        if (!$userId) {
            $this->line("");
            $this->comment("💡 Tip: Run 'php artisan test:developer-lokasi {user_id}' to test specific user");
        }

        return 0;
    }
}
