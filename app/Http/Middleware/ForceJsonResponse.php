<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse {
    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */

    public function handle( Request $request, Closure $next ) {
        // Force the request to accept JSON responses
        $request->headers->set( 'Accept', 'application/json' );
        $response = $next( $request );

        // Set CORS headers
        $response->headers->set( 'Access-Control-Allow-Origin', '*' );
        $response->headers->set( 'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS' );
        $response->headers->set( 'Access-Control-Allow-Headers', 'Content-Type, Authorization' );

        if ( $request->isMethod( 'OPTIONS' ) ) {
            return response()->json( 'OK', 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, x-telegram-id',
            ] );
        }

        return $response;
    }
}
