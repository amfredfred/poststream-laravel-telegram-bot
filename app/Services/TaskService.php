<?php

namespace App\Services;

use App\Models\Activity;
use App\Enums\ActivityStatusEnum;
use Carbon\Carbon;

class TaskService {

    public static function createTask( $userId, $challengeId, $points ) {
        return Activity::create( [
            'user_id' => $userId,
            'challenge_id' => $challengeId,
            'points' => $points,
            'status' => ActivityStatusEnum::InProgress,
        ] );
    }

    public static function updateTaskStatus( Activity $task, $status, $points, $completedAt = null ) {
        return $task->update( [
            'status' => $status,
            'points' => $points,
            'completed_at' => $completedAt ?? now(),
        ] );
    }

    public static function markTaskCompleted( Activity $task ):Activity {
        $task->status = ActivityStatusEnum::Completed;
        $task->completed_at = Carbon::now();
        $task->save();
        return $task;
    }
}
