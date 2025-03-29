<?php declare( strict_types = 1 );

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
* @method static static START()
* @method static static STOP()
* @method static static PAUSE()
* @method static static RESUME()
* @method static static HELP()
* @method static static SUPER_STATS()
* @method static static ACCOUNT()
* @method static static CREATE_POST()
* @method static static BROADCAST_MESSAGE()
*/
final class CommandsEnum extends Enum {
    const START = 'start';
    const STOP = 'stop';
    const PAUSE = 'pause';
    const RESUME = 'resume';
    const HELP = 'help';
    const SUPER_STATS = 'super_stats';
    const ACCOUNT = 'account';
    const CREATE_POST = 'create_post';
    const CREATE_BROADCAST = 'broadcast';
    const CONNECT_CHAT = 'connect_chat';
}
