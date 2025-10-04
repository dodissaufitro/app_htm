<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DaftarBank;

class TestDaftarBankDuplicate extends Command
{
    protected $signature = 'test:daftar-bank-duplicate';
    protected $description = 'Test creating duplicate bank codes with different status';

    public function handle()
    {
        $this->info('Testing Daftar Bank Duplicate Codes...');
        $this->newLine();

        try {
            // Test creating multiple banks with same code but different status
            $banks = [
                [
                    'kode_bank' => 'BCA',
                    'nama_bank' => 'Bank Central Asia - Jakarta',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BCA',
                    'nama_bank' => 'Bank Central Asia - Surabaya',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BCA',
                    'nama_bank' => 'Bank Central Asia - Maintenance',
                    'status' => 'maintenance'
                ],
                [
                    'kode_bank' => 'BNI',
                    'nama_bank' => 'Bank Negara Indonesia',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BNI',
                    'nama_bank' => 'Bank Negara Indonesia - Pending',
                    'status' => 'pending'
                ],
            ];

            $createdBanks = [];
            foreach ($banks as $bankData) {
                $bank = DaftarBank::create($bankData);
                $createdBanks[] = $bank;
                $this->info("✓ Created bank: {$bank->kode_bank} - {$bank->nama_bank} ({$bank->status})");
            }

            $this->newLine();
            $this->info('Displaying all banks by code:');

            // Group banks by code
            $allBanks = DaftarBank::orderBy('kode_bank')->orderBy('status')->get();
            $groupedBanks = $allBanks->groupBy('kode_bank');

            foreach ($groupedBanks as $kode => $banks) {
                $this->line("Bank Code: $kode");
                foreach ($banks as $bank) {
                    $statusColor = match ($bank->status) {
                        'active' => 'info',
                        'maintenance' => 'comment',
                        'pending' => 'warn',
                        'inactive' => 'error',
                        default => 'line'
                    };
                    $this->$statusColor("  - ID: {$bank->id} | {$bank->nama_bank} | Status: {$bank->status}");
                }
                $this->newLine();
            }

            // Test statistics
            $totalBanks = DaftarBank::count();
            $uniqueCodes = DaftarBank::distinct('kode_bank')->count('kode_bank');
            $activeBanks = DaftarBank::where('status', 'active')->count();

            $this->info('Statistics:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Banks', $totalBanks],
                    ['Unique Codes', $uniqueCodes],
                    ['Active Banks', $activeBanks],
                    ['BCA Variants', DaftarBank::where('kode_bank', 'BCA')->count()],
                    ['BNI Variants', DaftarBank::where('kode_bank', 'BNI')->count()],
                ]
            );

            $this->newLine();
            $this->info('✓ All tests passed! Bank codes can now be duplicated with different status.');
        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
