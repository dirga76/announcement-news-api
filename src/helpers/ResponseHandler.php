<?php

namespace App\Helpers;

class ResponseHandler
{
    public static function sendResponse($data, $message = 'Success', $status = 200)
    {
        http_response_code($status);
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    public static function sendError($message = 'Error', $status = 400)
    {
        http_response_code($status);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit;
    }
}
