<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostEarning extends Model {

    protected $fillable = [
        'post_id',
        'point',
        'user_id'
    ];

    public function post() {
        return $this->belongsTo( Post::class );
    }

    public function user() {
        return $this->belongsTo( User::class );
    }
}