<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    redirect('news.php');
}

$error = '';

// Get data berita
$query = "SELECT * FROM news WHERE id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    redirect('news.php');
}

$news = $result->fetch_assoc();

// Get semua kategori
$categories = $conn->query("SELECT * FROM categories ORDER BY nama ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = clean_input($_POST['judul']);
    $slug = create_slug($judul);
    $konten = clean_input($_POST['konten']);
    $category_id = (int)$_POST['category_id'];
    $status = clean_input($_POST['status']);
    
    // Cek slug unik (kecuali untuk berita ini sendiri)
    $check = $conn->query("SELECT id FROM news WHERE slug = '$slug' AND id != $id");
    if ($check->num_rows > 0) {
        $slug .= '-' . time();
    }
    
    // Upload gambar baru jika ada
    $gambar = $news['gambar'];
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Hapus gambar lama
            if ($gambar && file_exists("../assets/uploads/" . $gambar)) {
                unlink("../assets/uploads/" . $gambar);
            }
            
            $gambar = time() . '_' . $filename;
            move_uploaded_file($_FILES['gambar']['tmp_name'], "../assets/uploads/" . $gambar);
        } else {
            $error = 'Format gambar tidak valid. Gunakan JPG, PNG, atau GIF.';
        }
    }
    
    if (!$error) {
        $query = "UPDATE news SET 
                  judul = '$judul', 
                  slug = '$slug', 
                  konten = '$konten', 
                  gambar = '$gambar', 
                  category_id = $category_id, 
                  status = '$status' 
                  WHERE id = $id";
        
        if ($conn->query($query)) {
            $_SESSION['success'] = 'Berita berhasil diupdate!';
            redirect('news.php');
        } else {
            $error = 'Gagal mengupdate berita: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berita - <?php echo SITE_NAME; ?></title>
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
            <h2>Edit Berita</h2>
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
                           value="<?php echo htmlspecialchars($news['judul']); ?>">
                </div>

                <div class="form-group">
                    <label for="category_id">Kategori *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $cat['id'] == $news['category_id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['nama']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gambar">Gambar Utama</label>
                    <?php if ($news['gambar']): ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="../assets/uploads/<?php echo $news['gambar']; ?>" 
                                 alt="Current" 
                                 style="max-width: 300px; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <small style="color: #666; display: block; margin-top: 0.5rem;">
                        Upload gambar baru untuk mengganti gambar saat ini. (Opsional)
                    </small>
                </div>

                <div class="form-group">
                    <label for="konten">Konten Berita *</label>
                    <textarea id="konten" name="konten" required><?php echo htmlspecialchars($news['konten']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="draft" <?php echo $news['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $news['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn">Update Berita</button>
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
