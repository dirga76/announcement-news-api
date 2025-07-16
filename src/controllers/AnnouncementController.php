<?php

namespace App\Controllers;

use App\Models\Announcement;
use App\Helpers\ResponseHandler;

class AnnouncementController
{
    private $announcement;

    public function __construct($db)
    {
        $this->announcement = new Announcement($db);
    }

    public function getAll()
    {
        $query = $_GET;

        $page     = isset($query['page']) ? (int)$query['page'] : 1;
        $limit    = isset($query['limit']) ? (int)$query['limit'] : 10;
        $search   = $query['search'] ?? '';
        $category = $query['category'] ?? null;
        $author   = $query['author'] ?? null;
        $sort     = $query['sort'] ?? 'start_date_desc';
        $start    = $query['start'] ?? null;
        $end      = $query['end'] ?? null;

        $data = $this->announcement->getFilteredAnnouncements($page, $limit, $search, $category, $author, $sort, $start, $end);
        return ResponseHandler::sendResponse($data);
    }

    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['title']) ||
            empty($data['content']) ||
            empty($data['start_date']) ||
            empty($data['end_date'])
        ) {
            return ResponseHandler::sendError('Semua data wajib diisi.', 400);
        }

        $author_id    = $_SERVER['auth']['user_id'];
        $category_ids = $data['category_ids'] ?? [];
        $title        = trim($data['title']);
        $slug         = $this->generateSlug($title);

        $announcementData = [
            'title'        => $title,
            'slug'         => $slug,
            'content'      => $data['content'],
            'author_id'    => $author_id,
            'is_important' => $data['is_important'] ?? false,
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date']
        ];

        $success = $this->announcement->insertWithCategories($announcementData, $category_ids);

        if ($success) {
            return ResponseHandler::sendResponse(null, 'Pengumuman berhasil ditambahkan', 201);
        }

        return ResponseHandler::sendError('Gagal menambahkan pengumuman.', 500);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['title']) ||
            empty($data['content']) ||
            empty($data['start_date']) ||
            empty($data['end_date'])
        ) {
            return ResponseHandler::sendError('Semua data wajib diisi.', 400);
        }

        $title        = trim($data['title']);
        $slug         = $this->generateSlug($title);
        $category_ids = $data['category_ids'] ?? [];

        $announcementData = [
            'title'        => $title,
            'slug'         => $slug,
            'content'      => $data['content'],
            'is_important' => $data['is_important'] ?? false,
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date']
        ];

        $success = $this->announcement->updateWithCategories($id, $announcementData, $category_ids);

        if ($success) {
            return ResponseHandler::sendResponse(null, 'Pengumuman berhasil diperbarui');
        }

        return ResponseHandler::sendError('Gagal memperbarui pengumuman.', 500);
    }

    public function delete($id)
    {
        $success = $this->announcement->delete($id);

        if ($success) {
            return ResponseHandler::sendResponse(null, 'Pengumuman berhasil dihapus');
        }

        return ResponseHandler::sendError('Gagal menghapus pengumuman.', 500);
    }

    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug . '-' . substr(md5(uniqid()), 0, 6);
    }
}
