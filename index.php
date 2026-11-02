


<?php
require_once 'config.php';

$isLoggedIn = isset($_SESSION['user_id']);

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

if ($search !== '') {
    $countStmt = $pdo->prepare(
        'SELECT COUNT(*) FROM posts
         WHERE title LIKE :title_search
            OR content LIKE :content_search'
    );

    $searchTerm = '%' . $search . '%';

    $countStmt->execute([
        'title_search' => $searchTerm,
        'content_search' => $searchTerm
    ]);

    $totalPosts = (int) $countStmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT id, title, content, created_at
         FROM posts
         WHERE title LIKE :title_search
            OR content LIKE :content_search
         ORDER BY created_at DESC
         LIMIT :limit OFFSET :offset'
    );

    $stmt->bindValue(':title_search', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':content_search', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $countStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM posts'
);

$countStmt->execute();

$totalPosts = (int) $countStmt->fetchColumn();
    $stmt = $pdo->prepare(
        'SELECT id, title, content, created_at
         FROM posts
         ORDER BY created_at DESC
         LIMIT :limit OFFSET :offset'
    );

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$totalPages = max(1, (int) ceil($totalPosts / $limit));
$posts = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApexPlanet Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="index.php">ApexPlanet Blog</a>

            <nav>
                <?php if ($isLoggedIn): ?>
                    <span>Welcome, <?= e($_SESSION['username'] ?? 'User') ?></span>
                    <a class="button-link" href="create.php">New Post</a>

                    <form class="inline-form" method="POST" action="logout.php">
                        <input type="hidden" name="csrf_token"
                               value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="secondary-button">
                            Logout
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a class="button-link" href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <p class="eyebrow">SHARE YOUR IDEAS</p>
            <h1>Welcome to the Blog</h1>
            <p>Create, read, update and delete blog posts in one place.</p>

            <?php if (!$isLoggedIn): ?>
                <a class="button-link" href="register.php">Get Started</a>
            <?php endif; ?>
        </section>

        <section class="posts-section">
            <h2>Latest Posts</h2>
            
<form method="GET" action="index.php" class="search-form">
    <input
        type="search"
        name="search"
        placeholder="Search posts by title or content..."
        value="<?= e($search) ?>"
    >
    <button type="submit" class="button-link">Search</button>

    <?php if ($search !== ''): ?>
        <a href="index.php">Clear Search</a>
    <?php endif; ?>
</form>


            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <h3>No posts yet</h3>
                    <p>
                        <?php if ($isLoggedIn): ?>
                            Create your first blog post to get started.
                        <?php else: ?>
                            Register or log in to publish your first post.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="posts-grid">
                    <?php if (empty($posts)): ?>
    <p class="no-posts">
            <?php if ($search !== ''): ?>
                No posts found for "<?= e($search) ?>". Try a different search.
            <?php else: ?>
                No posts available yet. Be the first to create one!
            <?php endif; ?>
    </p>
<?php endif; ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <p class="post-date">
                                <?= e($post['created_at']) ?>
                            </p>

                            <h3><?= e($post['title']) ?></h3>

                            <div class="post-content"><?= nl2br(e($post['content'])) ?></div>

                            <?php if ($isLoggedIn): ?>
                                <div class="post-actions">
                                    <a class="button-link"
                                       href="edit.php?id=<?= (int) $post['id'] ?>">
                                        Edit
                                    </a>

                                    <form method="POST" action="delete.php"
                                          onsubmit="return confirm('Delete this post?');">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id"
                                               value="<?= (int) $post['id'] ?>">
                                        <button type="submit" class="danger-button">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

<?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Post pages">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query([
                'search' => $search,
                'page' => $page - 1
            ]) ?>">Previous</a>
        <?php endif; ?>

        <span>Page <?= $page ?> of <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query([
                'search' => $search,
                'page' => $page + 1
            ]) ?>">Next</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>

    <footer class="site-footer">
        <p>Secure Blog Application · ApexPlanet Internship Task 4</p>
    </footer>
</body>
</html>
