<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class TestAppResourceAccess extends Command
{
    protected $signature = 'test:app-resource-access {user_id?}';
    protected $description = 'Test App resource access control';

    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;
        $user = User::find($userId);

        if (!$user) {
            $this->error("User dengan ID {$userId} tidak ditemukan!");
            return 1;
        }

        $this->info("Testing akses untuk user: {$user->name} (ID: {$user->id})");

        // Test App Resources
        $appResources = [
            'app::verifikator',
            'app::bank',
            'app::developer',
            'app::penetapan',
            'app::bast',
            'app::akad'
        ];

        $this->info("\n=== Test Permission Access ===");

        foreach ($appResources as $resource) {
            $this->line("\nResource: {$resource}");

            $permissions = [
                "view_any_{$resource}",
                "view_{$resource}",
                "create_{$resource}",
                "update_{$resource}",
                "delete_{$resource}",
            ];

            foreach ($permissions as $permission) {
                $hasPermission = $user->can($permission);
                $status = $hasPermission ? '✅' : '❌';
                $this->line("  {$status} {$permission}");
            }
        }

        $this->info("\n=== User Roles ===");
        foreach ($user->roles as $role) {
            $this->line("- {$role->name}");
        }

        $this->info("\n=== Available App Permissions ===");
        $appPermissions = Permission::where('name', 'LIKE', '%app::%')->count();
        $this->line("Total App permissions: {$appPermissions}");

        return 0;
    }
}
