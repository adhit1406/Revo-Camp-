<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

$error = '';
$success = '';

// Get semua kategori
$categories = $conn->query("SELECT * FROM categories ORDER BY nama ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = clean_input($_POST['judul']);
    $slug = create_slug($judul);
    $konten = clean_input($_POST['konten']);
    $category_id = (int)$_POST['category_id'];
    $status = clean_input($_POST['status']);
    $author_id = $_SESSION['admin_id'];
    
    // Cek slug unik
    $check = $conn->query("SELECT id FROM news WHERE slug = '$slug'");
    if ($check->num_rows > 0) {
        $slug .= '-' . time();
    }
    
    // Upload gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $gambar = time() . '_' . $filename;
            move_uploaded_file($_FILES['gambar']['tmp_name'], "../assets/uploads/" . $gambar);
        } else {
            $error = 'Format gambar tidak valid. Gunakan JPG, PNG, atau GIF.';
        }
    }
    
    if (!$error) {
        $query = "INSERT INTO news (judul, slug, konten, gambar, category_id, author_id, status) 
                  VALUES ('$judul', '$slug', '$konten', '$gambar', $category_id, $author_id, '$status')";
        
        if ($conn->query($query)) {
            $_SESSION['success'] = 'Berita berhasil ditambahkan!';
            redirect('news.php');
        } else {
            $error = 'Gagal menambahkan berita: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Berita - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">Admin Panel - <?php echo SITE_NAME; ?></a></h1>
            <nav>
                <ul>
                    <li><a href="../index.php" target="_blank">Lihat Website</a></li>
                    <li><a href="logout.php">Logout (<?php echo $_SESSION['admin_name']; ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <h2>Tambah Berita Baru</h2>
            <a href="news.php" class="btn btn-secondary">← Kembali</a>
        </div>

        <div class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="news.php" class="active">Kelola Berita</a>
            <a href="categories.php">Kategori</a>
        </div>

        <div class="admin-content" style="margin-top: 2rem;">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="judul">Judul Berita *</label>
                    <input type="text" id="judul" name="judul" required 
                           placeholder="Masukkan judul berita">
                </div>

                <div class="form-group">
                    <label for="category_id">Kategori *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nama']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gambar">Gambar Utama</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <small style="color: #666; display: block; margin-top: 0.5rem;">
                        Format: JPG, PNG, GIF. Max 2MB. (Opsional)
                    </small>
                </div>

                <div class="form-group">
                    <label for="konten">Konten Berita *</label>
                    <textarea id="konten" name="konten" required 
                              placeholder="Tulis konten berita di sini..."></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn">Simpan Berita</button>
                    <a href="news.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
