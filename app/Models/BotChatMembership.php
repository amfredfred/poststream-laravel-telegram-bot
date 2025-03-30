<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BotChatMembership extends Model {
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
        'last_checked_at',
        'chat_username'
    ];

    protected $casts = [
        'permissions' => 'array',
        'added_at' => 'datetime',
        'last_checked_at' => 'datetime'
    ];

    // Use proper scope method naming

    public function scopeActive( Builder $query ): Builder {
        return $query->whereNotIn( 'bot_status', [ 'left', 'kicked', 'banned' ] );
    }

    public function scopeChannels( Builder $query ): Builder {
        return $query->where( 'chat_type', 'channel' );
    }

    public function scopeGroups( Builder $query ): Builder {
        return $query->whereIn( 'chat_type', [ 'group', 'supergroup' ] );
    }

    // Helper accessor

    public function getIsActiveAttribute(): bool {
        return !in_array( $this->bot_status, [ 'left', 'kicked', 'banned' ] );
    }
}
