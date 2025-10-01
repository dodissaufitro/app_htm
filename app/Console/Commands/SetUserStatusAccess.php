<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Status;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;

class SetUserStatusAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:set-status-access {email} {--status=* : Status codes to allow} {--roles=* : Role names to assign} {--clear : Clear all status restrictions} {--clear-roles : Clear all roles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set allowed status access and roles for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $statusCodes = $this->option('status');
        $roleNames = $this->option('roles');
        $clear = $this->option('clear');
        $clearRoles = $this->option('clear-roles');

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Handle status clearing
        if ($clear) {
            $user->update(['allowed_status' => null]);
            $this->info("All status restrictions cleared for user '{$user->name}' ({$user->email}).");
        }

        // Handle roles clearing
        if ($clearRoles) {
            $user->syncRoles([]);
            $this->info("All roles cleared for user '{$user->name}' ({$user->email}).");
        }

        // Handle status setting
        if (!empty($statusCodes)) {
            // Validate status codes
            $validStatusCodes = Status::whereIn('kode', $statusCodes)->pluck('kode')->toArray();
            $invalidStatusCodes = array_diff($statusCodes, $validStatusCodes);

            if (!empty($invalidStatusCodes)) {
                $this->error("Invalid status codes: " . implode(', ', $invalidStatusCodes));
                $this->showAvailableStatus();
                return 1;
            }

            // Update user
            $user->update(['allowed_status' => $validStatusCodes]);
            $this->info("Status access updated for user '{$user->name}' ({$user->email}).");
        }

        // Handle roles setting
        if (!empty($roleNames)) {
            // Validate role names
            $validRoles = Role::whereIn('name', $roleNames)->get();
            $validRoleNames = $validRoles->pluck('name')->toArray();
            $invalidRoleNames = array_diff($roleNames, $validRoleNames);

            if (!empty($invalidRoleNames)) {
                $this->error("Invalid role names: " . implode(', ', $invalidRoleNames));
                $this->showAvailableRoles();
                return 1;
            }

            // Update user roles
            $user->syncRoles($validRoleNames);
            $this->info("Roles updated for user '{$user->name}' ({$user->email}).");
        }

        // Show current access if no operations were performed
        if (empty($statusCodes) && empty($roleNames) && !$clear && !$clearRoles) {
            $this->showCurrentAccess($user);
        } else {
            $this->showCurrentAccess($user);
        }

        return 0;
    }

    private function showCurrentAccess(User $user)
    {
        $this->line('');
        $this->line("Current access for '{$user->name}' ({$user->email}):");

        // Show roles
        $roles = $user->roles;
        if ($roles->isEmpty()) {
            $this->warn("  Roles: No roles assigned");
        } else {
            $this->info("  Roles:");
            foreach ($roles as $role) {
                $this->line("    → {$role->name}");
            }
        }

        // Show status access
        if (empty($user->allowed_status)) {
            $this->info("  Status Access: Can access ALL status (no restrictions)");
        } else {
            $statusNames = Status::whereIn('kode', $user->allowed_status)
                ->orderBy('urut')
                ->get()
                ->map(fn($status) => "    → {$status->kode}: {$status->nama_status}")
                ->join("\n");

            $this->info("  Allowed status:");
            $this->line($statusNames);
        }
    }

    private function showAvailableStatus()
    {
        $this->line('');
        $this->line('Available status codes:');

        $statusList = Status::orderBy('urut')
            ->get()
            ->map(fn($status) => "  {$status->kode}: {$status->nama_status}")
            ->join("\n");

        $this->line($statusList);
    }

    private function showAvailableRoles()
    {
        $this->line('');
        $this->line('Available roles:');

        $roleList = Role::all()
            ->map(fn($role) => "  {$role->name}")
            ->join("\n");

        $this->line($roleList);
    }
}
