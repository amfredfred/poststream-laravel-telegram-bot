<?php

namespace App\Services;

use App\Helpers\BunzillaHelper;
use App\Models\User;

class SettingsService {
    public static function settingsHomeData() {
        $data[ 'totalEarned' ] = ( int )User::sum( 'balance' );
        $data[ 'totalTarget' ] = ( int )config( 'airdrop_bot.xp_thresshold_tosnapshot' );
        $data[ 'isSnapshotReached' ] = $data[ 'totalEarned' ] >= $data[ 'totalTarget' ];
        $data[ 'isOfficeEnabled' ] = ( bool ) config( 'airdrop_bot.office.enabled' );
        $data[ 'news_channel_url' ] = BunzillaHelper::getNewsChannelURL();
        return $data;
    }
}
