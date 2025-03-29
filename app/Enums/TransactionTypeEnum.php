<?php declare( strict_types = 1 );

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
* @method static static Credit()
* @method static static Debit()
* @method static static Rewards()
* @method static static DailyBonus()
* @method static static StreakBonus()
* @method static static ReferralBonus()
* @method static static GameplayReward()
* @method static static TaskCompletion()
*/
final class TransactionTypeEnum extends Enum {
    const Credit = 'Credit';
    const Debit = 'Debit';
    const Rewards = 'Rewards';
    const DailyBonus = 'DailyBonus';
    const StreakBonus = 'StreakBonus';
    const ReferralBonus = 'ReferralBonus';
    const GameplayReward = 'GameplayReward';
    const TaskCompletion = 'TaskCompletion';

    /**
    * Get the label for each rank.
    */

    public function label(): string {
        return $this->value;
    }
}
