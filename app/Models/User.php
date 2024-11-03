<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
    * The attributes that are mass assignable.
    *
    * @var array<int, string>
    */
    protected $fillable = [
        'tid',
        'name',
        'full_name',
        'user_id',
        'channel_from',
        'message',
        'chat_id',
        'balance',
    ];

    /**
    * The attributes that should be cast.
    *
    * @return array<string, string>
    */
    protected function casts(): array {
        return [
            'message' => 'boolean',
            'balance' => 'decimal:2',
        ];
    }
}
