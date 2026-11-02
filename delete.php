
<?php
require_once 'config.php';

// Only logged-in users can delete posts
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Deletion must use POST, not a simple URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Verify the CSRF token
verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: index.php');
    exit;
}

if (is_admin()) {
    $stmt = $pdo->prepare(
        'DELETE FROM posts WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
} else {
    $stmt = $pdo->prepare(
        'DELETE FROM posts
         WHERE id = :id AND user_id = :user_id'
    );

    $stmt->execute([
        'id' => $id,
        'user_id' => $_SESSION['user_id']
    ]);
}
header('Location: index.php');
exit;
