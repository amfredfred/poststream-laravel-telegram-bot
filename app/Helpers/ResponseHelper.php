<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
* Helper functions for standardized JSON responses.
*/

class ResponseHelper {
    /**
    * Return a success response.
    *
    * @param mixed $data
    * @param string|null $message
    * @param int $status
    * @return JsonResponse
    */
    public static function success( $data = null, $message = null, $status = 200 ): JsonResponse {
        return response()->json( [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status );
    }

    /**
    * Return an error response.
    *
    * @param string $message
    * @param int $status
    * @param array|null $errors
    * @return JsonResponse
    */
    public static function error( $message = 'An error occurred.', $status = 400, $errors = null ): JsonResponse {
        return response()->json( [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status );
    }
}
