<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;

final class ApiErrorResponse
{
    /**
     * @param array<string, mixed> $extra
     */
    public static function fromStatus(int $status, string $message, array $extra = []): JsonResponse
    {
        return response()->json([
            'error' => array_merge([
                'code' => self::codeFromStatus($status),
                'message' => $message,
                'status' => $status,
            ], $extra),
        ], $status);
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    public static function validation(array $errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return self::fromStatus(
            status: 422,
            message: $message,
            extra: [
                'errors' => $errors,
            ],
        );
    }

    private static function codeFromStatus(int $status): string
    {
        return match ($status) {
            401 => 'unauthorized',
            403 => 'forbidden',
            404 => 'not_found',
            422 => 'validation_error',
            default => 'http_error',
        };
    }
}