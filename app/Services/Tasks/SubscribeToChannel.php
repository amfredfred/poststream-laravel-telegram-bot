<?php
namespace App\Services\Tasks;

use App\Models\Challenge;
use App\Enums\ActivityStatusEnum;
use App\Exceptions\TaskNotCompletedException;
use App\Helpers\BunzillaHelper;
use App\Interfaces\ChallengeHandlerInterface;
use App\Models\Activity;
use App\Services\TaskService;

class SubscribeToChannel implements ChallengeHandlerInterface {
    public static function handle( Challenge $challenge, Activity $task, $userId ) {
        $isSubscribed = BunzillaHelper::checkSubscription( $userId, $challenge->channel_id );
        if ( $isSubscribed ) {
            $task = TaskService::markTaskCompleted( $task );
            $task->name = $challenge->name;
            $task->description = $challenge->description;
            $task->type = $challenge->type;
            $task->url = $challenge->url;
            $task->is_achievement = $challenge->isAchievement();
            return [ $task, '' ];
        } else {
            throw new TaskNotCompletedException( 'Please subscribe to the channel to complete this challenge.' );
        }
    }
}
