<?php

namespace App\Models;

use PDO;
use Ramsey\Uuid\Uuid;

class Media
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function upload($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO media (id, file_name, file_path, file_type, file_size, uploader_id)
            VALUES (:id, :file_name, :file_path, :file_type, :file_size, :uploader_id)
        ");

        $uuid = Uuid::uuid4()->toString();

        return $stmt->execute([
            'id' => $uuid,
            'file_name' => $data['file_name'],
            'file_path' => $data['file_path'],
            'file_type' => $data['file_type'],
            'file_size' => $data['file_size'],
            'uploader_id' => $data['uploader_id'],
        ]);
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT * FROM media ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM media WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
