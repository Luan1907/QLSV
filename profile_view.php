<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];

// Lấy ID người dùng từ URL
if (!isset($_GET['id'])) {
    die('Thiếu ID người dùng');
}

$profile_id = $_GET['id'];

// Lấy thông tin người dùng
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_id]);
$profile = $stmt->fetch();
if (!$profile) {
    die('Người dùng không tồn tại');
}

// Xử lý thêm tin nhắn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $profile_id, $msg]);
    header("Location: profile_view.php?id=" . $profile_id);
    exit;
}



// Lấy danh sách tin nhắn đã gửi
$stmt = $pdo->prepare("SELECT * FROM messages WHERE sender_id = ? AND receiver_id = ?");
$stmt->execute([$user['id'], $profile_id]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">



<?php include 'includes/sidebar.php'; ?>


<div class="main-content">
    <h2>Thông tin của <?= htmlspecialchars($profile['full_name']) ?></h2>
    <div class="profile-card">
        <?php if ($profile['avatar']): ?>
            <img src="uploads/avatars/<?= htmlspecialchars($profile['avatar']) ?>" alt="Avatar" width="100">
        <?php else: ?>
            <img src="default-avatar.jpg" alt="Avatar" width="100" height: "100" border-radius: 50%>
        <?php endif; ?>
        <p><strong>Tên đăng nhập:</strong> <?= htmlspecialchars($profile['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($profile['email']) ?></p>
        <p><strong>SĐT:</strong> <?= htmlspecialchars($profile['phone']) ?></p>
        <p><strong>Vai trò:</strong> <?= $profile['role'] == 'teacher' ? 'Giáo viên' : 'Sinh viên' ?></p>
    </div>




    <!-- Form để lại tin nhắn -->
    <div class="message">
    <h3>Để lại tin nhắn cho <?= htmlspecialchars($profile['full_name']) ?></h3>
    <form method="POST">
        <textarea class="textarea" name="message" rows="4" cols="50" required></textarea><br>
        <button type="submit">Gửi tin nhắn</button>
    </form>


    <!-- Danh sách tin nhắn -->
     
    <h3>Tin nhắn đã gửi</h3>
    <?php if (count($messages) > 0): ?>
        <ul>
            <?php foreach ($messages as $msg): ?>
                <li>
                    <?= htmlspecialchars($msg['content']) ?>
                    <br>
                    <small>Gửi lúc: <?= date('H:i d/m/Y', strtotime($msg['created_at'])) ?></small>
                    <br>
                    <a href="delete_message.php?id=<?= $msg['id'] ?>&profile_id=<?= $profile_id ?>"
                        onclick="return confirm('Xóa tin nhắn này?')">Xóa</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Chưa có tin nhắn nào.</p>
    <?php endif; ?>
    </div>

</div>
</body>

</html>