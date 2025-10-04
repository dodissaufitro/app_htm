<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateAdminStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:admin-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update admin user allowed status with new status codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = \App\Models\User::where('email', 'admin@gmail.com')->first();

        if ($user) {
            $newStatusCodes = ['-1', '0', '1', '2', '3', '4', '5', '6', '8', '9', '10', '11', '12', '15', '16', '17', '18', '19', '20'];
            $user->allowed_status = json_encode($newStatusCodes);
            $user->save();

            $this->info('✅ Admin user allowed_status updated with new status codes:');
            $this->line(implode(', ', $newStatusCodes));
        } else {
            $this->error('❌ Admin user not found!');
        }
    }
}
