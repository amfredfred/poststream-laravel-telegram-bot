<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskNotCompletedException extends Exception {
    protected $details;

    /**
    * Constructor for the exception.
    *
    * @param string $message
    * @param int $code
    * @param mixed $details
    */

    public function __construct( string $message = 'Task not completed.', int $code = 400, $details = null ) {
        parent::__construct( $message, $code );
        $this->details = $details;
    }

    /**
    * Log the exception details.
    */

    public function report(): void {
        // Log::error( "TaskNotCompletedException: {$this->getMessage()}", [
        //     'code' => $this->getCode(),
        //     'details' => $this->details,
        // ] );
    }

    /**
    * Render the exception as an HTTP response.
    *
    * @param Request $request
    * @return JsonResponse
    */

    public function render( Request $request ): JsonResponse {
        return ResponseHelper::error(
            $this->getMessage(),
            $this->getCode(),
            $this->details
        );
    }
}
