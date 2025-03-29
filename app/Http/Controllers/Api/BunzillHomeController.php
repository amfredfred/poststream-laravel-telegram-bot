<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BunzillaHelper;
use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Services\CheckInService;
use App\Services\EarnService;
use App\Services\PoolService;
use App\Services\RankService;
use App\Services\ReferralService;
use App\Services\SettingsService;
use App\Services\UserService;

class BunzillHomeController extends Controller {

    public function tasks_home() {
        // $tasksData = TaskService::getHomeData();
        // return $this->success( $tasksData );
    }

    public function profile_home() {
        $profileData = UserService::getProfileData();
        return $this->success( $profileData );
    }

    public function transactions_history() {
        $histories = UserService::transactionsHistory();
        return $this->success( $histories );
    }

    public function invite_home() {
        $inviteData = ReferralService::getHomeData();
        return $this->success( $inviteData );
    }

    public function earn_home() {
        $earnData = EarnService::getHomeData();
        return $this->success( $earnData );
    }

    public function rank_home() {
        $rankData = RankService::getRankHomeData();
        return $this->success( $rankData );
    }

    public function settings_home() {
        $settingsData = SettingsService::settingsHomeData();
        return $this->success( $settingsData );
    }

    public function do_task( $challenge_id ) {
        $challenge = Challenge::findOrFail( $challenge_id );
        [ $task, $message ] = EarnService::doTask( $challenge );
        return $this->success( $task, $message );
    }

    public function do_checkin() {
        [ $checkin, $message ] = CheckInService::checkIn();
        // Corrected typo here
        return $this->success( $checkin, $message );
    }


    public function do_claim_from_pool( int | string $poolId ) {
        $userId = BunzillaHelper::tg_user()->id;
        try {
            [ $pool ] = PoolService::claimPoints( $poolId, $userId );
            return $this->success( $pool );
        } catch ( \Exception $e ) {
            return $this->error( $e->getMessage(), status: 400 );
        }
    }

    public function get_pool_status( int $poolId ) {
        $status = PoolService::getPoolStatus( $poolId );
        return $this->success( $status );
    }

    public function get_active_pool() {
        $activePool = PoolService::getActivePool();
        return $this->success( $activePool );
    }
}
