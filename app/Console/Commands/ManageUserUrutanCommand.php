<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ManageUserUrutanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:urutan 
                            {action : Action to perform (list|set|reset|workflow)}
                            {--user-id= : User ID to update}
                            {--urutan= : Urutan value to set}
                            {--email= : User email to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage user urutan for developer workflow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                $this->listUsers();
                break;
            case 'set':
                $this->setUserUrutan();
                break;
            case 'reset':
                $this->resetUrutan();
                break;
            case 'workflow':
                $this->showWorkflow();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->line('Available actions: list, set, reset, workflow');
                return 1;
        }

        return 0;
    }

    private function listUsers()
    {
        $this->info('All Users with Urutan:');
        $this->line('');

        $users = User::orderBy('urutan', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'urutan']);

        $headers = ['ID', 'Name', 'Email', 'Urutan', 'Status'];
        $rows = [];

        foreach ($users as $user) {
            $status = $user->urutan > 0 ? 'In Workflow' : 'Not in Workflow';
            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->urutan,
                $status
            ];
        }

        $this->table($headers, $rows);
    }

    private function setUserUrutan()
    {
        $userId = $this->option('user-id');
        $email = $this->option('email');
        $urutan = $this->option('urutan');

        if (!$userId && !$email) {
            $this->error('Please provide either --user-id or --email');
            return;
        }

        if (!$urutan) {
            $this->error('Please provide --urutan value');
            return;
        }

        $user = $userId ? User::find($userId) : User::where('email', $email)->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        // Check if urutan is already taken
        if ($urutan > 0) {
            $existingUser = User::where('urutan', $urutan)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                $this->warn("Urutan {$urutan} is already taken by {$existingUser->name}");
                if (!$this->confirm('Do you want to swap their positions?')) {
                    return;
                }

                // Swap positions
                $oldUrutan = $user->urutan;
                $user->update(['urutan' => $urutan]);
                $existingUser->update(['urutan' => $oldUrutan]);

                $this->info("Swapped positions:");
                $this->line("  {$user->name}: {$oldUrutan} → {$urutan}");
                $this->line("  {$existingUser->name}: {$urutan} → {$oldUrutan}");
                return;
            }
        }

        $oldUrutan = $user->urutan;
        $user->update(['urutan' => $urutan]);

        $this->info("Updated {$user->name}: urutan {$oldUrutan} → {$urutan}");
    }

    private function resetUrutan()
    {
        if (!$this->confirm('Are you sure you want to reset all urutan to 0?')) {
            return;
        }

        $count = User::where('urutan', '>', 0)->count();
        User::query()->update(['urutan' => 0]);

        $this->info("Reset urutan for {$count} users");
    }

    private function showWorkflow()
    {
        $this->info('Developer Workflow Users:');
        $this->line('');

        $workflowUsers = User::getDeveloperWorkflowUsers();

        if ($workflowUsers->isEmpty()) {
            $this->warn('No users in workflow (urutan > 0)');
            return;
        }

        $headers = ['Urutan', 'Name', 'Email', 'Next User'];
        $rows = [];

        foreach ($workflowUsers as $user) {
            $nextUser = $user->getNextUser();
            $nextUserName = $nextUser ? $nextUser->name : 'Last in sequence';

            $rows[] = [
                $user->urutan,
                $user->name,
                $user->email,
                $nextUserName
            ];
        }

        $this->table($headers, $rows);

        $this->line('');
        $this->info('Workflow Flow:');
        foreach ($workflowUsers as $index => $user) {
            $arrow = $index < $workflowUsers->count() - 1 ? ' → ' : '';
            $this->line("  {$user->urutan}. {$user->name}{$arrow}");
        }
    }
}
