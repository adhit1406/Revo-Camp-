<?php
require_once 'includes/config.php';

// Pagination
$items_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Filter kategori
$category_filter = isset($_GET['category']) ? clean_input($_GET['category']) : '';

// Query berita
$where = "WHERE n.status = 'published'";
if ($category_filter) {
    $where .= " AND c.slug = '$category_filter'";
}

$query = "SELECT n.*, c.nama as category_name, c.slug as category_slug, u.nama as author_name 
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          LEFT JOIN users u ON n.author_id = u.id 
          $where
          ORDER BY n.created_at DESC 
          LIMIT $items_per_page OFFSET $offset";

$result = $conn->query($query);

// Total berita untuk pagination
$total_query = "SELECT COUNT(*) as total FROM news n LEFT JOIN categories c ON n.category_id = c.id $where";
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Get semua kategori
$categories_query = "SELECT * FROM categories ORDER BY nama ASC";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_DESCRIPTION; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php"><?php echo SITE_NAME; ?></a></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php">Berita</a></li>
                    <li><a href="admin/login.php">Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h2 style="margin-bottom: 1rem;">Berita Terkini</h2>
            
            <!-- Filter Kategori -->
            <div style="margin-bottom: 2rem;">
                <a href="index.php" class="btn <?php echo !$category_filter ? 'active' : 'btn-secondary'; ?>">Semua</a>
                <?php while($cat = $categories_result->fetch_assoc()): ?>
                    <a href="index.php?category=<?php echo $cat['slug']; ?>" 
                       class="btn <?php echo $category_filter == $cat['slug'] ? 'active' : 'btn-secondary'; ?>">
                        <?php echo $cat['nama']; ?>
                    </a>
                <?php endwhile; ?>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="news-grid">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <article class="news-card">
                            <?php if ($row['gambar']): ?>
                                <img src="assets/uploads/<?php echo $row['gambar']; ?>" 
                                     alt="<?php echo htmlspecialchars($row['judul']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x200?text=No+Image" 
                                     alt="No Image">
                            <?php endif; ?>
                            
                            <div class="news-card-content">
                                <span class="news-category"><?php echo $row['category_name']; ?></span>
                                <h3>
                                    <a href="pages/detail.php?slug=<?php echo $row['slug']; ?>">
                                        <?php echo htmlspecialchars($row['judul']); ?>
                                    </a>
                                </h3>
                                <p class="news-excerpt">
                                    <?php echo substr(strip_tags($row['konten']), 0, 150) . '...'; ?>
                                </p>
                                <div class="news-meta">
                                    <span>📅 <?php echo format_date($row['created_at']); ?></span>
                                    <span>👁️ <?php echo $row['views']; ?> views</span>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div style="text-align: center; margin-top: 3rem;">
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="index.php?page=<?php echo $i; ?><?php echo $category_filter ? '&category='.$category_filter : ''; ?>" 
                               class="btn <?php echo $page == $i ? 'active' : 'btn-secondary'; ?>" 
                               style="margin: 0 0.2rem;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info">
                    <p>Belum ada berita yang dipublikasikan.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p>Dibuat dengan PHP & MySQL</p>
        </div>
    </footer>
</body>
</html>
