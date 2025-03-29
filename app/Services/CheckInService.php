<?php

namespace App\Services;

use App\Enums\ActivityStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Helpers\BunzillaHelper;
use App\Models\CheckIn;
use App\Models\Transaction;
use Carbon\Carbon;

class CheckInService {
    /**
    * Handle a user's check-in.
     *
     * @param int $userId
     * @return string
     */
   public static function checkIn($userId = null) {
        $userId = $userId ?? BunzillaHelper::tg_user()->id;
        $date = Carbon::now(); // Current time
        $streakDays = config('airdrop_bot.check_in.streak_days', 7);
        $checkInIntervalHours = config('airdrop_bot.check_in.check_in_interval_hours', 24);
        $checkInRecord = self::getCheckInData($userId);
        $inStreak = Carbon::parse($checkInRecord['next_check_in_date'])->addHours($checkInIntervalHours) > $date;
        if ($checkInRecord['is_checked_in']) {
            return [$checkInRecord, ''];
        }
        if ($inStreak) {
            $checkInRecord['streak']++;
        } else {
            $checkInRecord['streak'] = 1;
        }
        if ($checkInRecord['streak'] >= $streakDays) {
            $checkInRecord['claimed'] = true;
            $checkInRecord['streak'] = 1; // Reset streak
            $message = "Streak complete! Reward claimed.";
        } else {
            $checkInRecord['claimed'] = false;
            $message = "Streak updated. Keep going!";
        }
        $checkInRecord['last_check_in_date'] = $date;
        $checkInRecord['next_check_in_date'] = $date->copy()->addHours($checkInIntervalHours);
        $checking = CheckIn::where('id', $checkInRecord['id'])->first();
        $checking->last_check_in_date = $checkInRecord['last_check_in_date'];
        $checking->next_check_in_date = $checkInRecord['next_check_in_date'];
        $checking->streak = $checkInRecord['streak'];
        $checking->save();
        $checkInRecord['is_checked_in'] = true;
        return [$checkInRecord, ''];
    }


    /**
     * Get the user's check-in record.
    *
    * @param int $userId
    * @return CheckIn
    */
    public static function getCheckInRecord( int $userId ):CheckIn {
        return CheckIn::firstOrCreate( [ 'user_id' => $userId ] );
    }

    public static function getCheckInData( int $userId = null ) {
        // Retrieve or set default user ID
        $userId = $userId ?? BunzillaHelper::tg_user()->id;
        // Load check-in configuration values
        $streakDays = config( 'airdrop_bot.check_in.streak_days', 7 );
        $streakBonus = config( 'airdrop_bot.check_in.streak_bonus', 0 );
        $checkInBonus = config( 'airdrop_bot.check_in.check_in_bonus', 1 );
        $checkInMultiplier = config( 'airdrop_bot.check_in.multiplier', 1 );
        $checkInRecord = self::getCheckInRecord( $userId );
        $isEligibleForCheckIn = $checkInRecord->next_check_in_date < Carbon::now();
        $current_bonus = ( $checkInBonus * $checkInMultiplier ) * ( int )  $checkInRecord->streak ;
        $checkInData = array_merge( $checkInRecord->toArray(), [
            'daily_checkin_bonus' => $checkInBonus * $checkInMultiplier,
            'current_bonus' => $current_bonus,
            'streak_bonus' => $streakBonus,
            'streak_days' => $streakDays,
            'referral_bonus' => config( 'airdrop_bot.referrals.bonus_points' ),
            'is_checked_in' => !$isEligibleForCheckIn,
            'current_streak' => $checkInRecord->streak,
        ] );
        return $checkInData;
    }

    public static function handleCheckInUpdate( CheckIn $model ): void {
        $currentDate = Carbon::now();
        if ( $model->isDirty( 'last_check_in_date' ) && $model->last_check_in_date < $currentDate ) {
            $checkingData = self::getCheckInData( $model->user->id );
            $current_bonus = $checkingData[ 'current_bonus' ];
            $streak_days = $checkingData[ 'streak_days' ];
            Transaction::create( [
                'to_user_id' => $model->user_id,
                'amount' => $current_bonus,
                'type' => $model->streak >= $streak_days ? TransactionTypeEnum::StreakBonus() : TransactionTypeEnum::DailyBonus(),
                'status' => ActivityStatusEnum::Completed,
                'description' => $model->streak >= $streak_days ? 'Check-in streak bonus 🎉🎉.' : 'Check-in bonus.',
            ] );

            if ( $current_bonus > 0 && $model->user->referrer ) {
                ReferralService::awardUpline( $model->user->id, $model->user->referrer, $current_bonus );
            }
        }
    }

}
