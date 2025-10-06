<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class GenerateAppResourcePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shield:generate-app-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate permissions for all App resources';

    /**
     * App resources yang membutuhkan permission
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
     * Permission prefixes dari config filament-shield
     *
     * @var array
     */
    protected $permissionPrefixes = [
        'view',
        'view_any',
        'create',
        'update',
        'restore',
        'restore_any',
        'replicate',
        'reorder',
        'delete',
        'delete_any',
        'force_delete',
        'force_delete_any',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating permissions for App resources...');

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($this->appResources as $resource) {
            foreach ($this->permissionPrefixes as $prefix) {
                $permissionName = "{$prefix}_{$resource}";

                if (Permission::where('name', $permissionName)->exists()) {
                    $this->warn("Permission already exists: {$permissionName}");
                    $skippedCount++;
                    continue;
                }

                Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);

                $this->info("Created permission: {$permissionName}");
                $createdCount++;
            }
        }

        $this->info("\nPermission generation completed!");
        $this->info("Created: {$createdCount} permissions");
        $this->info("Skipped: {$skippedCount} permissions (already exist)");

        $this->line("\nNext steps:");
        $this->line("1. Assign these permissions to roles using the Shield interface");
        $this->line("2. Or use the following command to assign to a role:");
        $this->line("   php artisan shield:assign {role_name}");
    }
}
