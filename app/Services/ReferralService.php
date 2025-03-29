<?php

namespace App\Services;

use App\Enums\ActivityStatusEnum;
use App\Enums\RankEnum;
use App\Enums\TransactionTypeEnum;
use App\Helpers\BunzillaHelper;
use App\Models\Referral;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ReferralService {

    /**
    * Get home data related to referrals.
    *
    * @return array
    */
    public static function getHomeData( ?User $user = null ): array {
        // Retrieve the referral data you need
        $user = $user?? BunzillaHelper::tg_user();
        $referralBonuses = 0;
        $referrals = Referral::select(
            'referrals.created_at as join_date',
            'users.balance',
            'users.full_name',
            'users.uid as referral_id',
            'users.rank',
            'check_ins.last_check_in_date as last_checkin_date'
        )
        ->join( 'users', 'referrals.referral_id', 'users.id' )
        ->join( 'check_ins', 'users.id', 'check_ins.user_id' )
        ->where( 'referrer_id', $user->id )->get()
        ->map( function ( $ref ) {
            $re = $ref->toArray();
            $rank_prop = RankEnum::getRankProperties()[RankEnum::fromValue( $ref[ 'rank' ] )->key];
            $re['my_rank'] = $rank_prop;
            return $re;
        } );

        $bonus_points = config('airdrop_bot.referrals.bonus_points');
        $max_referrals_per_user = config('airdrop_bot.referrals.max_referrals_per_user');
        $score_percentage = config('airdrop_bot.referrals.score_percentage');
        $referrer = $user->referrer()->with('checkin')->first();

        return [
            'list' => $referrals,
            'referrer' => $referrer,
            'total_bonus' => $referralBonuses,
            'bonus_points' => $bonus_points,
            'max_referrals_per_user' => $max_referrals_per_user,
            'score_percentage' => $score_percentage,
            'total_referrals' => $user->downlines()->count()
        ];
    }

    /**
    * Create a referral record and update user balances and ranks if applicable.
    *
    * @param int $referrerId
    * @param int $referralId
    * @param float $rewardAmount
    * @return Referral|null
    */
    public static function createReferral(string $referrerUId, string $referralUId) {
        try {
            // Find the users involved in the referral
            $referrer = User::where('uid', $referrerUId)->firstOrFail();
            $referral = User::where('uid', $referralUId)->firstOrFail();

            // Prevent older users from being referred by newer users
            if ($referral->created_at < $referrer->created_at) {
                // Log::channel('telegram')->info('Referral skipped: Referrer is newer than the referred user', [
                //     'referrer_id' => $referrer->id,
                //     'referral_id' => $referral->id,
                //     'referrer_created_at' => $referrer->created_at,
                //     'referral_created_at' => $referral->created_at,
                // ]);
                return null; // Ignore the referral
            }

            // Check for duplicate referral
            if (Referral::where('referrer_id', $referrer->id)->where('referral_id', $referral->id)->exists()) {
                return null; // Skip duplicate referral
            }

             // Check for duplicate referral
            if (Referral::where('referral_id', $referrer->id)->where('referrer_id', $referral->id)->exists()) {
                return null; // Skip duplicate referral
            }

            // Create the referral record
            $referralRecord = Referral::create([
                'referrer_id' => $referrer->id,
                'referral_id' => $referral->id,
            ]);

            return $referralRecord;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::channel('telegram')->warning('Referral creation failed: User not found', [
                'referrer_uid' => $referrerUId,
                'referral_uid' => $referralUId,
            ]);
            return null;
        } catch (\Throwable $th) {
            Log::channel('telegram')->error('Failed to create referral', [
                'referrer_uid' => $referrerUId,
                'referral_uid' => $referralUId,
                'error' => $th->getMessage(),
            ]);
            return null;
        }
    }



    /**
    * Get the total reward for a user based on their referrals.
    *
    * @param int $referrerId
    * @return float
    */
    public static function getTotalReward( int $referrerId ): float {
        return Referral::where( 'referrer_id', $referrerId )->sum( 'reward_amount' );
    }

      /**
     * Award bonuses to the upline hierarchy of the user.
     *
     * @param string $referralUId
     * @param float $initialBonus
     * @return void
     */
    public static function awardUpline($fromUserId, User $currentReferral, float $initialBonus): void {
        if (!$currentReferral) {
            Log::error("Referrer with UID {___} not found.");
            return;
        }
        $uplineLevels = config('airdrop_bot.referrals.upline_levels', 3);
        $percentageDecay = config('airdrop_bot.referrals.upline_decay_percentage', 60);
        $scorePercentage = config('airdrop_bot.referrals.score_percentage', 0);
        $currentBonus = $initialBonus * ($scorePercentage / 100);
        for ($level = 1; $level <= $uplineLevels; $level++) {
            $referrer = $currentReferral;
            if (!$referrer) {
                break;
            }
            $rewardAmount = $currentBonus * ($percentageDecay / 100);
            Transaction::create([
                'from_user_id' => $fromUserId,
                'to_user_id' => $referrer->id,
                'amount' => $rewardAmount,
                'type' => TransactionTypeEnum::Rewards,
                'status' => ActivityStatusEnum::Completed,
                'description' => "Reward from referral level {$level}."
            ]);
            $currentReferral = $referrer->referrer;
            $currentBonus = $rewardAmount;
        }
    }
}
