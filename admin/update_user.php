<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    header('Location: ../index.php');
    exit;
}

// Nhận dữ liệu từ form
$id = $_POST['id'] ?? null;
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// Kiểm tra dữ liệu bắt buộc
if (!$id || !$username || !$full_name || !$email) {
    header("Location: ../students.php?error=missing_fields");
    exit;
}

// Xử lý avatar nếu có
$avatar_sql = '';
$params = [];

if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === 0) {
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    $original_name = $_FILES['avatar']['name'];
    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $file_size = $_FILES['avatar']['size'];
    $tmp_path = $_FILES['avatar']['tmp_name'];

    if (!in_array($file_ext, $allowed_ext)) {
        header("Location: ../students.php?error=invalid_avatar_format");
        exit;
    }

    if ($file_size > $max_size) {
        header("Location: ../students.php?error=avatar_too_large");
        exit;
    }

    $avatar_name = time() . '_' . basename($original_name);
    $target_path = "../uploads/avatars/" . $avatar_name;

    if (move_uploaded_file($tmp_path, $target_path)) {
        $avatar_sql = ", avatar = ?";
        $params[] = $avatar_name;
    } else {
        header("Location: ../students.php?error=upload_failed");
        exit;
    }
}

try {
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, password = ?, full_name = ?, email = ?, phone = ? $avatar_sql WHERE id = ?";
        $params = array_merge([$username, $hashed_password, $full_name, $email, $phone], $params, [$id]);
    } else {
        $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ? $avatar_sql WHERE id = ?";
        $params = array_merge([$username, $full_name, $email, $phone], $params, [$id]);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header('Location: ../students.php?success=updated');
    exit;
} catch (PDOException $e) {
    // Có thể ghi log lỗi chi tiết ra file nếu cần
    header('Location: ../students.php?error=duplicate_or_query_failed');
    exit;
}
?>
