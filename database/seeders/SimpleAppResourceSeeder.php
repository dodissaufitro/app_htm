<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SimpleAppResourceSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role super_admin jika belum ada
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web'
        ]);

        // Assign semua permission app ke super_admin
        $appPermissions = Permission::where('name', 'LIKE', '%app::%')->get();
        $superAdmin->syncPermissions($appPermissions);

        echo "Assigned " . $appPermissions->count() . " app permissions to super_admin role\n";

        // Buat role panel_user jika belum ada  
        $panelUser = Role::firstOrCreate([
            'name' => 'panel_user',
            'guard_name' => 'web'
        ]);

        echo "Created panel_user role\n";
    }
}
