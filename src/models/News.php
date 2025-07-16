<?php

namespace App\Models;

use PDO;
use Ramsey\Uuid\Uuid;

class News
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getFilteredNews($page, $limit, $search, $category, $author, $sort)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT n.*, u.username AS author_username
                FROM news n
                JOIN users u ON n.author_id = u.id
                LEFT JOIN news_categories nc ON n.id = nc.news_id
                LEFT JOIN categories c ON nc.category_id = c.id
                WHERE 1=1";

        $params = [];


        if (!empty($search)) {
            $sql .= " AND (n.title LIKE :search OR n.content LIKE :search)";
            $params[':search'] = "%$search%";
        }


        if (!empty($category)) {
            $sql .= " AND c.slug = :category";
            $params[':category'] = $category;
        }


        if (!empty($author)) {
            $sql .= " AND u.username = :author";
            $params[':author'] = $author;
        }


        switch ($sort) {
            case 'title_asc':
                $sql .= " ORDER BY n.title ASC";
                break;
            case 'title_desc':
                $sql .= " ORDER BY n.title DESC";
                break;
            case 'published_at_asc':
                $sql .= " ORDER BY n.published_at ASC";
                break;
            default:
                $sql .= " ORDER BY n.published_at DESC";
        }


        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $countStmt = $this->conn->prepare("SELECT COUNT(*) FROM news");
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        return [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'data' => $results
        ];
    }
    public function insert($data)
    {
        $sql = "INSERT INTO news (title, slug, content, excerpt, featured_image, author_id, is_published, published_at)
            VALUES (:title, :slug, :content, :excerpt, :featured_image, :author_id, :is_published, :published_at)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':title' => $data['title'],
            ':slug' => $data['slug'],
            ':content' => $data['content'],
            ':excerpt' => $data['excerpt'],
            ':featured_image' => $data['featured_image'],
            ':author_id' => $data['author_id'],
            ':is_published' => $data['is_published'],
            ':published_at' => $data['published_at']
        ]);
    }

    public function insertWithCategories($newsData, $categoryIds = [])
    {
        try {
            $this->conn->beginTransaction();

           
            $newsId = Uuid::uuid4()->toString();
            $newsData['id'] = $newsId;

            $sql = "INSERT INTO news (id, title, slug, content, excerpt, featured_image, author_id, is_published, published_at)
                VALUES (:id, :title, :slug, :content, :excerpt, :featured_image, :author_id, :is_published, :published_at)";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':id' => $newsData['id'],
                ':title' => $newsData['title'],
                ':slug' => $newsData['slug'],
                ':content' => $newsData['content'],
                ':excerpt' => $newsData['excerpt'],
                ':featured_image' => $newsData['featured_image'],
                ':author_id' => $newsData['author_id'],
                ':is_published' => $newsData['is_published'],
                ':published_at' => $newsData['published_at']
            ]);

          
            if (!empty($categoryIds)) {
                $pivotSQL = "INSERT INTO news_categories (news_id, category_id) VALUES ";
                $params = [];
                foreach ($categoryIds as $index => $categoryId) {
                    $pivotSQL .= "(:news_id, :cat$index),";
                    $params[":cat$index"] = $categoryId;
                }
                $pivotSQL = rtrim($pivotSQL, ',');
                $params[':news_id'] = $newsId;

                $pivotStmt = $this->conn->prepare($pivotSQL);
                $pivotStmt->execute($params);
            }

            $this->conn->commit();
            return true;
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("News insert error: " . $e->getMessage());
            return false;
        }
    }

    public function updateWithCategories($id, $data, $categoryIds = [])
    {
        try {
            $this->conn->beginTransaction();

            $sql = "UPDATE news SET 
                    title = :title,
                    slug = :slug,
                    content = :content,
                    excerpt = :excerpt,
                    featured_image = :featured_image,
                    is_published = :is_published,
                    published_at = :published_at,
                    updated_at = NOW()
                WHERE id = :id";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':title' => $data['title'],
                ':slug' => $data['slug'],
                ':content' => $data['content'],
                ':excerpt' => $data['excerpt'],
                ':featured_image' => $data['featured_image'],
                ':is_published' => $data['is_published'],
                ':published_at' => $data['published_at']
            ]);

           
            $this->conn->prepare("DELETE FROM news_categories WHERE news_id = :id")->execute([':id' => $id]);

        
            if (!empty($categoryIds)) {
                $pivotSQL = "INSERT INTO news_categories (news_id, category_id) VALUES ";
                $params = [];
                foreach ($categoryIds as $i => $catId) {
                    $pivotSQL .= "(:id, :cat$i),";
                    $params[":cat$i"] = $catId;
                }
                $pivotSQL = rtrim($pivotSQL, ',');
                $params[':id'] = $id;
                $this->conn->prepare($pivotSQL)->execute($params);
            }

            $this->conn->commit();
            return true;
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM news WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
