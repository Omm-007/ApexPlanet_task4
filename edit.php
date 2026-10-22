
<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, title, content FROM posts WHERE id = :id'
);
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    exit('Post not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $error = 'Please fill in both title and content.';
    } elseif (strlen($title) > 255) {
        $error = 'Title must be 255 characters or fewer.';
    } else {
        $update = $pdo->prepare(
            'UPDATE posts SET title = :title, content = :content WHERE id = :id'
        );

        $update->execute([
            'title' => $title,
            'content' => $content,
            'id' => $id
        ]);

        header('Location: index.php');
        exit;
    }

    $post['title'] = $title;
    $post['content'] = $content;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post | ApexPlanet Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container form-page">
        <h1>Edit Blog Post</h1>
        <p>Update your post below.</p>

        <?php if ($error !== ''): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?= (int) $post['id'] ?>">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrf_token()) ?>">

            <label for="title">Post Title</label>
            <input
                type="text"
                id="title"
                name="title"
                maxlength="255"
                value="<?= e($post['title']) ?>"
                required
            >

            <label for="content">Post Content</label>
            <textarea
                id="content"
                name="content"
                rows="8"
                required
            ><?= e($post['content']) ?></textarea>

            <button type="submit">Save Changes</button>
            <a class="back-link" href="index.php">Cancel</a>
        </form>
    </main>
</body>
</html>
