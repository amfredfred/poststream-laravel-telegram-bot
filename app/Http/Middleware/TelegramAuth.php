<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\UserService;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Log;

class TelegramAuth {
    /**
    * Handle an incoming request.
    *
    * @param  \Closure( \Illuminate\Http\Request ): ( \Symfony\Component\HttpFoundation\Response )  $next
    * @param Request $request
    * @return Response
    */


    public function handle( Request $request, Closure $next ): Response {
        // Expecting a user ID from the x-telegram-id header
        $userId = $request->header( 'x-telegram-id' );

        if ( !$userId ) {
            return ResponseHelper::error( 'Unauthorized: Missing user ID', Response::HTTP_UNAUTHORIZED );
        }

        // Check if the user exists or create a new user
        $user = UserService::findUserOrCrashApi( $userId );

        if ( !$user ) {
            return ResponseHelper::error( 'Unauthorized', Response::HTTP_UNAUTHORIZED, 'Reason: User with ID '.$userId.' Not Found' );
        }
        // Optionally, you can attach the user to the request for later use
        $request->attributes->set( 'telegram_user', $user );
        // Proceed to the next middleware or controller
        return $next( $request );
    }
}
