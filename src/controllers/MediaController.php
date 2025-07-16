<?php

namespace App\Controllers;

use App\Models\Media;
use App\Helpers\ResponseHandler;

class MediaController
{
    private $media;

    public function __construct($db)
    {
        $this->media = new Media($db);
    }

    public function upload()
    {
        if (!isset($_FILES['file'])) {
            return ResponseHandler::sendError('File tidak ditemukan dalam request.', 400);
        }

        $file = $_FILES['file'];
        $uploader_id = $_SERVER['auth']['user_id'];

        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileName = basename($file['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            $this->media->upload([
                'file_name' => $fileName,
                'file_path' => '/uploads/' . $fileName,
                'file_type' => $file['type'],
                'file_size' => $file['size'],
                'uploader_id' => $uploader_id,
            ]);

            return ResponseHandler::sendResponse(null, 'File berhasil diunggah', 201);
        } else {
            return ResponseHandler::sendError('Gagal mengunggah file.', 500);
        }
    }

    public function getAll()
    {
        $media = $this->media->getAll();
        ResponseHandler::sendResponse($media);
    }

    public function delete($id)
    {
        $success = $this->media->delete($id);

        if ($success) {
            ResponseHandler::sendResponse(null, 'Media berhasil dihapus');
        } else {
            ResponseHandler::sendError('Gagal menghapus media.', 500);
        }
    }
}
