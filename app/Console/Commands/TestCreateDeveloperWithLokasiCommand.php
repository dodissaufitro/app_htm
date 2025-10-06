<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DataHunian;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class TestCreateDeveloperWithLokasiCommand extends Command
{
    protected $signature = 'test:create-developer-with-lokasi';
    protected $description = 'Test creating developer user with lokasi assignments';

    public function handle()
    {
        $this->info("🧪 Testing Create Developer with Lokasi");
        $this->newLine();

        // Test 1: Create test developer
        $this->info("📋 Test 1: Create Test Developer");

        $testEmail = 'test.developer@example.com';
        $existingUser = User::where('email', $testEmail)->first();

        if ($existingUser) {
            $this->warn("   ⚠️  Test user already exists, deleting...");
            $existingUser->delete();
        }

        // Get available locations
        $availableLocations = DataHunian::take(3)->pluck('id')->toArray();
        $this->line("   Available locations for assignment: " . json_encode($availableLocations));

        // Create new user with lokasi
        $userData = [
            'name' => 'Test Developer',
            'email' => $testEmail,
            'password' => Hash::make('password'),
            'urutan' => 3,
            'lokasi_hunian' => $availableLocations,
        ];

        $user = User::create($userData);
        $this->info("   ✅ Created user: {$user->name} (ID: {$user->id})");

        // Assign Developer role
        $developerRole = Role::where('name', 'Developer')->first();
        if ($developerRole) {
            $user->assignRole($developerRole);
            $this->info("   ✅ Assigned Developer role");
        }

        // Test 2: Verify assignments
        $this->info("📋 Test 2: Verify Assignments");
        $user->refresh();

        $this->line("   User roles: " . $user->roles->pluck('name')->join(', '));
        $this->line("   Lokasi hunian (raw): " . json_encode($user->lokasi_hunian));
        $this->line("   Is Developer: " . ($user->hasRole('Developer') ? 'Yes' : 'No'));
        $this->line("   Has locations: " . (!empty($user->lokasi_hunian) ? 'Yes' : 'No'));
        $this->line("   isDeveloperWithLocations(): " . ($user->isDeveloperWithLocations() ? 'Yes' : 'No'));

        // Test location names
        $locationNames = $user->lokasi_hunian_names;
        $this->line("   Location names: " . json_encode($locationNames));

        // Test location records
        $locationRecords = $user->lokasiHunian();
        $this->line("   Location records:");
        foreach ($locationRecords as $location) {
            $this->line("     - ID: {$location->id}, Name: {$location->nama_pemukiman}");
        }

        // Test 3: Test canHandleLocation method
        $this->info("📋 Test 3: Test Location Handling");

        foreach ($availableLocations as $locationId) {
            $canHandle = $user->canHandleLocation($locationId);
            $locationName = DataHunian::find($locationId)->nama_pemukiman ?? 'Unknown';
            $this->line("   Can handle {$locationName} (ID: {$locationId}): " . ($canHandle ? 'Yes' : 'No'));
        }

        // Test with location not assigned
        $otherLocation = DataHunian::whereNotIn('id', $availableLocations)->first();
        if ($otherLocation) {
            $canHandle = $user->canHandleLocation($otherLocation->id);
            $this->line("   Can handle {$otherLocation->nama_pemukiman} (ID: {$otherLocation->id}): " . ($canHandle ? 'Yes' : 'No'));
        }

        // Test 4: Simulate form data processing
        $this->info("📋 Test 4: Form Data Processing");

        $formData = [
            'name' => 'Updated Test Developer',
            'email' => $testEmail,
            'roles' => [$developerRole->id],
            'lokasi_hunian' => [1, 2, 4], // Different locations
            'urutan' => 3,
        ];

        $this->line("   Simulated form data:");
        $this->line("     Roles: " . json_encode($formData['roles']));
        $this->line("     Lokasi: " . json_encode($formData['lokasi_hunian']));

        // Update user
        $user->update([
            'name' => $formData['name'],
            'lokasi_hunian' => $formData['lokasi_hunian'],
        ]);

        // Update roles
        $user->syncRoles([$developerRole->name]);

        $this->info("   ✅ Updated user with new data");

        // Verify update
        $user->refresh();
        $this->line("   Updated lokasi hunian: " . json_encode($user->lokasi_hunian));
        $this->line("   Updated location names: " . json_encode($user->lokasi_hunian_names));

        $this->newLine();
        $this->info("🎯 Testing completed successfully!");
        $this->line("");
        $this->comment("💡 Test user created with email: {$testEmail}");
        $this->comment("💡 You can now test the form in the admin panel");

        return 0;
    }
}
