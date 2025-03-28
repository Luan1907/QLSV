<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

// Nhận dữ liệu form
$username = $_POST['username'] ?? $_SESSION['user']['username'];
$full_name = $_POST['full_name'] ?? $_SESSION['user']['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = $_POST['password'];

// Xử lý avatar mới nếu có
$avatar_sql = '';
if (!empty($_FILES['avatar']['name'])) {
    $avatar_name = time() . '_' . basename($_FILES['avatar']['name']);
    $target = "../uploads/avatars/" . $avatar_name;
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
        $avatar_sql = ", avatar = '$avatar_name'";
    }
}

try {
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($role == 'teacher') {
            // Giáo viên update đầy đủ
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, full_name = ?, email = ?, phone = ? $avatar_sql WHERE id = ?");
            $stmt->execute([$username, $hashed_password, $full_name, $email, $phone, $id]);
        } else {
            // Sinh viên chỉ update 1 số trường
            $stmt = $pdo->prepare("UPDATE users SET password = ?, email = ?, phone = ? $avatar_sql WHERE id = ?");
            $stmt->execute([$hashed_password, $email, $phone, $id]);
        }
    } else {
        if ($role == 'teacher') {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, phone = ? $avatar_sql WHERE id = ?");
            $stmt->execute([$username, $full_name, $email, $phone, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET email = ?, phone = ? $avatar_sql WHERE id = ?");
            $stmt->execute([$email, $phone, $id]);
        }
    }

    // Cập nhật session sau khi sửa
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['user'] = $stmt->fetch();

    header('Location: ../profile.php');
} catch (PDOException $e) {
    die("Lỗi cập nhật: " . $e->getMessage());
}

?>
