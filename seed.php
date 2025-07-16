<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;
use App\Models\User;
use Ramsey\Uuid\Uuid;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = (new Database())->connect();
$userModel = new User($db);

// === SEED USERS ===
$users = [
    [
        'username' => 'admin',
        'email' => 'admin@sekolah.sch.id',
        'password' => 'admin123',
        'role' => 'admin'
    ],
    [
        'username' => 'guru1',
        'email' => 'guru1@sekolah.sch.id',
        'password' => 'admin123',
        'role' => 'teacher'
    ],
    [
        'username' => 'superadmin',
        'email' => 'superadmin@sekolah.sch.id',
        'password' => 'admin123',
        'role' => 'super_admin'
    ]
];

echo "Seeding users...\n";
foreach ($users as $data) {
    $existing = $userModel->findByUsername($data['username']);
    if ($existing) {
        echo "User {$data['username']} sudah ada, dilewati...\n";
        continue;
    }
    $userModel->createWithHashedPassword($data);
    echo "User {$data['username']} berhasil ditambahkan\n";
}

// === SEED CATEGORIES ===
echo "\nSeeding categories...\n";
$categories = [
    [
        'name' => 'Pengumuman',
        'slug' => 'pengumuman',
        'description' => 'Informasi penting terkait agenda dan kegiatan sekolah.'
    ],
    [
        'name' => 'Berita',
        'slug' => 'berita',
        'description' => 'Berisi informasi dan kabar terbaru seputar sekolah.'
    ]
];

foreach ($categories as $cat) {
    $id = Uuid::uuid4()->toString();
    $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
    $stmt->execute([$cat['slug']]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO categories (id, name, slug, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $cat['name'], $cat['slug'], $cat['description']]);
        echo "Kategori {$cat['name']} berhasil ditambahkan\n";
    } else {
        echo "Kategori {$cat['name']} sudah ada, dilewati...\n";
    }
}


$stmt = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
$stmt->execute();
$adminId = $stmt->fetchColumn();


$stmt = $db->query("SELECT id, slug FROM categories");
$categoryMap = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $categoryMap[$row['slug']] = $row['id'];
}

// === SEED ANNOUNCEMENT ===
echo "\nSeeding announcements...\n";
$announcementId = Uuid::uuid4()->toString();
$stmt = $db->prepare("SELECT COUNT(*) FROM announcements WHERE slug = ?");
$slug = 'pendaftaran-ulang-' . time();
$stmt->execute([$slug]);

if ($stmt->fetchColumn() == 0) {
    $stmt = $db->prepare("INSERT INTO announcements (id, title, slug, content, author_id, is_important, start_date, end_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $announcementId,
        'Pendaftaran Ulang Semester Ganjil',
        $slug,
        'Pendaftaran ulang dilakukan mulai tanggal 5 Juli hingga 15 Juli. Silakan cek jadwal masing-masing.',
        $adminId,
        true,
        '2025-07-05',
        '2025-07-15'
    ]);
    echo "Announcement berhasil ditambahkan\n";

    $stmt = $db->prepare("INSERT INTO announcement_categories (announcement_id, category_id) VALUES (?, ?)");
    $stmt->execute([$announcementId, $categoryMap['pengumuman']]);
}

// === SEED NEWS ===
echo "\nSeeding news...\n";
$newsId = Uuid::uuid4()->toString();
$slug = 'kegiatan-mpls-' . time();
$stmt = $db->prepare("SELECT COUNT(*) FROM news WHERE slug = ?");
$stmt->execute([$slug]);

if ($stmt->fetchColumn() == 0) {
    $stmt = $db->prepare("INSERT INTO news (id, title, slug, content, excerpt, featured_image, author_id, is_published, published_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $newsId,
        'Kegiatan MPLS Siswa Baru',
        $slug,
        'MPLS (Masa Pengenalan Lingkungan Sekolah) akan dilaksanakan mulai 20 Juli selama 3 hari.',
        'MPLS akan dimulai minggu depan.',
        null,
        $adminId,
        true,
        date('Y-m-d H:i:s')
    ]);

    $stmt = $db->prepare("INSERT INTO news_categories (news_id, category_id) VALUES (?, ?)");
    $stmt->execute([$newsId, $categoryMap['berita']]);
    echo "News berhasil ditambahkan\n";
}

echo "\n✅ SEED SELESAI ✅\n";
