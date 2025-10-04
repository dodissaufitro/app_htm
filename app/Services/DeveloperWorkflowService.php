<?php

namespace App\Services;

use App\Models\User;
use App\Models\DataPemohon;
use Illuminate\Support\Facades\Log;

class DeveloperWorkflowService
{
    /**
     * Get next user in developer workflow based on current user
     *
     * @param int $currentUserId
     * @return User|null
     */
    public function getNextDeveloperUser(int $currentUserId): ?User
    {
        $currentUser = User::find($currentUserId);

        if (!$currentUser || $currentUser->urutan <= 0) {
            // If current user is not in workflow, get first user
            return User::where('urutan', 1)->first();
        }

        return $currentUser->getNextUser();
    }

    /**
     * Get first user in developer workflow
     *
     * @return User|null
     */
    public function getFirstDeveloperUser(): ?User
    {
        return User::where('urutan', 1)->first();
    }

    /**
     * Auto assign pemohon to next user in workflow
     *
     * @param DataPemohon $dataPemohon
     * @param int|null $currentUserId
     * @return array
     */
    public function autoAssignToNextUser(DataPemohon $dataPemohon, ?int $currentUserId = null): array
    {
        try {
            if (!$currentUserId) {
                // Start from first user in workflow
                $nextUser = $this->getFirstDeveloperUser();
                $action = 'started_workflow';
            } else {
                // Get next user from current user
                $nextUser = $this->getNextDeveloperUser($currentUserId);
                $action = 'forwarded_to_next';
            }

            if (!$nextUser) {
                return [
                    'success' => false,
                    'message' => 'No next user found in workflow',
                    'next_user' => null
                ];
            }

            // Update pemohon assignment (assuming there's a field for this)
            // You might need to add assigned_to field to data_pemohon table
            // $dataPemohon->update(['assigned_to' => $nextUser->id]);

            Log::info("DeveloperWorkflowService: Auto-assigned pemohon to next user", [
                'pemohon_id' => $dataPemohon->id,
                'current_user_id' => $currentUserId,
                'next_user_id' => $nextUser->id,
                'next_user_name' => $nextUser->name,
                'next_user_urutan' => $nextUser->urutan,
                'action' => $action
            ]);

            return [
                'success' => true,
                'message' => "Pemohon assigned to {$nextUser->name} (urutan {$nextUser->urutan})",
                'next_user' => $nextUser,
                'action' => $action
            ];
        } catch (\Exception $e) {
            Log::error("DeveloperWorkflowService: Error in auto assignment", [
                'pemohon_id' => $dataPemohon->id,
                'current_user_id' => $currentUserId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error in auto assignment: ' . $e->getMessage(),
                'next_user' => null
            ];
        }
    }

    /**
     * Get workflow progress for a pemohon
     *
     * @param DataPemohon $dataPemohon
     * @param int|null $currentUserUrutan
     * @return array
     */
    public function getWorkflowProgress(DataPemohon $dataPemohon, ?int $currentUserUrutan = null): array
    {
        $workflowUsers = User::getDeveloperWorkflowUsers();
        $totalSteps = $workflowUsers->count();

        if ($totalSteps === 0) {
            return [
                'total_steps' => 0,
                'current_step' => 0,
                'progress_percentage' => 0,
                'steps' => []
            ];
        }

        $currentStep = $currentUserUrutan ?? 0;
        $progressPercentage = $totalSteps > 0 ? round(($currentStep / $totalSteps) * 100) : 0;

        $steps = [];
        foreach ($workflowUsers as $user) {
            $status = 'pending';
            if ($user->urutan < $currentStep) {
                $status = 'completed';
            } elseif ($user->urutan == $currentStep) {
                $status = 'current';
            }

            $steps[] = [
                'urutan' => $user->urutan,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'status' => $status
            ];
        }

        return [
            'total_steps' => $totalSteps,
            'current_step' => $currentStep,
            'progress_percentage' => $progressPercentage,
            'steps' => $steps
        ];
    }

    /**
     * Check if user can approve at their level
     *
     * @param User $user
     * @param DataPemohon $dataPemohon
     * @return bool
     */
    public function canUserApprove(User $user, DataPemohon $dataPemohon): bool
    {
        // Basic check - user must be in workflow
        if ($user->urutan <= 0) {
            return false;
        }

        // Add more business logic here:
        // - Check if previous steps are completed
        // - Check user permissions
        // - Check pemohon status requirements

        return true;
    }

    /**
     * Get workflow summary
     *
     * @return array
     */
    public function getWorkflowSummary(): array
    {
        $workflowUsers = User::getDeveloperWorkflowUsers();

        $summary = [
            'total_users' => $workflowUsers->count(),
            'users' => []
        ];

        foreach ($workflowUsers as $user) {
            $summary['users'][] = [
                'urutan' => $user->urutan,
                'name' => $user->name,
                'email' => $user->email,
                'is_first' => $user->isFirstInSequence(),
                'is_last' => $user->isLastInSequence(),
                'next_user' => $user->getNextUser()?->name,
                'previous_user' => $user->getPreviousUser()?->name
            ];
        }

        return $summary;
    }
}
