<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];

// Lấy danh sách sinh viên
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'student'");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý thêm sinh viên

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Danh sách sinh viên</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Danh sách sinh viên</h2>

        <?php if ($user['role'] == 'teacher'): ?>
            <div class="add-btn" id="openModal">+ Thêm sinh viên</div>
        <?php endif; ?>

        <?php if (isset($error))
            echo "<p style='color:red;'>$error</p>"; ?>

        <table class="student-table">
            <tr>
                <th>Avatar</th>
                <th>Tên đăng nhập</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <?php if ($user['role'] == 'teacher'): ?>
                    <th>Hành động</th>
                <?php endif; ?>
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td>
                        <?php if ($student['avatar']): ?>
                            <img src="uploads/avatars/<?= htmlspecialchars($student['avatar']) ?>" alt="avatar" width="40"
                                height="40">
                        <?php else: ?>
                            <img src="default-avatar.jpg" alt="avatar" width="40" <?php endif; ?> </td>
                    <td>
                        <a href="profile_view.php?id=<?= $student['id'] ?>">
                            <?= htmlspecialchars($student['username']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td><?= htmlspecialchars($student['phone']) ?></td>
                    <?php if ($user['role'] == 'teacher'): ?>
                        <td class="actions">
                            <a href="#" class="edit-btn" data-id="<?= $student['id'] ?>"
                                data-username="<?= $student['username'] ?>"
                                data-fullname="<?= htmlspecialchars($student['full_name']) ?>"
                                data-email="<?= htmlspecialchars($student['email']) ?>"
                                data-phone="<?= htmlspecialchars($student['phone']) ?>">Sửa</a>
                            <a href="admin/delete_user.php?id=<?= $student['id'] ?>"
                                onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Modal THÊM sinh viên -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h3>Thêm sinh viên</h3>
            <form method="POST" enctype="multipart/form-data" action="admin/add_user.php">
                <input type="text" name="username" placeholder="Tên đăng nhập" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <input type="text" name="full_name" placeholder="Họ tên" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Số điện thoại">
                <p>Avatar: <input type="file" name="avatar"></p>
                <button type="submit" name="add_student">Thêm</button>
            </form>
        </div>
    </div>

    <!-- Modal SỬA sinh viên -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <span class="close" id="closeEditModal">&times;</span>
            <h3>Sửa sinh viên</h3>
            <form method="POST" enctype="multipart/form-data" action="admin/update_user.php">
                <input type="hidden" name="id" id="edit-id">
                <input type="text" name="username" id="edit-username" placeholder="Tên đăng nhập" required>
                <input type="password" name="password" id="edit-password" placeholder="Mật khẩu mới">
                <input type="text" name="full_name" id="edit-fullname" placeholder="Họ tên" required>
                <input type="email" name="email" id="edit-email" placeholder="Email" required>
                <input type="text" name="phone" id="edit-phone" placeholder="Số điện thoại">
                <p>Avatar mới (nếu muốn): <input type="file" name="avatar"></p>
                <button type="submit" name="edit_student">Cập nhật</button>
            </form>
        </div>
    </div>

    <script>
        const addModal = document.getElementById('addModal');
        const openAddBtn = document.getElementById('openModal');
        const closeAddBtn = document.getElementById('closeModal');
        openAddBtn.onclick = () => addModal.style.display = 'flex';
        closeAddBtn.onclick = () => addModal.style.display = 'none';

        const editModal = document.getElementById('editModal');
        const closeEditBtn = document.getElementById('closeEditModal');
        const editBtns = document.querySelectorAll('.edit-btn');

        editBtns.forEach(btn => {
            btn.onclick = () => {
                document.getElementById('edit-id').value = btn.dataset.id;
                document.getElementById('edit-username').value = btn.dataset.username;
                document.getElementById('edit-fullname').value = btn.dataset.fullname;
                document.getElementById('edit-email').value = btn.dataset.email;
                document.getElementById('edit-phone').value = btn.dataset.phone;
                editModal.style.display = 'flex';
            };
        });

        closeEditBtn.onclick = () => editModal.style.display = 'none';
        window.onclick = (e) => {
            if (e.target == addModal) addModal.style.display = 'none';
            if (e.target == editModal) editModal.style.display = 'none';
        }
    </script>
</body>

</html>