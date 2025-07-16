<?php

namespace App\Controllers;

use App\Models\User;
use App\Helpers\JwtHandler;
use App\Helpers\ResponseHandler;

class AuthController
{
    private $db;
    private $user;
    private $jwt;

    public function __construct($db)
    {
        $this->db = $db;
        $this->user = new User($db);
        $this->jwt = new JwtHandler();
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->username) || empty($data->password)) {
            return ResponseHandler::sendError('Username dan password wajib diisi.', 400);
        }

        $userData = $this->user->findByUsername($data->username);

        if (!$userData) {
            return ResponseHandler::sendError('User tidak ditemukan', 404);
        }

        if (!password_verify($data->password, $userData['password'])) {
            return ResponseHandler::sendError('Password salah', 401);
        }


        $accessToken = $this->jwt->generateAccessToken([
            'user_id' => $userData['id'],
            'role' => $userData['role']
        ]);

        $refreshToken = $this->jwt->generateRefreshToken();
        $this->user->updateRefreshToken($userData['id'], $refreshToken);

        $response = [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => [
                'id' => $userData['id'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'role' => $userData['role']
            ]
        ];

        return ResponseHandler::sendResponse($response, 'Login berhasil');
    }
    public function refreshToken()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->refresh_token)) {
            return ResponseHandler::sendError('Refresh token diperlukan', 400);
        }

        $userData = $this->user->getByRefreshToken($data->refresh_token);

        if (!$userData) {
            return ResponseHandler::sendError('Refresh token tidak valid.', 401);
        }

        $accessToken = $this->jwt->generateAccessToken([
            'user_id' => $userData['id'],
            'role' => $userData['role']
        ]);

        // (Opsional) generate refresh token baru:
        // $newRefreshToken = $this->jwt->generateRefreshToken();
        // $this->user->updateRefreshToken($userData['id'], $newRefreshToken);

        ResponseHandler::sendResponse([
            'access_token' => $accessToken
            // 'refresh_token' => $newRefreshToken
        ], 'Token berhasil diperbarui');
    }
}
