<?php
namespace App\Services\Tasks;

use App\Models\Challenge;
use App\Models\Activity;
use App\Enums\ActivityStatusEnum;
use App\Exceptions\TaskNotCompletedException;
use App\Helpers\BunzillaHelper;
use App\Interfaces\ChallengeHandlerInterface;
use App\Services\TaskService;

class BoostChannel implements ChallengeHandlerInterface {
    public static function handle( Challenge $challenge, Activity $task, $userId ) {
        $isBoostedChat = BunzillaHelper::checkChapBoost( $userId, $challenge->channel_id );
        if ( $isBoostedChat ) {
            $task = TaskService::markTaskCompleted( $task );
            $task->name = $challenge->name;
            $task->description = $challenge->description;
            $task->type = $challenge->type;
            $task->url = $challenge->url;
            $task->is_achievement = $challenge->isAchievement();
            // $message = 'Channel boosted successfully! Challenge completed.';
            return [ $task, '' ];
        } else {
            throw new TaskNotCompletedException( 'Challenge is not completed.' );
        }
    }
}
