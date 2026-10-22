
<?php
require_once 'config.php';

// Only logged-in users can create posts
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$title = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $error = 'Please fill in both title and content.';
    } elseif (strlen($title) > 255) {
        $error = 'Title must be 255 characters or fewer.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO posts (title, content) VALUES (:title, :content)'
        );

        $stmt->execute([
            'title' => $title,
            'content' => $content
        ]);

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post | ApexPlanet Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container form-page">
        <h1>Create a New Post</h1>
        <p>Share your thoughts with the world.</p>

        <?php if ($error !== ''): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="create.php">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrf_token()) ?>">

            <label for="title">Post Title</label>
            <input
                type="text"
                id="title"
                name="title"
                maxlength="255"
                value="<?= e($title) ?>"
                required
            >

            <label for="content">Post Content</label>
            <textarea
                id="content"
                name="content"
                rows="8"
                required
            ><?= e($content) ?></textarea>

            <button type="submit">Publish Post</button>
            <a class="back-link" href="index.php">Cancel</a>
        </form>
    </main>
</body>
</html>
