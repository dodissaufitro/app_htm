<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class AppResourceRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // App resources
        $appResources = [
            'app::verifikator',
            'app::bank',
            'app::developer',
            'app::penetapan',
            'app::bast',
            'app::akad',
        ];

        // Permission prefixes
        $permissionPrefixes = [
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

        $this->command->info('Creating permissions for App resources...');

        // Create permissions for each app resource
        foreach ($appResources as $resource) {
            foreach ($permissionPrefixes as $prefix) {
                $permissionName = "{$prefix}_{$resource}";

                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }
            $this->command->info("Created permissions for: {$resource}");
        }

        // Create roles dengan permission yang berbeda
        $roles = [
            'Super Admin' => 'all', // Akses ke semua App resources
            'Admin' => 'all', // Akses ke semua App resources
            'Verifikator UPDP' => ['app::verifikator'], // Hanya akses ke App Verifikator
            'Admin Bank' => ['app::bank'], // Hanya akses ke App Bank
            'Admin Developer' => ['app::developer'], // Hanya akses ke App Developer
            'Admin Penetapan' => ['app::penetapan'], // Hanya akses ke App Penetapan
            'Admin BAST' => ['app::bast'], // Hanya akses ke App BAST
            'Admin Akad' => ['app::akad'], // Hanya akses ke App Akad
            'Manager' => $appResources, // Akses ke semua kecuali delete/force_delete
            'Supervisor' => ['app::verifikator', 'app::bank', 'app::developer'], // Akses terbatas
        ];

        $this->command->info('Creating roles and assigning permissions...');

        foreach ($roles as $roleName => $resourceAccess) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($resourceAccess === 'all') {
                // Give access to all app resources
                $permissions = Permission::where('name', 'LIKE', 'view_any_app::%')
                    ->orWhere('name', 'LIKE', 'view_app::%')
                    ->orWhere('name', 'LIKE', 'create_app::%')
                    ->orWhere('name', 'LIKE', 'update_app::%')
                    ->get();

                if (in_array($roleName, ['Super Admin', 'Admin'])) {
                    // Super Admin dan Admin mendapat semua permission
                    $permissions = Permission::where('name', 'LIKE', '%app::%')->get();
                }
            } else {
                // Give access to specific resources
                $permissions = collect();
                foreach ($resourceAccess as $resource) {
                    $resourcePermissions = Permission::where('name', 'LIKE', "%{$resource}")
                        ->get();

                    if ($roleName === 'Manager') {
                        // Manager tidak bisa delete atau force delete
                        $resourcePermissions = $resourcePermissions->reject(function ($permission) {
                            return str_contains($permission->name, 'delete') ||
                                str_contains($permission->name, 'force_delete');
                        });
                    }

                    $permissions = $permissions->merge($resourcePermissions);
                }
            }

            $role->syncPermissions($permissions);
            $this->command->info("Created role: {$roleName} with " . $permissions->count() . " permissions");
        }

        $this->command->info('App resource roles and permissions seeded successfully!');
    }
}
