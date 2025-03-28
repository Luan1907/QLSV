<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    header('Location: ../index.php');
    exit;
}

$id = $_POST['id'];
$username = $_POST['username'];
$password = $_POST['password'];
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];

// Xử lý avatar mới nếu có
$avatar_sql = '';
$params = [];

if (!empty($_FILES['avatar']['name'])) {
    $avatar_name = time() . '_' . basename($_FILES['avatar']['name']);
    $target = "../uploads/avatars/" . $avatar_name;
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
        $avatar_sql = ", avatar = ?";
        $params[] = $avatar_name;
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

    header('Location: ../students.php');
    exit;
} catch (PDOException $e) {
    // Bạn có thể xử lý lỗi cụ thể hơn nếu cần
    header('Location: ../students.php?error=duplicate');
    exit;
}
?>