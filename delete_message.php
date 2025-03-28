<?php
session_start();
require 'includes/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'];

// Kiểm tra ID tin nhắn hợp lệ
$message_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$profile_id = filter_input(INPUT_GET, 'profile_id', FILTER_VALIDATE_INT);

if (!$message_id || !$profile_id) {
    die('Yêu cầu không hợp lệ.');
}

// Kiểm tra tin nhắn có tồn tại không và người dùng có quyền xóa không
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ? AND sender_id = ?");
$stmt->execute([$message_id, $user['id']]);
$message = $stmt->fetch();

if (!$message) {
    die('Tin nhắn không tồn tại hoặc bạn không có quyền xóa.');
}

// Tiến hành xóa tin nhắn
$stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
$stmt->execute([$message_id]);

// Quay lại trang hồ sơ
header("Location: profile_view.php?id=" . $profile_id);
exit;
?>
