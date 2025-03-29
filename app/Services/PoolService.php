<?php

namespace App\Services;

use App\Models\Pool;
use App\Models\PoolClaim;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

namespace App\Services;

use App\Enums\ActivityStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Pool;
use App\Models\PoolClaim;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PoolService
{
    /**
     * Create a new pool, but only if no active pool exists.
     */
    public static function createPool(string $name, float $initialAmount, Carbon $startTime, Carbon $endTime): Pool {
        $existingPool = self::getActivePool();
        if ($existingPool) {
            throw new \Exception('There is already an active pool.');
        }

        return Pool::create([
            'name' => $name,
            'initial_amount' => $initialAmount,
            'current_amount' => $initialAmount,
            'eposh' => time(),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }

    /**
     * Claim points from a pool.
     */
    public static function claimPoints(int $poolId, int $userId): mixed {
        $pool = self::getActivePool($poolId);
        if ($pool->hasUserClaimed($userId)) {
            throw new \Exception('You have already claimed from this pool. Only one claim is allowed.');
        }

        $claimableRange = self::calculateClaimablePoints($pool);
        $randomClaim = min(mt_rand($claimableRange['min'], $claimableRange['max']), $pool->current_amount);

        if ($randomClaim <= 0) {
            throw new \Exception('No BNZ left to claim.');
        }

        // Deduct points and record the claim
        DB::transaction(function () use ($pool, $userId, $randomClaim) {
            $pool->update(['current_amount' => $pool->current_amount - $randomClaim]);

            PoolClaim::create([
                'pool_id' => $pool->id,
                'user_id' => $userId,
                'claimed_amount' => $randomClaim,
            ]);

            $description = "Claimed {$randomClaim} BNZ from event - '{$pool->name}'.";

            Transaction::create( [
                'to_user_id' => $userId,
                'amount' => $randomClaim,
                'type' => TransactionTypeEnum::GameplayReward,
                'status' => ActivityStatusEnum::Completed,
                'description' =>  $description,
            ] );
        });

        $pool[ 'remaining_points' ] = $pool->current_amount;
        $pool[ 'min' ] = $claimableRange [ 'min' ];
        $pool[ 'max' ] = $claimableRange [ 'max' ];
        $pool['amount_claimed'] = $pool->claims()->where('user_id', $userId)->first()->claimed_amount ?? 0;
        $pool[ 'is_claimed' ] = $pool->hasUserClaimed($userId);

        return [$pool];
    }

    /**
     * Get the status of a pool.
     */
    public static function getPoolStatus(int $poolId): array {
        $pool = Pool::findOrFail($poolId);

        return [
            'name' => $pool->name,
            'eposh' => $pool->eposh,
            'initial_amount' => $pool->initial_amount,
            'current_amount' => $pool->current_amount,
            'start_time' => $pool->start_time,
            'end_time' => $pool->end_time,
        ];
    }

    /**
     * Get the claimable points range for a user.
     */
    public static function getUserClaimablePoints(int $poolId): array {
        $pool = self::getActivePool($poolId);
        return self::calculateClaimablePoints($pool);
    }

    /**
     * Helper method to fetch an active pool by ID or return the latest active one.
     */
    public static function getActivePool(int $poolId = null): ?Pool {
        $now = Carbon::now();
        $pool = $poolId ? Pool::findOrFail($poolId) : Pool::where('start_time', '<=', $now)->where('end_time', '>=', $now)->latest()->first();

        if ($pool && ($now->lessThan($pool->start_time) || $now->greaterThan($pool->end_time))) {
            return null;
        }

        return $pool;
    }

    /**
     * Helper method to calculate claimable points, with decreasing claimable amount over time.
     */
    private static function calculateClaimablePoints(Pool $pool): array {
        $now = Carbon::now();
        $totalDuration = Carbon::createFromDate($pool->start_time)->diffInSeconds($pool->end_time);
        $elapsedTime = $now->diffInSeconds($pool->start_time);

        // Ensure elapsed time doesn't exceed the total duration
        $elapsedTime = min($elapsedTime, $totalDuration);

        // Calculate the elapsed percentage
        $elapsedPercentage = abs($elapsedTime / $totalDuration);

        // Calculate the claimable points based on the elapsed percentage (increasing)
        $claimablePoints = ($pool->current_amount - ($pool->current_amount * $elapsedPercentage));
        // Ensure min and max values are set based on the claimable points
        return [
            'remaining_points' => $claimablePoints,
            'min' => max(1, intval(0.005 * $claimablePoints)),  // Minimum 0.1% of claimable points
            'max' => max(5, intval(0.03 * $claimablePoints)),  // Maximum 3% of claimable points
        ];
    }
}
