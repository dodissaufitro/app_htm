<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DaftarBank;
use Illuminate\Support\Facades\DB;

class CleanupDaftarBankTest extends Command
{
    protected $signature = 'cleanup:daftar-bank-test';
    protected $description = 'Clean up test data and create proper sample banks';

    public function handle()
    {
        $this->info('Cleaning up test data and creating sample banks...');
        $this->newLine();

        try {
            // Delete all existing banks
            DaftarBank::truncate();
            $this->info('✓ Cleared all existing bank data');

            // Create proper sample banks with duplicate codes
            $sampleBanks = [
                [
                    'kode_bank' => 'BCA',
                    'nama_bank' => 'Bank Central Asia - Jakarta Pusat',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BCA',
                    'nama_bank' => 'Bank Central Asia - Jakarta Timur',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BCA',
                    'nama_bank' => 'Bank Central Asia - Maintenance Branch',
                    'status' => 'maintenance'
                ],
                [
                    'kode_bank' => 'BNI',
                    'nama_bank' => 'Bank Negara Indonesia - Kantor Pusat',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BNI',
                    'nama_bank' => 'Bank Negara Indonesia - Cabang Surabaya',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BRI',
                    'nama_bank' => 'Bank Rakyat Indonesia - Kantor Pusat',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BRI',
                    'nama_bank' => 'Bank Rakyat Indonesia - Under Review',
                    'status' => 'pending'
                ],
                [
                    'kode_bank' => 'Mandiri',
                    'nama_bank' => 'Bank Mandiri - Kantor Pusat',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BSI',
                    'nama_bank' => 'Bank Syariah Indonesia - Jakarta',
                    'status' => 'active'
                ],
                [
                    'kode_bank' => 'BSI',
                    'nama_bank' => 'Bank Syariah Indonesia - Bandung',
                    'status' => 'active'
                ],
            ];

            foreach ($sampleBanks as $bankData) {
                $bank = DaftarBank::create($bankData);
                $this->info("✓ Created: {$bank->kode_bank} - {$bank->nama_bank} ({$bank->status})");
            }

            $this->newLine();

            // Display final results
            $allBanks = DaftarBank::orderBy('kode_bank')->orderBy('status')->get();
            $groupedBanks = $allBanks->groupBy('kode_bank');

            $this->info('Final Bank Structure:');
            foreach ($groupedBanks as $kode => $banks) {
                $this->line("Bank Code: $kode ({$banks->count()} branches)");
                foreach ($banks as $bank) {
                    $statusColor = match ($bank->status) {
                        'active' => 'info',
                        'maintenance' => 'comment',
                        'pending' => 'warn',
                        'inactive' => 'error',
                        default => 'line'
                    };
                    $this->$statusColor("  - {$bank->nama_bank} | Status: {$bank->status}");
                }
                $this->newLine();
            }

            $this->info('Summary:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Banks', DaftarBank::count()],
                    ['Unique Codes', DaftarBank::distinct('kode_bank')->count('kode_bank')],
                    ['Active Banks', DaftarBank::where('status', 'active')->count()],
                    ['Pending Banks', DaftarBank::where('status', 'pending')->count()],
                    ['Maintenance Banks', DaftarBank::where('status', 'maintenance')->count()],
                ]
            );

            $this->newLine();
            $this->info('✓ Sample bank data created successfully!');
            $this->info('✓ Bank codes can now be duplicated with different status values.');
        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
