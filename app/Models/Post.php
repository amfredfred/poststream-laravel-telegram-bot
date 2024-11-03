<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model {
    protected $fillable = [
        'user_id',
        'caption',
        'media_type',
        'media_id',
        'caption_entities',
        'post_id',
        'inline_keyboard_markup'
    ];

    protected $casts = [
        'caption_entities' => 'array',
        'inline_keyboard_markup' => 'array'
    ];

    /**
    * Get the user that owns the post.
    */

    public function user() {
        return $this->belongsTo( User::class );
    }

    /**
    * Get the views associated with the post.
    */

    public function views(): HasMany {
        return $this->hasMany( PostViews::class );
    }

    /**
    * Check if the given user has viewed the post.
    */

    public function hasUserViewed( int $userId ): bool {
        return $this->views()->where( 'user_id', $userId )->exists();
    }

    /**
    * Get the comments associated with the post.
    */

    // public function comments(): HasMany {
    // return $this->hasMany( Comment::class );
    // }

    /**
    * Get the likes associated with the post.
    */

    // public function likes(): HasMany {
    // return $this->hasMany( Like::class );
    // }

    // Relationship to the PostEarning model

    public function earnings() {
        return $this->hasMany( PostEarning::class );
    }
}
