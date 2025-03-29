<?php
namespace App\Services\Tasks;

use App\Enums\ActivityStatusEnum;
use App\Interfaces\ChallengeHandlerInterface;
use App\Models\Challenge;
use App\Models\Activity;
use App\Services\TaskService;

class RankUp implements ChallengeHandlerInterface {
    public static function handle( Challenge $challenge, Activity $task, $userId ) {
        $task = TaskService::markTaskCompleted( $task );
        $task->is_achievement = $challenge->isAchievement();
        $message = 'You have ranked up! Challenge completed.';
        return [ $task, $message ];
    }
}
