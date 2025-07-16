<?php

namespace App\Controllers;

use App\Models\News;
use App\Helpers\ResponseHandler;

class NewsController
{
    private $news;

    public function __construct($db)
    {
        $this->news = new News($db);
    }

    public function getAll()
    {
        $query = $_GET;

        $page = isset($query['page']) ? (int)$query['page'] : 1;
        $limit = isset($query['limit']) ? (int)$query['limit'] : 10;
        $search = $query['search'] ?? '';
        $category = $query['category'] ?? null;
        $author = $query['author'] ?? null;
        $sort = $query['sort'] ?? 'published_at_desc';

        $data = $this->news->getFilteredNews($page, $limit, $search, $category, $author, $sort);
        ResponseHandler::sendResponse($data);
    }
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['title']) || empty($data['content'])) {
            return ResponseHandler::sendError('Judul dan konten wajib diisi.', 400);
        }

        $title = $data['title'];
        $content = $data['content'];
        $excerpt = $data['excerpt'] ?? null;
        $featured_image = $data['featured_image'] ?? null;
        $is_published = $data['is_published'] ?? true;
        $published_at = $is_published ? date('Y-m-d H:i:s') : null;
        $author_id = $_SERVER['auth']['user_id'];

        $slug = $this->generateSlug($title);

        $categoryIds = $data['category_ids'] ?? [];

        $success = $this->news->insertWithCategories([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'featured_image' => $featured_image,
            'author_id' => $author_id,
            'is_published' => $is_published,
            'published_at' => $published_at
        ], $categoryIds);


        if ($success) {
            ResponseHandler::sendResponse(null, 'Berita berhasil ditambahkan', 201);
        } else {
            ResponseHandler::sendError('Gagal menambahkan berita.', 500);
        }
    }

    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug . '-' . time();
    }
    public function update($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['title']) || empty($data['content'])) {
            return ResponseHandler::sendError('Judul dan konten wajib diisi.', 400);
        }

        $title = $data['title'];
        $content = $data['content'];
        $excerpt = $data['excerpt'] ?? null;
        $featured_image = $data['featured_image'] ?? null;
        $is_published = $data['is_published'] ?? true;
        $published_at = $is_published ? date('Y-m-d H:i:s') : null;
        $categoryIds = $data['category_ids'] ?? [];

        $slug = $this->generateSlug($title);

        $success = $this->news->updateWithCategories($id, [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'featured_image' => $featured_image,
            'is_published' => $is_published,
            'published_at' => $published_at
        ], $categoryIds);

        if ($success) {
            ResponseHandler::sendResponse(null, 'Berita berhasil diperbarui');
        } else {
            ResponseHandler::sendError('Gagal memperbarui berita.', 500);
        }
    }
    public function delete($id)
    {
        $success = $this->news->delete($id);

        if ($success) {
            ResponseHandler::sendResponse(null, 'Berita berhasil dihapus');
        } else {
            ResponseHandler::sendError('Gagal menghapus berita', 500);
        }
    }
}
