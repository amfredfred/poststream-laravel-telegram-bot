<?php

namespace App\Http\Controllers\Api;

use App\Services\PostEarningService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PostEarningController extends Controller {
    protected PostEarningService $postEarningService;

    public function __construct( PostEarningService $postEarningService ) {
        $this->postEarningService = $postEarningService;
    }

    public function earnPoints( Request $request ): JsonResponse {
        $request->validate( [
            'post_id' => 'required|exists:posts,post_id',
            'user_id' => 'required|exists:users,user_id',
        ] );

        try {
            $earning = $this->postEarningService->createEarning( $request->input( 'post_id' ), $request->input( 'user_id' ) );
            return $this->success( $earning, 201 );
        } catch ( \Throwable $th ) {
            Log::error( 'PostEarningController->earnPoints: ' . $th->getMessage(), [
                'exception' => $th,
                'post_id' => $request->input( 'post_id' ),
                'user_id' => $request->input( 'user_id' ),
            ] );
            return $this->error( 'An error occurred while earning points', 500, $th->getMessage() );
        }
    }
}
