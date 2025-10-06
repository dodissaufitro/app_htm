<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DataPemohon;
use App\Filament\Resources\PersetujuanDeveloperResource;
use Illuminate\Support\Facades\Auth;

class TestPersetujuanDeveloperCommand extends Command
{
    protected $signature = 'test:persetujuan-developer {user_id=4}';
    protected $description = 'Test PersetujuanDeveloper resource access and functionality';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User dengan ID {$userId} tidak ditemukan.");
            return 1;
        }

        $this->info("🧪 Testing PersetujuanDeveloper Resource");
        $this->info("👤 User: {$user->name} (ID: {$user->id})");
        $this->info("🔢 Urutan: {$user->urutan}");
        $this->newLine();

        // Set auth context untuk testing
        Auth::login($user);

        // Test 1: Navigation visibility
        $this->info("📋 Test 1: Navigation Visibility");
        $shouldShow = PersetujuanDeveloperResource::shouldRegisterNavigation();
        $canAccess = PersetujuanDeveloperResource::canAccess();

        $this->line("   Should show in navigation: " . ($shouldShow ? '✅ Yes' : '❌ No'));
        $this->line("   Can access resource: " . ($canAccess ? '✅ Yes' : '❌ No'));


        if ($user->urutan === 3) {
            if ($shouldShow && $canAccess) {
                $this->info("   ✅ Access control working correctly for Developer");
            } else {
                $this->error("   ❌ Access control not working for Developer");
            }
        } elseif ($user->hasRole('Super Admin')) {
            if ($shouldShow && $canAccess) {
                $this->info("   ✅ Access control working correctly for Super Admin");
            } else {
                $this->error("   ❌ Access control not working for Super Admin");
            }
        } else {
            if (!$shouldShow && !$canAccess) {
                $this->info("   ✅ Access control working correctly for non-Developer/non-Super Admin");
            } else {
                $this->error("   ❌ Access control not working for non-Developer/non-Super Admin");
            }
        }        // Test 2: Badge count
        $this->info("📊 Test 2: Badge Count");
        $badgeCount = PersetujuanDeveloperResource::getNavigationBadge();
        $actualCount = DataPemohon::where('status_permohonan', '2')->count();

        $this->line("   Badge count: {$badgeCount}");
        $this->line("   Actual count (status=2): {$actualCount}");

        if ($badgeCount == $actualCount) {
            $this->info("   ✅ Badge count accurate");
        } else {
            $this->error("   ❌ Badge count mismatch");
        }

        // Test 3: Query filtering
        $this->info("🔍 Test 3: Query Filtering");
        $resourceQuery = PersetujuanDeveloperResource::getEloquentQuery();
        $resourceCount = $resourceQuery->count();

        $this->line("   Resource query count: {$resourceCount}");

        if ($resourceCount == $actualCount) {
            $this->info("   ✅ Query filtering working correctly");
        } else {
            $this->error("   ❌ Query filtering not working");
        }

        // Test 4: Show sample data
        if ($actualCount > 0) {
            $this->info("📄 Test 4: Sample Data");
            $sampleData = $resourceQuery->first();
            $this->line("   Sample record:");
            $this->line("     ID: {$sampleData->id}");
            $this->line("     ID Pendaftaran: {$sampleData->id_pendaftaran}");
            $this->line("     Nama: {$sampleData->nama}");
            $this->line("     Status: {$sampleData->status_permohonan}");
            $this->line("     Updated: {$sampleData->updated_at}");
        } else {
            $this->warn("📄 Test 4: No data with status_permohonan = 2 found");
        }

        $this->newLine();
        $this->info("🎯 Testing completed!");

        Auth::logout();
        return 0;
    }
}
