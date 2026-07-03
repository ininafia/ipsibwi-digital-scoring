<?php

namespace App\Http\Presenter;

class Response
{
    public static function buildSuccess(
        array $data = [],
        int $code = 200,
        string $message = 'Success'
    ): array {
        return [
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];
    }

    public static function buildError(
        string $message = 'Error',
        int $code = 422
    ): array {
        return [
            'success' => false,
            'code' => $code,
            'message' => $message
        ];
    }

    public static function buildErrorService(
        string $message = 'Internal Server Error'
    ): array {
        return [
            'success' => false,
            'code' => 500,
            'message' => $message
        ];
    }

    public static function buildSuccessCreated(
        string $message = 'Data berhasil dibuat'
    ): array {
        return [
            'success' => true,
            'code' => 201,
            'message' => $message
        ];
    }
}