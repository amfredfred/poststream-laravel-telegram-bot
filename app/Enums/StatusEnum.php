<?php declare( strict_types = 1 );

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
* Enum representing different status options.
*
* @method static static ACTIVE()
* @method static static INACTIVE()
* @method static static PENDING()
* @method static static COMPLETED()
* @method static static APPROVED()
* @method static static DECLINED()
* @method static static CANCELLED()
* @method static static EXPIRED()
* @method static static LOCKED()
* @method static static SUSPEND()
* @method static static WAITING()
*/
final class StatusEnum extends Enum {
    const ACTIVE = 'ACTIVE';
    // Represents an active status
    const INACTIVE = 'INACTIVE';
    // Represents an inactive status
    const PENDING = 'PENDING';
    // Represents a pending status
    const COMPLETED = 'COMPLETED';
    // Represents a completed status
    const APPROVED = 'APPROVED';
    // Represents an approved status
    const DECLINED = 'DECLINED';
    // Represents a declined status
    const CANCELLED = 'CANCELLED';
    // Represents a cANCELLED status
    const EXPIRED = 'EXPIRED';
    // Represents a locked status
    const LOCKED =   'LOCKED';
    // Represents a suspend status
    const SUSPEND = 'SUSPEND';
    // Represents a waiting status
    const WAITING = 'WAITING';
}
