<?php
namespace App\Services\Tasks;

use App\Models\Challenge;
use App\Models\Activity;
use App\Exceptions\TaskNotCompletedException;
use App\Interfaces\ChallengeHandlerInterface;
use App\Services\TaskService;
use Throwable;

class WatchAds implements ChallengeHandlerInterface {
    public static function handle( Challenge $challenge, Activity $task, $userId ) {
        try {
            $task = TaskService::markTaskCompleted( $task );
            $task->name = $challenge->name;
            $task->description = $challenge->description;
            $task->type = $challenge->type;
            $task->url = $challenge->url;
            $task->is_achievement = $challenge->isAchievement();
            // $message = 'You have successfully watched the video! Challenge completed.';
            return [ $task, '' ];
        } catch( Throwable $th ) {
            throw new TaskNotCompletedException( 'Please watch the video to complete this challenge.' );
        }
    }
}
