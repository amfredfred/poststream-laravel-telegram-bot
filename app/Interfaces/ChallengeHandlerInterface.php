<?php

namespace App\Interfaces;

use App\Models\Activity;
use App\Models\Challenge;

interface ChallengeHandlerInterface {
    public static function handle( Challenge $challenge, Activity $task, $userId );
}
