<?php
require_once '../includes/config.php';

// Cek login
if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

// Get statistik
$stats = [];

$stats['total_news'] = $conn->query("SELECT COUNT(*) as total FROM news")->fetch_assoc()['total'];
$stats['published_news'] = $conn->query("SELECT COUNT(*) as total FROM news WHERE status = 'published'")->fetch_assoc()['total'];
$stats['draft_news'] = $conn->query("SELECT COUNT(*) as total FROM news WHERE status = 'draft'")->fetch_assoc()['total'];
$stats['total_views'] = $conn->query("SELECT SUM(views) as total FROM news")->fetch_assoc()['total'] ?? 0;
$stats['total_categories'] = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];

// Get berita terbaru
$recent_news = $conn->query("SELECT n.*, c.nama as category_name 
                             FROM news n 
                             LEFT JOIN categories c ON n.category_id = c.id 
                             ORDER BY n.created_at DESC 
                             LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?php echo SITE_NAME; ?></title>
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
            <h2>Dashboard</h2>
        </div>

        <div class="admin-nav">
            <a href="index.php" class="active">Dashboard</a>
            <a href="news.php">Kelola Berita</a>
            <a href="categories.php">Kategori</a>
        </div>

        <div class="admin-content" style="margin-top: 2rem;">
            <!-- Statistik -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                    <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $stats['total_news']; ?></h3>
                    <p>Total Berita</p>
                </div>
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                    <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $stats['published_news']; ?></h3>
                    <p>Berita Published</p>
                </div>
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                    <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $stats['draft_news']; ?></h3>
                    <p>Draft</p>
                </div>
                <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                    <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo number_format($stats['total_views']); ?></h3>
                    <p>Total Views</p>
                </div>
                <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                    <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $stats['total_categories']; ?></h3>
                    <p>Kategori</p>
                </div>
            </div>

            <!-- Berita Terbaru -->
            <h3 style="margin-bottom: 1rem;">Berita Terbaru</h3>
            <table>
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($news = $recent_news->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($news['judul']); ?></td>
                            <td><?php echo $news['category_name']; ?></td>
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
                                    <a href="news-edit.php?id=<?php echo $news['id']; ?>" class="btn btn-secondary">Edit</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div style="margin-top: 1.5rem;">
                <a href="news.php" class="btn">Lihat Semua Berita →</a>
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
