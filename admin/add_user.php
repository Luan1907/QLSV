<?php
/* session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    header('Location: ../dashboard.php');
    exit;
}

if (isset($_POST['add'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Upload avatar nếu có
    $avatar = '';
    if (!empty($_FILES['avatar']['name'])) {
        $avatar_name = time() . '_' . basename($_FILES['avatar']['name']);
        $target = "../uploads/avatars/" . $avatar_name;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            $avatar = $avatar_name;
        }
    }

    // Thêm vào DB
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, avatar, role) VALUES (?, ?, ?, ?, ?, ?, 'student')");
    try {
        $stmt->execute([$username, $password, $full_name, $email, $phone, $avatar]);
        header('Location: ../dashboard.php');
        exit;
    } catch (PDOException $e) {
        $error = "Lỗi: Tên đăng nhập đã tồn tại hoặc dữ liệu không hợp lệ.";
    }
}
?>
<link rel="stylesheet" href="../css/style.css">
<div class="container">
    <h2>Thêm sinh viên mới</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <input type="text" name="full_name" placeholder="Họ tên" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Số điện thoại">
        <p>Avatar: <input type="file" name="avatar"></p>
        <button type="submit" name="add">Thêm sinh viên</button>
        <a href="../dashboard.php">Quay lại</a>
    </form>
</div>
*/
session_start();
require '../includes/db.php';
if (isset($_POST['add_student'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $avatar = '';
    if (!empty($_FILES['avatar']['name'])) {
        $avatar_name = time() . '_' . basename($_FILES['avatar']['name']);
        $target = "uploads/avatars/" . $avatar_name;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            $avatar = $avatar_name;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, avatar, role) VALUES (?, ?, ?, ?, ?, ?, 'student')");
        $stmt->execute([$username, $password, $full_name, $email, $phone, $avatar]);
        header('Location: ../students.php');
        exit;
    } catch (PDOException $e) {
        $error = "Tên đăng nhập đã tồn tại!";
    }
    header('Location: ../students.php');
}
?>