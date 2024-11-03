<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    // Define the table name (optional if the table name follows Laravel conventions)
    protected $table = 'transactions';

    // Mass assignable attributes
    protected $fillable = [
        'unique_id',
        'user_id',
        'amount',
        'wallet_address',
        'status',
    ];

    // Casts for attribute types
    protected $casts = [
        'amount' => 'decimal:2',  // Ensures two decimal places
        'status' => StatusEnum::class, // Enum casting
    ];

    /**
     * Define a relationship with the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set a unique ID when creating a new transaction.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            $transaction->unique_id = self::generateUniqueId();
        });
    }

    /**
     * Generate a unique ID for each transaction.
     *
     * @return string
     */
    public static function generateUniqueId(): string
    {
        return Str::upper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
    }

    /**
     * Scope for filtering transactions by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param StatusEnum $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, StatusEnum $status)
    {
        return $query->where('status', $status);
    }
}
