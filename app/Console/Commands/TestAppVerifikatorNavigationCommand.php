<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Filament\Resources\AppVerifikatorResource;
use Illuminate\Support\Facades\Auth;

class TestAppVerifikatorNavigationCommand extends Command
{
    protected $signature = 'test:app-verifikator-navigation {user_id=1}';
    protected $description = 'Test AppVerifikator resource navigation for different users';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User dengan ID {$userId} tidak ditemukan.");
            return 1;
        }

        $this->info("🧪 Testing AppVerifikator Navigation");
        $this->info("👤 User: {$user->name} (ID: {$user->id})");
        $this->info("🔐 Roles: " . $user->roles->pluck('name')->join(', '));
        $this->newLine();

        // Set auth context untuk testing
        Auth::login($user);

        // Test 1: Navigation visibility
        $this->info("📋 Test 1: Navigation Visibility");
        $shouldShow = AppVerifikatorResource::shouldRegisterNavigation();
        $canAccess = AppVerifikatorResource::canAccess();

        $this->line("   Should show in navigation: " . ($shouldShow ? '✅ Yes' : '❌ No'));
        $this->line("   Can access resource: " . ($canAccess ? '✅ Yes' : '❌ No'));

        // Test 2: Permission check
        $this->info("🔑 Test 2: Permission Check");
        $hasRole = $user->hasRole('Super Admin');
        $hasPermission = $user->can('view_any_app::verifikator');

        $this->line("   Has Super Admin role: " . ($hasRole ? '✅ Yes' : '❌ No'));
        $this->line("   Has view_any_app::verifikator permission: " . ($hasPermission ? '✅ Yes' : '❌ No'));

        // Test 3: Resource info
        $this->info("ℹ️  Test 3: Resource Configuration");
        $navigationGroup = AppVerifikatorResource::getNavigationGroup();
        $navigationLabel = AppVerifikatorResource::getNavigationLabel();
        $navigationSort = AppVerifikatorResource::getNavigationSort();

        $this->line("   Navigation Group: {$navigationGroup}");
        $this->line("   Navigation Label: {$navigationLabel}");
        $this->line("   Navigation Sort: {$navigationSort}");

        // Test 4: Expected result
        $this->info("🎯 Test 4: Expected Result");
        if ($user->hasRole('Super Admin')) {
            if ($shouldShow && $canAccess) {
                $this->info("   ✅ Super Admin access working correctly");
            } else {
                $this->error("   ❌ Super Admin access not working");
            }
        } else {
            if ($hasPermission) {
                if ($shouldShow && $canAccess) {
                    $this->info("   ✅ Permission-based access working correctly");
                } else {
                    $this->error("   ❌ Permission-based access not working");
                }
            } else {
                if (!$shouldShow && !$canAccess) {
                    $this->info("   ✅ Access control working correctly (blocked)");
                } else {
                    $this->error("   ❌ Access control not working (should be blocked)");
                }
            }
        }

        $this->newLine();
        $this->info("🎯 Testing completed!");

        Auth::logout();
        return 0;
    }
}
