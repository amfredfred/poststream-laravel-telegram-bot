<?php

namespace App\Services;

use App\Models\User;
use App\Enums\RankEnum;
use App\Helpers\BunzillaHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RankService {

    /**
    * Get rank-related data for the rank home screen.
    *
    * @return array
    */
  public static function getRankHomeData($limit = 100){
        // Fetch users based on balance, achievements (via activities), and referrals
        $leaders = User::select(
                'users.uid',
                'users.full_name',
                'users.rank',
                'users.balance',
                DB::raw('COUNT(DISTINCT referrals.id) as referrals_count'), // Count distinct referrals
                DB::raw('SUM(CASE WHEN activities.is_achievement = true THEN 1 ELSE 0 END) as achievements_count') // Conditional aggregation
            )
            ->leftJoin('referrals', 'users.id', '=', 'referrals.referrer_id') // Left join to include all users
            ->leftJoin('activities', 'users.id', '=', 'activities.user_id') // Left join to include all activities
            ->groupBy('users.uid', 'users.full_name', 'users.rank', 'users.balance')
            ->orderByDesc('achievements_count') // Order by achievements count
            ->orderByDesc('referrals_count') // Order by referrals count
            ->orderByDesc('balance') // Order by balance
            ->limit($limit)
            ->get()
            ->map(function ($ref) {
                $re = $ref->toArray();
                $rank_prop = RankEnum::getRankProperties()[RankEnum::fromValue($ref['rank'])->key];
                $re['my_rank'] = $rank_prop;
                return $re;
            })
            ->toArray();

        // Get the user's rank and position
        $user = BunzillaHelper::tg_user();
        $my_rank = $user->rank;

        $position = User::where('rank', $my_rank)
            ->orderBy('updated_at')
            ->pluck('id')
            ->search($user->id) + 1;

        $total_users = User::count();
        $rank_prop = RankEnum::getRankProperties()[RankEnum::fromValue($my_rank)->key];

        return [
            'my_rank' => $rank_prop,
            'leaders' => $leaders, // List of top users based on balance, referrals, and achievements
            'ranks' => RankEnum::getAllRankDetails(),
            'total_users' => $total_users,
            'number_on_the_rank' => $position,
        ];
    }


    public static function getRankProperties(string $rank){
        try {
            $rank_prop = RankEnum::getRankProperties()[RankEnum::fromValue($rank)->key];
            return $rank_prop;
        } catch (\Throwable $th) {

        }
    }


    /**
     * Check and update the user's rank based on their performance.
    *
    * @param User $user
    */
    public static function checkAndUpdateRank( User $user ) {
        $totalPoints = $user->balance;
        $totalReferrals = $user->referrals()->count();
        $currentRank = Str::upper( $user->rank );
        $rankOrder = RankEnum::getRankOrder();
        $ranks = RankEnum::getRankProperties();
        foreach ( $rankOrder as $rank ) {
            $rankProperties = $ranks[ $rank ];
            if (
                ( $totalPoints >= $rankProperties[ 'points_required' ] &&
                $totalReferrals >= $rankProperties[ 'referrals_required' ] &&
                $totalPoints >= $rankProperties[ 'balance_required' ] )
                && $rank !== $currentRank
                && self::isHigherRank( $rank, $currentRank )
            ) {
                $user->rank = $rank;
                $user->save();
                break;
            }
        }
    }

    /**
    * Helper function to check if the new rank is higher than the current rank.
    *
    * @param string $newRank
    * @param string $currentRank
    * @return bool
    */
    private static function isHigherRank( $newRank, $currentRank ) {
        // Rank order, with higher ranks having a larger index value
        $rankOrder = RankEnum::getRankOrder();
        return array_search( $newRank, $rankOrder ) > array_search( $currentRank, $rankOrder );
    }

    /**
    * Get the top 100 users sorted by rank.
    * @param number $limit
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public static function getAllUsersSortedByRank( $limit = 100 ) {
        $rankOrder = RankEnum::getRankOrder();

        return User::whereIn( 'rank', $rankOrder )
        ->orderByRaw( "FIELD(rank, '".implode( "','", $rankOrder )."')" )
        ->limit( $limit )
        ->get();
    }

    /**
    * Get users by a specific rank, ordered by balance.
    *
    * @param string $rank
    * @param int $limit
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public static function getUsersByRankOrderedByBalance( string $rank, int $limit = 100 ) {
        return User::where( 'rank', $rank )
        ->orderBy( 'balance', 'desc' )
        ->limit( $limit )
        ->get();
    }

    /**
    * Get users within multiple ranks, ordered by balance.
    *
    * @param array $ranks
    * @param int $limit
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public static function getUsersByRanksOrderedByBalance( array $ranks, int $limit = 100 ) {
        return User::whereIn( 'rank', $ranks )
        ->orderBy( 'balance', 'desc' )
        ->limit( $limit )
        ->get();
    }

    /**
    * Get users by rank, sorted by balance and total referrals.
    *
    * @param string $rank
    * @param int $limit
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public static function getUsersByRankSortedByBalanceAndReferrals( string $rank, int $limit = 100 ) {
        return User::where( 'rank', $rank )
        ->withCount( 'referrals' )
        ->orderBy( 'balance', 'desc' )
        ->orderBy( 'referrals_count', 'desc' )
        ->limit( $limit )
        ->get();
    }

    /**
    * Get users across all ranks, ordered by rank and balance.
    *
    * @param int $limit
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public static function getAllUsersOrderedByRankAndBalance( int $limit = 100 ) {
        $rankOrder = RankEnum::getRankOrder();

        return User::whereIn( 'rank', $rankOrder )
        ->orderByRaw( "FIELD(rank, '".implode( "','", $rankOrder )."')" )
        ->orderBy( 'balance', 'desc' )
        ->limit( $limit )
        ->get();
    }

}
