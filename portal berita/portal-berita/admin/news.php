<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

// Hapus berita
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Hapus gambar jika ada
    $query = "SELECT gambar FROM news WHERE id = $id";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $news = $result->fetch_assoc();
        if ($news['gambar'] && file_exists("../assets/uploads/" . $news['gambar'])) {
            unlink("../assets/uploads/" . $news['gambar']);
        }
    }
    
    $conn->query("DELETE FROM news WHERE id = $id");
    $_SESSION['success'] = 'Berita berhasil dihapus!';
    redirect('news.php');
}

// Get semua berita
$query = "SELECT n.*, c.nama as category_name, u.nama as author_name 
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          LEFT JOIN users u ON n.author_id = u.id 
          ORDER BY n.created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - <?php echo SITE_NAME; ?></title>
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
            <h2>Kelola Berita</h2>
            <a href="news-add.php" class="btn">+ Tambah Berita Baru</a>
        </div>

        <div class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="news.php" class="active">Kelola Berita</a>
            <a href="categories.php">Kategori</a>
        </div>

        <div class="admin-content" style="margin-top: 2rem;">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($news = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($news['gambar']): ?>
                                        <img src="../assets/uploads/<?php echo $news['gambar']; ?>" 
                                             alt="<?php echo htmlspecialchars($news['judul']); ?>">
                                    <?php else: ?>
                                        <div style="width: 80px; height: 60px; background: #ddd; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 0.8rem; color: #666;">
                                            No Image
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($news['judul']); ?></td>
                                <td><?php echo $news['category_name']; ?></td>
                                <td><?php echo $news['author_name']; ?></td>
                                <td>
                                    <span style="background: <?php echo $news['status'] == 'published' ? '#28a745' : '#ffc107'; ?>; 
                                                 color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem;">
                                        <?php echo ucfirst($news['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $news['views']; ?></td>
                                <td><?php echo format_date($news['created_at']); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="../pages/detail.php?slug=<?php echo $news['slug']; ?>" 
                                           class="btn btn-secondary" target="_blank">Lihat</a>
                                        <a href="news-edit.php?id=<?php echo $news['id']; ?>" 
                                           class="btn btn-secondary">Edit</a>
                                        <a href="news.php?delete=<?php echo $news['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus berita ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Belum ada berita.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
