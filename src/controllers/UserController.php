<?php

namespace App\Controllers;

use App\Models\User;
use App\Helpers\ResponseHandler;

class UserController
{
    private $user;

    public function __construct($db)
    {
        $this->user = new User($db);
    }

    public function getAll()
    {
        $users = $this->user->getAll();
        ResponseHandler::sendResponse($users);
    }

    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['username']) ||
            empty($data['email']) ||
            empty($data['password']) ||
            empty($data['role'])
        ) {
            return ResponseHandler::sendError("Semua data wajib diisi", 400);
        }

        try {
            $this->user->createWithHashedPassword($data);
            ResponseHandler::sendResponse(null, "User berhasil ditambahkan", 201);
        } catch (\Exception $e) {
            ResponseHandler::sendError($e->getMessage(), 400);
        }
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['username']) ||
            empty($data['email']) ||
            empty($data['role'])
        ) {
            return ResponseHandler::sendError("Semua data wajib diisi", 400);
        }

        try {
            $this->user->update($id, $data);
            ResponseHandler::sendResponse(null, "User berhasil diperbarui");
        } catch (\Exception $e) {
            ResponseHandler::sendError($e->getMessage(), 400);
        }
    }

    public function delete($id)
    {
        $success = $this->user->delete($id);

        if ($success) {
            ResponseHandler::sendResponse(null, "User berhasil dihapus");
        } else {
            ResponseHandler::sendError("Gagal menghapus user", 500);
        }
    }
}
