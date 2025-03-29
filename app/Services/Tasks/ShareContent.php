<?php
namespace App\Services\Tasks;

use App\Enums\ActivityStatusEnum;
use App\Interfaces\ChallengeHandlerInterface;
use App\Models\Challenge;
use App\Models\Activity;
use App\Services\TaskService;

class ShareContent implements ChallengeHandlerInterface {

    public static function handle( Challenge $challenge, Activity $task, $userId ) {
        $task = TaskService::markTaskCompleted( $task );
        $task->name = $challenge->name;
        $task->description = $challenge->description;
        $task->type = $challenge->type;
        $task->url = $challenge->url;
        $task->is_achievement = $challenge->isAchievement();
        return [ $task, 'Content shared successfully! Challenge completed.' ];
    }

}
