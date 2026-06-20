<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function safeErrorResponse(Throwable $exception, string $message = 'Terjadi kesalahan pada sistem. Silakan coba lagi.'): JsonResponse
    {
        Log::error($message, [
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);

        return response()->json([
            'status' => [
                'msg' => 'Err',
                'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ],
            'data' => null,
            'message' => $message,
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
