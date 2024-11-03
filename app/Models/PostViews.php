<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostViews extends Model {
    use HasFactory;

    // Specify the fillable attributes
    protected $fillable = [ 'post_id', 'user_id', 'viewed_at' ];

    /**
    * Check if the user has already viewed the post.
    *
    * @param int $postId
    * @param int $userId
    * @return bool
    */
    public static function isViewedByUser( int $postId, int $userId ): bool {
        return self::where( 'post_id', $postId )
        ->where( 'user_id', $userId )
        ->exists();
    }

    /**
    * Get the post associated with the view.
    */

    public function post(): BelongsTo {
        return $this->belongsTo( Post::class );
    }

    /**
    * Get the user who viewed the post.
    */

    public function user(): BelongsTo {
        return $this->belongsTo( User::class );
    }
}
