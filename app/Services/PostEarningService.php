<?php

namespace App\Services;

use App\Helpers\TelegramHelper;
use App\Models\PostEarning;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB; // Make sure to import the DB facade

class PostEarningService {

    public function createEarning(string $postId, string $viewerId): array {
        // Begin a database transaction
        return DB::transaction(function () use ($postId, $viewerId) {
            // Find the post or fail
            $post = Post::where('post_id', $postId)->firstOrFail();

            // Get reward points for the viewer and owner
            $rewardPoints = TelegramHelper::getPostViewRewardPoint();
            $rewardOwnerPoints = TelegramHelper::getPostViewOwnerPoint();

            // Find the user who viewed the post
            $user = User::where('user_id', $viewerId)->firstOrFail();;

            // Get the owner of the post
            $owner = $post->user;

            // Create an earning record for the user who viewed the post
            $userEarning = PostEarning::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'point' => $rewardPoints,
            ]);

            $ownerEarning = PostEarning::create([
                'post_id' => $post->id,
                'user_id' => $owner->id,
                'point' => $rewardOwnerPoints,
            ]);

            $owner->increment('balance', $rewardOwnerPoints);
            $user->increment('balance', $rewardPoints);

            return [
                'user_earning' => $userEarning,
                'owner_earning' => $ownerEarning ?? null,
            ];
        });
    }


    /**
    * Check if a user has already earned points for a specific post.
    *
    * @param int $postId
    * @param int $userId
    * @return bool
    */

    public function hasUserEarned( int $postId, int $userId ): bool {
        return PostEarning::where( 'post_id', $postId )
        ->where( 'user_id', $userId )
        ->exists();
    }

    /**
    * Get all earnings for a specific post.
    *
    * @param int $postId
    * @return \Illuminate\Database\Eloquent\Collection
    */

    public function getEarningsByPost( int $postId ) {
        return PostEarning::where( 'post_id', $postId )->get();
    }

    /**
    * Get all earnings for a specific user.
    *
    * @param int $userId
    * @return \Illuminate\Database\Eloquent\Collection
    */

    public function getEarningsByUser( int $userId ) {
        return PostEarning::where( 'user_id', $userId )->get();
    }

    /**
    * Delete a post earning record.
    *
    * @param int $postId
    * @param int $userId
    * @return bool
    * @throws ModelNotFoundException
    */

    public function deleteEarning( int $postId, int $userId ): bool {
        $earning = PostEarning::where( 'post_id', $postId )
        ->where( 'user_id', $userId )
        ->firstOrFail();

        return $earning->delete();
    }
}
