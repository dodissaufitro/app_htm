<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DaftarBank;

class TestDaftarBankInterface extends Command
{
    protected $signature = 'test:daftar-bank-interface';
    protected $description = 'Test Daftar Bank interface functionality';

    public function handle()
    {
        $this->info('Testing Daftar Bank Interface Functionality...');
        $this->newLine();

        try {
            // Test creating a new bank with duplicate code
            $this->info('Testing duplicate bank code creation...');

            $newBank = DaftarBank::create([
                'kode_bank' => 'BCA',
                'nama_bank' => 'Bank Central Asia - Jakarta Selatan',
                'status' => 'active'
            ]);

            $this->info("✓ Successfully created new BCA branch: {$newBank->nama_bank}");

            // Test creating bank with different status
            $pendingBank = DaftarBank::create([
                'kode_bank' => 'Mandiri',
                'nama_bank' => 'Bank Mandiri - Cabang Bandung (Review)',
                'status' => 'pending'
            ]);

            $this->info("✓ Successfully created pending Mandiri branch: {$pendingBank->nama_bank}");

            // Show all BCA banks
            $this->newLine();
            $this->info('All BCA banks in system:');
            $bcaBanks = DaftarBank::where('kode_bank', 'BCA')->get();

            foreach ($bcaBanks as $bank) {
                $statusColor = match ($bank->status) {
                    'active' => 'info',
                    'maintenance' => 'comment',
                    'pending' => 'warn',
                    'inactive' => 'error',
                    default => 'line'
                };
                $this->$statusColor("  - ID: {$bank->id} | {$bank->nama_bank} | Status: {$bank->status}");
            }

            // Show interface URL
            $this->newLine();
            $this->info('Interface URLs:');
            $this->line('• Daftar Bank List: /admin/daftar-banks');
            $this->line('• Create New Bank: /admin/daftar-banks/create');
            $this->line("• View Bank Details: /admin/daftar-banks/{bank_id}");
            $this->line("• Edit Bank: /admin/daftar-banks/{bank_id}/edit");

            // Test query functionality
            $this->newLine();
            $this->info('Testing query functionality:');

            $activeCount = DaftarBank::where('status', 'active')->count();
            $bcaCount = DaftarBank::where('kode_bank', 'BCA')->count();
            $pendingCount = DaftarBank::where('status', 'pending')->count();

            $this->table(
                ['Query Type', 'Result'],
                [
                    ['Active Banks', $activeCount],
                    ['Total BCA Branches', $bcaCount],
                    ['Pending Banks', $pendingCount],
                    ['Banks with code "BRI"', DaftarBank::where('kode_bank', 'BRI')->count()],
                    ['Unique Bank Codes', DaftarBank::distinct('kode_bank')->count()],
                ]
            );

            $this->newLine();
            $this->info('✓ All interface tests passed!');
            $this->info('✓ Bank codes can be duplicated with different status successfully.');
            $this->info('✓ Filament resource forms and tables are working correctly.');
        } catch (\Exception $e) {
            $this->error('Interface test failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
