<?php
session_start();
require 'includes/db.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        header('Location: profile.php');
        exit;
    } else {
        $error = "Sai tên đăng nhập hoặc mật khẩu!";
    }
}


?>

<link rel="stylesheet" href="css/style.css"> 
<div class="container">
    <h2>Đăng nhập</h2>
    <?php if (isset($error))
        echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit" name="login">Đăng nhập</button>
    </form>
</div>