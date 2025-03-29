<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Enums\TransactionTypeEnum;
use App\Enums\ActivityStatusEnum;
use App\Helpers\BunzillaHelper;

class Referral extends Model {
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referral_id',
    ];

    /**
     * Get the user who made the referral.
     */
    public function referrer() {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who was referred.
     */
    public function referral() {
        return $this->belongsTo(User::class, 'referral_id');
    }

    protected static function boot() {
        parent::boot();

        static::created(function ($model) {
            $referral = $model->referral;
            $referrer = $model->referrer;

            if ($referral && $referrer) {
                $rewardAmount = BunzillaHelper::getReferralBonus(); // Assuming this is the referral bonus calculation
                Transaction::create([
                    'amount' => $rewardAmount,
                    'to_user_id' => $referrer->id,
                    'from_user_id' => $referral->id,
                    'type' => TransactionTypeEnum::ReferralBonus,
                    'status' => ActivityStatusEnum::Completed,
                    'description' => 'Referral bonus for referring '.$referral->uid,
                ]);
            } else {
                Log::warning('Referral or referrer data is missing.');
            }
        });
    }
}
