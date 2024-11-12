<?php

return [
    'private_channel_id' => env( 'PRIVATE_CHANNEL_ID' ),
    'finance_channel_id' => env( 'FINANCE_CHANNEL_ID' ),
    'bot_username' => env( 'BOT_USERNAME', '' ),
    'bot_url' => env( 'BOT_URL', 'https://t.me/' . env( 'BOT_USERNAME', '' ) ),
    'bot_deeplinking' => 'tg://resolve?domain=' . env( 'BOT_USERNAME', '' ) . '&start=',
    'bot_webapp_deeplinking' => 'tg://resolve?domain=' . env( 'BOT_USERNAME', '' ) . '&appname=stream&startapp=',
    'bot_webapp_link' => 'https://t.me/' . env( 'BOT_USERNAME', '' ) . '/stream?startapp=',
    'telegram_share_url' => 'https://telegram.me/share/url?url=',
    'webapp_url' => env( 'WEBAPP_URL', '' ),
    'min_withdrawal_amount' => env( 'MIN_WITHDRAWAL_AMOUNT', 10 ),
    'post_view_rpoint' => env( 'POST_VIEW_RPOINT', 0.006 ),
    'post_view_opoint' => env( 'POST_VIEW_OPOINT', 0.006 ),

    //
    'admins' => [1497831921]
];
