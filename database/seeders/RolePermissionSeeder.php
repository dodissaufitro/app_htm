<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Data Pemohon Permissions
            'view_any_kelengkapan::data',
            'view_kelengkapan::data',
            'create_kelengkapan::data',
            'update_kelengkapan::data',
            'delete_kelengkapan::data',
            'delete_any_kelengkapan::data',
            'force_delete_kelengkapan::data',
            'force_delete_any_kelengkapan::data',
            'restore_kelengkapan::data',
            'restore_any_kelengkapan::data',
            'replicate_kelengkapan::data',
            'reorder_kelengkapan::data',

            // User Management Permissions
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',

            // Status Management Permissions
            'view_any_status',
            'view_status',
            'create_status',
            'update_status',
            'delete_status',
            'delete_any_status',

            // Bank Management Permissions
            'view_any_daftar::bank',
            'view_daftar::bank',
            'create_daftar::bank',
            'update_daftar::bank',
            'delete_daftar::bank',
            'delete_any_daftar::bank',

            // General Permissions
            'access_admin_panel',
            'manage_roles_permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo([
            'access_admin_panel',
            'view_any_kelengkapan::data',
            'view_kelengkapan::data',
            'create_kelengkapan::data',
            'update_kelengkapan::data',
            'delete_kelengkapan::data',
            'view_any_user',
            'view_user',
            'view_any_status',
            'view_status',
            'view_any_daftar::bank',
            'view_daftar::bank',
        ]);

        $verifikatorRole = Role::firstOrCreate(['name' => 'Verifikator']);
        $verifikatorRole->givePermissionTo([
            'access_admin_panel',
            'view_any_kelengkapan::data',
            'view_kelengkapan::data',
            'update_kelengkapan::data',
        ]);

        $approverRole = Role::firstOrCreate(['name' => 'Approver']);
        $approverRole->givePermissionTo([
            'access_admin_panel',
            'view_any_kelengkapan::data',
            'view_kelengkapan::data',
            'update_kelengkapan::data',
        ]);

        $operatorRole = Role::firstOrCreate(['name' => 'Operator']);
        $operatorRole->givePermissionTo([
            'access_admin_panel',
            'view_any_kelengkapan::data',
            'view_kelengkapan::data',
            'create_kelengkapan::data',
            'update_kelengkapan::data',
        ]);

        $viewerRole = Role::firstOrCreate(['name' => 'Viewer']);
        $viewerRole->givePermissionTo([
            'access_admin_panel',
            'view_any_kelengkapan::data',
            'view_kelengkapan::data',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created roles: Super Admin, Admin, Verifikator, Approver, Operator, Viewer');
    }
}
