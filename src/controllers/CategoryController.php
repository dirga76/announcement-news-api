<?php

namespace App\Controllers;

use App\Models\Category;
use App\Helpers\ResponseHandler;

class CategoryController
{
    private $category;

    public function __construct($db)
    {
        $this->category = new Category($db);
    }

    public function getAll()
    {
        $data = $this->category->getAll();
        ResponseHandler::sendResponse($data);
    }

    public function create()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['name'])) {
            return ResponseHandler::sendError('Nama kategori wajib diisi.', 400);
        }

        $success = $this->category->create($input);

        if ($success) {
            ResponseHandler::sendResponse(null, 'Kategori berhasil ditambahkan', 201);
        } else {
            ResponseHandler::sendError('Gagal menambahkan kategori.', 500);
        }
    }

    public function update($id)
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['name'])) {
            return ResponseHandler::sendError('Nama kategori wajib diisi.', 400);
        }

        $success = $this->category->update($id, $input);

        if ($success) {
            ResponseHandler::sendResponse(null, 'Kategori berhasil diperbarui');
        } else {
            ResponseHandler::sendError('Gagal memperbarui kategori.', 500);
        }
    }

    public function delete($id)
    {
        $success = $this->category->delete($id);

        if ($success) {
            ResponseHandler::sendResponse(null, 'Kategori berhasil dihapus');
        } else {
            ResponseHandler::sendError('Gagal menghapus kategori.', 500);
        }
    }
}
