<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerifyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify status table data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $statuses = \App\Models\Status::orderBy('urut')->get();

        $this->info('📋 Status Data Summary:');
        $this->line('Total Status: ' . $statuses->count());
        $this->newLine();

        $headers = ['Kode', 'Urut', 'Nama Status', 'Keterangan'];
        $rows = [];

        foreach ($statuses as $status) {
            $rows[] = [
                $status->kode,
                $status->urut,
                $status->nama_status,
                \Illuminate\Support\Str::limit($status->keterangan, 50)
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('✅ Status data verification complete!');
    }
}
