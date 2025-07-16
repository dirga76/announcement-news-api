<?php

namespace App\Models;

use PDO;
use Ramsey\Uuid\Uuid;

class Category
{
    private $conn;
    private $table = 'categories';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $id = Uuid::uuid4()->toString();
        $slug = $this->slugify($data['name']);

        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (id, name, slug, description) VALUES (:id, :name, :slug, :description)");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null
        ]);
    }

    public function update($id, $data)
    {
        $slug = $this->slugify($data['name']);
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET name = :name, slug = :slug, description = :description WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function slugify($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return rtrim($text, '-');
    }
}
