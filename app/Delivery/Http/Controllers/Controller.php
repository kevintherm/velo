<?php

namespace App\Delivery\Http\Controllers;

use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    public function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        return ApiResponse::success($data, $message, $status, $meta);
    }

    public function error(
        string|array $message,
        int $status = 400,
        ?string $code = null
    ): JsonResponse {
        return ApiResponse::error($message, $status, $code);
    }
}
