<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\Activity;
use App\Enums\ActivityStatusEnum;
// Import the Enum
use App\Helpers\BunzillaHelper;
use Database\Factories\ChallengeHandlerFactory;
use Illuminate\Support\Facades\Log;

class EarnService {

    /**
    * Get data for the earn home screen, including user participation and completion status.
    *
    * @return array
    */
    public static function getHomeData( $userId = null ) {
        try {
            $userId = $userId ?? BunzillaHelper::tg_user()->id;

            if ( !$userId ) {
                throw new \Exception( 'User ID is required but not provided.' );
            }

            $challenges = Challenge::select( 'challenges.*' )->get();
            $checkIn = CheckInService::getCheckInData( $userId );
            $activePool = PoolService::getActivePool();
            if ( $activePool ) {
                $poolClaimabablePoint = PoolService::getUserClaimablePoints( $activePool->id );
                $activePool[ 'remaining_points' ] = $poolClaimabablePoint [ 'remaining_points' ];
                $activePool[ 'min' ] = $poolClaimabablePoint [ 'min' ];
                $activePool[ 'max' ] = $poolClaimabablePoint [ 'max' ];
                $activePool['amount_claimed'] = $activePool->claims()->where('user_id', $userId)->first()->claimed_amount ?? 0;
                $activePool[ 'is_claimed' ] = $activePool->hasUserClaimed($userId);
            }
            $completedTasks = [];
            $nonCompletedTasks = [];

            foreach ( $challenges as $challenge ) {
                $userActivity = Activity::where( 'user_id', $userId )
                ->where( 'challenge_id', $challenge->id )
                ->first();

                $challenge[ 'challenge_id' ] = $challenge->id;
                $challenge[ 'is_achievement' ] = $challenge->isAchievement();

                if ( !$userActivity ) {
                    $nonCompletedTasks[] = $challenge;
                    continue;
                }

                $challenge->points = $userActivity->points;
                $challenge->status = $userActivity->status;
                $challenge->completed_at = $userActivity->completed_at;

                if ( $userActivity->status  == ActivityStatusEnum::Completed ) {
                    $completedTasks[] = $challenge;
                } else {
                    $nonCompletedTasks[] = $challenge;
                }
            }

            $tasks = array_merge( $completedTasks, $nonCompletedTasks );
        } catch ( \Exception $e ) {
            Log::error( 'Error fetching home data', [ 'error' => $e->getMessage() ] );
            return [
                'checkin' => null,
                'tasks' => []
            ];
        }

        return [
            'checkin' => $checkIn,
            'tasks' => $tasks,
            'pool_claim'=>  $activePool
        ];
    }

    public static function doTask( Challenge $challenge ) {
        $user = BunzillaHelper::tg_user();
        $task = $challenge->activities()->where( 'user_id', $user->id )->first();

        // Check if the task exists and is completed
        if ( $task && $task->status == ActivityStatusEnum::Completed ) {
            return [ $task, 'Task already completed' ];
        }

        // Create a new task if none exists or handle the current in-progress task
        if ( !$task ) {
            $task = TaskService::createTask( $user->id, $challenge->id, $challenge->points );
        }

        // Process the task using the appropriate handler
        $handler = ChallengeHandlerFactory::createHandler( $challenge->type );

        if ( !$handler ) {
            throw new \Exception( "Handler for challenge type {$challenge->type} not found." );
        }

        return $handler::handle( $challenge, $task, $user->user_id );
    }

}
