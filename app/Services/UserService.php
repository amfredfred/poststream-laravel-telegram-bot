<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class UserService {

    public static  function getUsersLast30Days() {
        $date = Carbon::now()->subDays( 30 );
        // Get date 30 days ago
        return User::where( 'updated_at', '>=', $date )->get();
    }

    public static function getUsersLast7Days() {
        $date = Carbon::now()->subDays( 7 );
        // Get date 7 days ago
        return User::where( 'updated_at', '>=', $date )->get();
    }

    public static function getUsersLast24Hours() {
        $date = Carbon::now()->subHours( 24 );
        // Get date 24 hours ago
        return User::where( 'updated_at', '>=', $date )->get();
    }

    public static function getLastUser() {
        return User::orderBy( 'updated_at', 'desc' )->first();
    }
}
