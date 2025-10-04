<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserUrutanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Setting up urutan for developer workflow...');

        // Update urutan untuk user yang sudah ada
        // Contoh alur tahap developer:
        $developerStages = [
            1 => 'Verifikator Awal',
            2 => 'Developer/Pengembang',
            3 => 'Bank Analisis',
            4 => 'Supervisor',
            5 => 'Manager',
            // Tambahkan sesuai kebutuhan
        ];

        $this->command->info('Available developer stages:');
        foreach ($developerStages as $urutan => $stage) {
            $this->command->line("  {$urutan}. {$stage}");
        }

        // Update beberapa user dengan urutan contoh
        $users = User::all();

        if ($users->count() > 0) {
            // Set urutan untuk user pertama sebagai verifikator awal
            $firstUser = $users->first();
            $firstUser->update(['urutan' => 1]);
            $this->command->info("Set {$firstUser->name} as urutan 1 (Verifikator Awal)");

            // Set urutan untuk user lain jika ada
            if ($users->count() > 1) {
                $secondUser = $users->skip(1)->first();
                $secondUser->update(['urutan' => 2]);
                $this->command->info("Set {$secondUser->name} as urutan 2 (Developer/Pengembang)");
            }

            if ($users->count() > 2) {
                $thirdUser = $users->skip(2)->first();
                $thirdUser->update(['urutan' => 3]);
                $this->command->info("Set {$thirdUser->name} as urutan 3 (Bank Analisis)");
            }

            // Set sisa user dengan urutan 0 (tidak dalam workflow)
            User::whereNotIn('id', $users->take(3)->pluck('id'))
                ->update(['urutan' => 0]);
        }

        // Tampilkan hasil
        $workflowUsers = User::where('urutan', '>', 0)
            ->orderBy('urutan', 'asc')
            ->get(['id', 'name', 'email', 'urutan']);

        $this->command->info('Current developer workflow users:');
        foreach ($workflowUsers as $user) {
            $stageName = $developerStages[$user->urutan] ?? 'Custom Stage';
            $this->command->line("  {$user->urutan}. {$user->name} ({$user->email}) - {$stageName}");
        }

        $this->command->info('Urutan setup completed!');
    }
}
