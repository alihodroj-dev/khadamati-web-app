<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse(
        string $message = 'Success',
        mixed $data = null,
        int $statusCode = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'errors'  => null,
            'data'    => $data,
        ], $statusCode);
    }

    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $statusCode = 400
    ) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'data'    => null,
        ], $statusCode);
    }
}