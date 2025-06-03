<?php
session_start();
require '../includes/db.php';

// Chỉ cho giáo viên thêm sinh viên
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header('Location: ../index.php');
    exit;
}

// Kiểm tra nút submit
if (!isset($_POST['add_student'])) {
    header('Location: ../students.php');
    exit;
}

// Lấy và làm sạch dữ liệu
$username   = trim($_POST['username'] ?? '');
$password   = $_POST['password'] ?? '';
$full_name  = trim($_POST['full_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');

if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
    header('Location: ../students.php?error=missing_fields');
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Xử lý avatar nếu có
$avatar = '';
if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === 0) {
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    $file_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $file_size = $_FILES['avatar']['size'];
    $tmp_path = $_FILES['avatar']['tmp_name'];

    if (!in_array($file_ext, $allowed_ext)) {
        header('Location: ../students.php?error=invalid_avatar_format');
        exit;
    }

    if ($file_size > $max_size) {
        header('Location: ../students.php?error=avatar_too_large');
        exit;
    }

    $avatar_name = time() . '_' . basename($_FILES['avatar']['name']);
    $target_dir = "../uploads/avatars/";
    $target_path = $target_dir . $avatar_name;

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!move_uploaded_file($tmp_path, $target_path)) {
        header('Location: ../students.php?error=avatar_upload_failed');
        exit;
    }

    $avatar = $avatar_name;
}

// Kiểm tra username có tồn tại không
$stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt_check->execute([$username]);

if ($stmt_check->rowCount() > 0) {
    header('Location: ../students.php?error=username_exists');
    exit;
}

// Chèn dữ liệu vào DB
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, avatar, role) VALUES (?, ?, ?, ?, ?, ?, 'student')");
    $stmt->execute([$username, $hashed_password, $full_name, $email, $phone, $avatar]);

    header('Location: ../students.php?success=added');
    exit;
} catch (PDOException $e) {
    // Log lỗi nếu cần: error_log($e->getMessage());
    header('Location: ../students.php?error=insert_failed');
    exit;
}
?>
