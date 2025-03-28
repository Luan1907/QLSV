<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];
// Lấy tin nhắn gửi đến user hiện tại
$stmt = $pdo->prepare("SELECT m.*, u.full_name AS sender_name FROM messages m 
                       JOIN users u ON m.sender_id = u.id 
                       WHERE m.receiver_id = ?");
$stmt->execute([$user['id']]);
$received_messages = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">

</head>

<?php include 'includes/sidebar.php'; ?>


<div class="main-content">
    <h2>Thông tin cá nhân</h2>
    <div class="profile-card">
        <?php if ($user['avatar']): ?>
            <img src="uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
        <?php else: ?>
            <img src="default-avatar.jpg" alt="Avatar">
        <?php endif; ?>
        <h3><?= htmlspecialchars($user['full_name']) ?></h3>
        <p><strong>Tên đăng nhập:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>SĐT:</strong> <?= htmlspecialchars($user['phone']) ?></p>
        <p><strong>Vai trò:</strong> <?= $user['role'] == 'teacher' ? 'Giáo viên' : 'Sinh viên' ?></p>
        <button class = "editProfileBtn" id="editProfileBtn">Thay đổi thông tin</button>
    </div>

    <!-- Nút thay đổi thông tin -->
    

    <!-- Popup form -->
    <div id="editProfileModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Chỉnh sửa thông tin cá nhân</h3>
            <form action="user/update_profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">

                <?php if ($user['role'] == 'teacher'): ?>
                    <label>Tên đăng nhập:</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    <label>Họ tên:</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                <?php else: ?>
                    <p><strong>Tên đăng nhập:</strong> <?= htmlspecialchars($user['username']) ?></p>
                    <p><strong>Họ tên:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
                <?php endif; ?>

                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                <label>SĐT:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>

                <label>Mật khẩu mới (bỏ trống nếu không đổi):</label>
                <input type="password" name="password">

                <label>Avatar mới (nếu có):</label>
                <input type="file" name="avatar">

                <button type="submit">Lưu thay đổi</button>
            </form>
        </div>
    </div>

    <script>
        // JS mở modal
        document.getElementById('editProfileBtn').onclick = function () {
            document.getElementById('editProfileModal').style.display = 'block';
        }
        // JS đóng modal
        document.querySelector('.close').onclick = function () {
            document.getElementById('editProfileModal').style.display = 'none';
        }
    </script>
    <div class="message">
    <h3>Tin nhắn gửi đến bạn</h3>
    <?php if (count($received_messages) > 0): ?>
        <ul>
            <?php foreach ($received_messages as $msg): ?>
                <li>
                    <strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong>
                    <?= htmlspecialchars($msg['content']) ?>
                    <br>
                    <small>Gửi lúc: <?= date('H:i d/m/Y', strtotime($msg['created_at'])) ?></small>
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