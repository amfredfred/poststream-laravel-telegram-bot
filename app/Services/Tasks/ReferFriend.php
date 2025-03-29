<?php
namespace App\Services\Tasks;

use App\Enums\ActivityStatusEnum;
use App\Exceptions\TaskNotCompletedException;
use App\Interfaces\ChallengeHandlerInterface;
use App\Models\Challenge;
use App\Models\Activity;
use App\Models\User;
use App\Services\TaskService;

class ReferFriend implements ChallengeHandlerInterface {

    public static function handle( Challenge $challenge, Activity $task, $userId ) {
        $referrals = self::countReferrals( $challenge, $userId );
        if ( $referrals >= $challenge->referrals_required ) {
            $task = TaskService::markTaskCompleted( $task );
            $task->name = $challenge->name;
            $task->description = $challenge->description;
            $task->type = $challenge->type;
            $task->url = $challenge->url;
            $task->is_achievement = $challenge->isAchievement();
            // $message = 'You have successfully referred enough friends! Challenge completed.';
            return [ $task, '' ];
        } else {
            $remaining = $challenge->referrals_required - $referrals;
            throw new TaskNotCompletedException( "Refer {$remaining} more friends to complete this challenge." );
        }
    }

    private static function countReferrals( Challenge $challenge, $userId ): int {
        $user = User::where( 'user_id', $userId )->firstOrFail();
        $referralsCount = $user->referrals()
        ->where( 'created_at', '>=', $challenge->created_at )
        ->count();
        return $referralsCount;
    }
}
