-- Database Portal Berita
-- Jalankan file ini di phpMyAdmin atau MySQL

CREATE DATABASE IF NOT EXISTS portal_berita;
USE portal_berita;

-- Tabel Admin/User
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Berita/Artikel
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    konten TEXT NOT NULL,
    gambar VARCHAR(255),
    category_id INT,
    author_id INT,
    views INT DEFAULT 0,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert data awal
-- Password default: admin123 (sudah di-hash)
INSERT INTO users (username, password, nama, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@portalberita.com');

-- Kategori default
INSERT INTO categories (nama, slug) VALUES 
('Berita Umum', 'berita-umum'),
('Teknologi', 'teknologi'),
('Bisnis', 'bisnis'),
('UMKM', 'umkm'),
('Event', 'event');

-- Berita contoh
INSERT INTO news (judul, slug, konten, category_id, author_id, status) VALUES 
('Selamat Datang di Portal Berita', 'selamat-datang-di-portal-berita', 
'Ini adalah artikel pertama di portal berita Anda. Anda dapat mengedit atau menghapus artikel ini melalui admin panel.

Untuk login ke admin panel, gunakan:
Username: admin
Password: admin123

Selamat mengelola website Anda!', 1, 1, 'published');
