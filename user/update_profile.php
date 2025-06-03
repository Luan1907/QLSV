<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

$username   = $_POST['username'] ?? $_SESSION['user']['username'];
$full_name  = $_POST['full_name'] ?? $_SESSION['user']['full_name'];
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$password   = $_POST['password'] ?? '';

$avatar = '';
$params = [];

// Xử lý avatar nếu có
if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === 0) {
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    $file_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $file_size = $_FILES['avatar']['size'];
    $tmp_path = $_FILES['avatar']['tmp_name'];

    if (!in_array($file_ext, $allowed_ext)) {
        header('Location: ../profile.php?error=invalid_avatar_format');
        exit;
    }

    if ($file_size > $max_size) {
        header('Location: ../profile.php?error=avatar_too_large');
        exit;
    }

    $avatar = time() . '_' . basename($_FILES['avatar']['name']);
    $target = "../uploads/avatars/" . $avatar;

    if (!is_dir("../uploads/avatars")) {
        mkdir("../uploads/avatars", 0777, true);
    }

    if (!move_uploaded_file($tmp_path, $target)) {
        header('Location: ../profile.php?error=avatar_upload_failed');
        exit;
    }
}

try {
    // Build câu lệnh SQL động tùy theo role và có avatar hay không
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($role === 'teacher') {
            $sql = "UPDATE users SET username = ?, password = ?, full_name = ?, email = ?, phone = ?";
            $params = [$username, $hashed_password, $full_name, $email, $phone];
        } else {
            $sql = "UPDATE users SET password = ?, email = ?, phone = ?";
            $params = [$hashed_password, $email, $phone];
        }
    } else {
        if ($role === 'teacher') {
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?";
            $params = [$username, $full_name, $email, $phone];
        } else {
            $sql = "UPDATE users SET email = ?, phone = ?";
            $params = [$email, $phone];
        }
    }

    // Thêm avatar nếu có
    if (!empty($avatar)) {
        $sql .= ", avatar = ?";
        $params[] = $avatar;
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Cập nhật lại session
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['user'] = $stmt->fetch();

    header('Location: ../profile.php?success=updated');
    exit;

} catch (PDOException $e) {
    // Ghi log nếu cần: error_log($e->getMessage());
    header('Location: ../profile.php?error=update_failed');
    exit;
}
?>
