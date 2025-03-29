<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class UserService {

    // Original methods to get users ( if needed )
    public static function getUsersLast30Days() {
        $date = Carbon::now()->subDays( 30 );
        return User::where( 'updated_at', '>=', $date )->get();
    }

    public static function getUsersLast7Days() {
        $date = Carbon::now()->subDays( 7 );
        return User::where( 'updated_at', '>=', $date )->get();
    }

    public static function getUsersLast24Hours() {
        $date = Carbon::now()->subHours( 24 );
        return User::where( 'updated_at', '>=', $date )->get();
    }

    public static function getLastUser() {
        return User::orderBy( 'updated_at', 'desc' )->first();
    }

    // New methods that return only `chat_id`s with non-overlapping periods

    public static function getUsersLast24HoursChatIds() {
        $date = Carbon::now()->subHours( 24 );
        return User::where( 'updated_at', '>=', $date )
        ->groupBy( 'chat_id' )
        ->pluck( 'chat_id' )
        ->toArray();
    }

    public static function getUsersLast7DaysChatIds() {
        $dateStart = Carbon::now()->subDays( 7 );
        $dateEnd = Carbon::now()->subHours( 24 );

        return User::whereBetween( 'updated_at', [ $dateStart, $dateEnd ] )
        ->groupBy( 'chat_id' )
        ->pluck( 'chat_id' )
        ->toArray();
    }

    public static function getUsersLast30DaysChatIds() {
        $dateStart = Carbon::now()->subDays( 30 );
        $dateEnd = Carbon::now()->subDays( 1 );

        return User::whereBetween( 'updated_at', [ $dateStart, $dateEnd ] )
        ->groupBy( 'chat_id' )
        ->pluck( 'chat_id' )
        ->toArray();
    }
}
