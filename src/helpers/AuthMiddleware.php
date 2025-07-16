<?php

namespace App\Helpers;

class AuthMiddleware
{
    public static function authorize()
    {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            ResponseHandler::sendError('Token tidak ditemukan.', 401);
        }

        $token = trim(str_replace('Bearer', '', $authHeader));

        $jwt = new JwtHandler();
        $payload = $jwt->validateToken($token);

        if (!$payload) {
            ResponseHandler::sendError('Token tidak valid atau kadaluarsa.', 401);
        }

       
        $_SERVER['auth'] = $payload;
    }
}
