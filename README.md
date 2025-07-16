# 📢 Sekolah Announcement & News API

Sistem backend REST API untuk mengelola berita, pengumuman, pengguna, dan media sekolah. Dibangun dengan PHP Native, MySQL, dan struktur modular yang production-ready.

---

## 🚀 Fitur Utama

- ✅ Manajemen User (Admin, Super Admin, Teacher)
- ✅ Login & Refresh Token (JWT-ready)
- ✅ Manajemen Pengumuman
- ✅ Manajemen Berita
- ✅ Kategori (berita & pengumuman)
- ✅ Upload Media (gambar, file)
- ✅ Filter, Search, Pagination, Sort
- ✅ Struktur Database Relasional (Normalized)
- ✅ Validasi dan Error Handling
- ✅ CORS dan Routing Dinamis

---

## 🗂️ Struktur Folder
```bash
project-root/
│
├── public/ # Root untuk akses web server
│ ├── uploads/ # Folder upload file/media
│ └── index.php
│
├── src/ # Kode utama aplikasi
│ ├── controllers/ # Semua controller
│ ├── models/ # Semua model (akses DB)
│ ├── helpers/ # Helper class seperti ResponseHandler, JWT, dsb
│ └── config/ # File konfigurasi DB
│
├── seed.php # Seeder awal untuk user & kategori
├── composer.json
├── .env
├── .gitignore
└── README.md
```
---

## ⚙️ Instalasi

### 1. Clone Repo

```bash
git clone https://github.com/username/announcement-news-api.git
cd announcement-news-api


```bash
composer install


DB_HOST=localhost
DB_NAME=announcement_news
DB_USER=root
DB_PASS=


mysql -u root -p announcement_news < schema.sql
php seed.php

