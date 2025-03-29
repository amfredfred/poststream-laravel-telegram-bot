<?php

namespace App\Services\Tasks;

use App\Exceptions\TaskNotCompletedException;
use App\Interfaces\ChallengeHandlerInterface;
use App\Models\Transaction;
use App\Services\TaskService;

class EarnPoint implements ChallengeHandlerInterface {
    public static function handle( $challenge, $task, $userId ) {
        // Calculate points earned after the challenge creation date
        $pointsEarned = Transaction::where( 'to_user_id', $userId )
        ->where( 'created_at', '>', $challenge->created_at )
        ->sum( 'amount' );

        // Check if the user earned enough points to complete the challenge
        if ( $pointsEarned >= $challenge->points_required ) {
            // Mark the task as completed using a dedicated service
            $task = TaskService::markTaskCompleted( $task );
            $task->name = $challenge->name;
            $task->description = $challenge->description;
            $task->type = $challenge->type;
            $task->url = $challenge->url;
            $task->is_achievement = $challenge->isAchievement();

            return [
                $task,
                'You have earned enough points! Challenge completed.',
            ];
        }

        $remainingPoints = $challenge->points_required - $pointsEarned;
        throw new TaskNotCompletedException(
            "You need to earn {$remainingPoints} more points to complete this challenge.",
            422,
            [
                'points_earned' => $pointsEarned,
                'points_required' => $challenge->points_required,
            ]
        );
    }
}
