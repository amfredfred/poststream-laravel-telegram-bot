<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotChatMembership extends Model {
    use HasFactory;

    protected $table = 'bot_chat_memberships';

    protected $fillable = [
        'chat_id',
        'chat_title',
        'chat_type',
        'permissions',
        'invited_by_username',
        'invited_by_id',
        'bot_status',
        'added_at',
        'last_checked_at'
    ];

    protected $casts = [
        'permissions' => 'array',
        'added_at' => 'datetime',
        'last_checked_at' => 'datetime'
    ];

    public function scopeActive( $query ) {
        return $query->whereNotIn( 'bot_status', [ 'left', 'kicked' ] );
    }

    public function scopeChannels( $query ) {
        return $query->where( 'chat_type', 'channel' );
    }

    public function scopeGroups( $query ) {
        return $query->whereIn( 'chat_type', [ 'group', 'supergroup' ] );
    }
}
