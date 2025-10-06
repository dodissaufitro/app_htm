<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignAppResourcePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shield:assign-app-permissions {role} {--resource=} {--permissions=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign App resource permissions to a role';

    /**
     * App resources yang tersedia
     *
     * @var array
     */
    protected $appResources = [
        'app::verifikator',
        'app::bank',
        'app::developer',
        'app::penetapan',
        'app::bast',
        'app::akad',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleName = $this->argument('role');
        $specificResource = $this->option('resource');
        $specificPermissions = $this->option('permissions');

        // Validasi role
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Role '{$roleName}' tidak ditemukan!");
            return 1;
        }

        $this->info("Assigning permissions to role: {$roleName}");

        // Tentukan resource yang akan diproses
        $resources = $specificResource ? [$specificResource] : $this->appResources;

        // Validasi resource jika spesifik
        if ($specificResource && !in_array($specificResource, $this->appResources)) {
            $this->error("Resource '{$specificResource}' tidak valid!");
            $this->line("Available resources: " . implode(', ', $this->appResources));
            return 1;
        }

        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($resources as $resource) {
            $this->info("\nProcessing resource: {$resource}");

            // Dapatkan semua permission untuk resource ini
            $resourcePermissions = Permission::where('name', 'LIKE', "%{$resource}")
                ->get();

            if ($specificPermissions) {
                $permissionFilters = explode(',', $specificPermissions);
                $resourcePermissions = $resourcePermissions->filter(function ($permission) use ($permissionFilters) {
                    foreach ($permissionFilters as $filter) {
                        if (str_starts_with($permission->name, trim($filter))) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            foreach ($resourcePermissions as $permission) {
                if ($role->hasPermissionTo($permission)) {
                    $this->warn("  Already assigned: {$permission->name}");
                    $skippedCount++;
                    continue;
                }

                $role->givePermissionTo($permission);
                $this->info("  Assigned: {$permission->name}");
                $assignedCount++;
            }
        }

        $this->info("\nPermission assignment completed!");
        $this->info("Assigned: {$assignedCount} permissions");
        $this->info("Skipped: {$skippedCount} permissions (already assigned)");

        return 0;
    }
}
