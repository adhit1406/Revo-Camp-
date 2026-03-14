<?php
require_once '../includes/config.php';

// Get berita berdasarkan slug
$slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';

if (!$slug) {
    redirect('../index.php');
}

// Update views
$conn->query("UPDATE news SET views = views + 1 WHERE slug = '$slug'");

// Get data berita
$query = "SELECT n.*, c.nama as category_name, c.slug as category_slug, u.nama as author_name 
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          LEFT JOIN users u ON n.author_id = u.id 
          WHERE n.slug = '$slug' AND n.status = 'published'";

$result = $conn->query($query);

if ($result->num_rows == 0) {
    redirect('../index.php');
}

$news = $result->fetch_assoc();

// Get berita terkait (kategori yang sama)
$related_query = "SELECT n.*, c.nama as category_name 
                  FROM news n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  WHERE n.category_id = {$news['category_id']} 
                  AND n.slug != '$slug' 
                  AND n.status = 'published'
                  ORDER BY n.created_at DESC 
                  LIMIT 3";
$related_result = $conn->query($related_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['judul']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo substr(strip_tags($news['konten']), 0, 160); ?>">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="../index.php"><?php echo SITE_NAME; ?></a></h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../index.php">Berita</a></li>
                    <li><a href="../admin/login.php">Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <article class="news-detail">
                <span class="news-category"><?php echo $news['category_name']; ?></span>
                
                <h1><?php echo htmlspecialchars($news['judul']); ?></h1>
                
                <div class="news-detail-meta">
                    <span>✍️ <?php echo $news['author_name']; ?></span>
                    <span>📅 <?php echo format_date($news['created_at']); ?></span>
                    <span>👁️ <?php echo $news['views']; ?> views</span>
                </div>

                <?php if ($news['gambar']): ?>
                    <img src="../assets/uploads/<?php echo $news['gambar']; ?>" 
                         alt="<?php echo htmlspecialchars($news['judul']); ?>">
                <?php endif; ?>

                <div class="news-detail-content">
                    <?php echo nl2br(htmlspecialchars($news['konten'])); ?>
                </div>
            </article>

            <!-- Berita Terkait -->
            <?php if ($related_result->num_rows > 0): ?>
                <div style="margin-top: 4rem;">
                    <h2 style="margin-bottom: 1.5rem;">Berita Terkait</h2>
                    <div class="news-grid">
                        <?php while($related = $related_result->fetch_assoc()): ?>
                            <article class="news-card">
                                <?php if ($related['gambar']): ?>
                                    <img src="../assets/uploads/<?php echo $related['gambar']; ?>" 
                                         alt="<?php echo htmlspecialchars($related['judul']); ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/400x200?text=No+Image" 
                                         alt="No Image">
                                <?php endif; ?>
                                
                                <div class="news-card-content">
                                    <span class="news-category"><?php echo $related['category_name']; ?></span>
                                    <h3>
                                        <a href="detail.php?slug=<?php echo $related['slug']; ?>">
                                            <?php echo htmlspecialchars($related['judul']); ?>
                                        </a>
                                    </h3>
                                    <p class="news-excerpt">
                                        <?php echo substr(strip_tags($related['konten']), 0, 100) . '...'; ?>
                                    </p>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div style="margin-top: 2rem;">
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
