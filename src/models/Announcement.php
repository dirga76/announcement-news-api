<?php

namespace App\Models;

use PDO;
use Ramsey\Uuid\Uuid;

class Announcement
{
    private $conn;
    private $table = 'announcements';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getFilteredAnnouncements($page, $limit, $search, $category, $author, $sort, $start, $end)
    {
        $offset = ($page - 1) * $limit;
        $sortOrder = strtoupper(str_contains($sort, 'asc') ? 'ASC' : 'DESC');
        $sortField = str_contains($sort, 'start_date') ? 'a.start_date' : 'a.created_at';

        $sql = "SELECT a.*, u.username AS author 
                FROM {$this->table} a 
                JOIN users u ON a.author_id = u.id
                WHERE 1=1";

        $bindings = [];

        if (!empty($search)) {
            $sql .= " AND (a.title LIKE :search OR a.content LIKE :search)";
            $bindings[':search'] = "%$search%";
        }

        if (!empty($category)) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM announcement_categories ac 
                WHERE ac.announcement_id = a.id AND ac.category_id = :cat)";
            $bindings[':cat'] = $category;
        }

        if (!empty($author)) {
            $sql .= " AND a.author_id = :author";
            $bindings[':author'] = $author;
        }

        if (!empty($start)) {
            $sql .= " AND a.start_date >= :start";
            $bindings[':start'] = $start;
        }

        if (!empty($end)) {
            $sql .= " AND a.end_date <= :end";
            $bindings[':end'] = $end;
        }

        $sql .= " ORDER BY $sortField $sortOrder LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        foreach ($bindings as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertWithCategories($data, $categoryIds = [])
    {
        $data['id'] = Uuid::uuid4()->toString();

        $sql = "INSERT INTO {$this->table} 
            (id, title, slug, content, author_id, is_important, start_date, end_date)
            VALUES (:id, :title, :slug, :content, :author_id, :is_important, :start_date, :end_date)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);

        foreach ($categoryIds as $catId) {
            $link = $this->conn->prepare("INSERT INTO announcement_categories (announcement_id, category_id) VALUES (:a_id, :c_id)");
            $link->execute([':a_id' => $data['id'], ':c_id' => $catId]);
        }

        return true;
    }

    public function updateWithCategories($id, $data, $categoryIds = [])
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);

        $this->conn->prepare("DELETE FROM announcement_categories WHERE announcement_id = :id")->execute([':id' => $id]);

        foreach ($categoryIds as $catId) {
            $link = $this->conn->prepare("INSERT INTO announcement_categories (announcement_id, category_id) VALUES (:a_id, :c_id)");
            $link->execute([':a_id' => $id, ':c_id' => $catId]);
        }

        return true;
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
