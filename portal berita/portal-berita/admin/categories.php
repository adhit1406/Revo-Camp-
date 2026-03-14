<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

$error = '';
$success = '';

// Tambah kategori
if (isset($_POST['add_category'])) {
    $nama = clean_input($_POST['nama']);
    $slug = create_slug($nama);
    
    // Cek slug unik
    $check = $conn->query("SELECT id FROM categories WHERE slug = '$slug'");
    if ($check->num_rows > 0) {
        $error = 'Kategori dengan nama tersebut sudah ada!';
    } else {
        $query = "INSERT INTO categories (nama, slug) VALUES ('$nama', '$slug')";
        if ($conn->query($query)) {
            $success = 'Kategori berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan kategori: ' . $conn->error;
        }
    }
}

// Edit kategori
if (isset($_POST['edit_category'])) {
    $id = (int)$_POST['id'];
    $nama = clean_input($_POST['nama']);
    $slug = create_slug($nama);
    
    // Cek slug unik (kecuali untuk kategori ini sendiri)
    $check = $conn->query("SELECT id FROM categories WHERE slug = '$slug' AND id != $id");
    if ($check->num_rows > 0) {
        $error = 'Kategori dengan nama tersebut sudah ada!';
    } else {
        $query = "UPDATE categories SET nama = '$nama', slug = '$slug' WHERE id = $id";
        if ($conn->query($query)) {
            $success = 'Kategori berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate kategori: ' . $conn->error;
        }
    }
}

// Hapus kategori
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Cek apakah kategori digunakan
    $check = $conn->query("SELECT COUNT(*) as total FROM news WHERE category_id = $id");
    $used = $check->fetch_assoc()['total'];
    
    if ($used > 0) {
        $error = "Kategori tidak dapat dihapus karena masih digunakan oleh $used berita!";
    } else {
        $conn->query("DELETE FROM categories WHERE id = $id");
        $success = 'Kategori berhasil dihapus!';
    }
}

// Get semua kategori
$categories = $conn->query("SELECT c.*, COUNT(n.id) as news_count 
                            FROM categories c 
                            LEFT JOIN news n ON c.id = n.category_id 
                            GROUP BY c.id 
                            ORDER BY c.nama ASC");

// Get kategori untuk edit
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM categories WHERE id = $edit_id");
    if ($result->num_rows > 0) {
        $edit_category = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - <?php echo SITE_NAME; ?></title>
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
            <h2>Kelola Kategori</h2>
        </div>

        <div class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="news.php">Kelola Berita</a>
            <a href="categories.php" class="active">Kategori</a>
        </div>

        <div class="admin-content" style="margin-top: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                <!-- Form Tambah/Edit -->
                <div>
                    <h3><?php echo $edit_category ? 'Edit' : 'Tambah'; ?> Kategori</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <?php if ($edit_category): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="nama">Nama Kategori *</label>
                            <input type="text" id="nama" name="nama" required 
                                   value="<?php echo $edit_category ? htmlspecialchars($edit_category['nama']) : ''; ?>"
                                   placeholder="Contoh: Teknologi">
                        </div>

                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" 
                                    name="<?php echo $edit_category ? 'edit_category' : 'add_category'; ?>" 
                                    class="btn">
                                <?php echo $edit_category ? 'Update' : 'Tambah'; ?>
                            </button>
                            
                            <?php if ($edit_category): ?>
                                <a href="categories.php" class="btn btn-secondary">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Daftar Kategori -->
                <div>
                    <h3>Daftar Kategori</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Slug</th>
                                <th>Jumlah Berita</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($categories->num_rows > 0): ?>
                                <?php while($cat = $categories->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($cat['nama']); ?></strong></td>
                                        <td><code><?php echo $cat['slug']; ?></code></td>
                                        <td><?php echo $cat['news_count']; ?> berita</td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="categories.php?edit=<?php echo $cat['id']; ?>" 
                                                   class="btn btn-secondary">Edit</a>
                                                <a href="categories.php?delete=<?php echo $cat['id']; ?>" 
                                                   class="btn btn-danger" 
                                                   onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">Belum ada kategori.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
