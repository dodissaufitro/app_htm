<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GiveVerifikatorPermissionCommand extends Command
{
    protected $signature = 'permission:give-verifikator-access';
    protected $description = 'Give view_any_app::verifikator permission to Verifikator role';

    public function handle()
    {
        $this->info("🔑 Giving AppVerifikator permission to Verifikator role");

        try {
            $role = Role::where('name', 'Verifikator')->first();
            $permission = Permission::where('name', 'view_any_app::verifikator')->first();

            if (!$role) {
                $this->error("❌ Role 'Verifikator' not found");
                return 1;
            }

            if (!$permission) {
                $this->error("❌ Permission 'view_any_app::verifikator' not found");
                return 1;
            }

            // Check if already has permission
            if ($role->hasPermissionTo($permission)) {
                $this->warn("⚠️  Role 'Verifikator' already has permission 'view_any_app::verifikator'");
            } else {
                $role->givePermissionTo($permission);
                $this->info("✅ Permission 'view_any_app::verifikator' given to role 'Verifikator'");
            }

            // Show current permissions
            $this->info("📋 Current permissions for Verifikator role:");
            $permissions = $role->permissions->pluck('name')->toArray();

            foreach ($permissions as $perm) {
                if (str_contains($perm, 'app::verifikator')) {
                    $this->line("   ✅ {$perm}");
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
