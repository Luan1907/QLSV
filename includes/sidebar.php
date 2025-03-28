<body>
    <div class="sidebar">
    
        <h3><?= htmlspecialchars($user['full_name']) ?></h3>
        <p>Vai trò: <?= $user['role'] == 'teacher' ? 'Giáo viên' : 'Sinh viên' ?></p>
        <a href="profile.php">Trang chủ</a>
        <?php if ($user['role'] == 'teacher'): ?>
            <a href="upload_assignment.php">Giao bài tập</a>
        <?php else: ?>
            <a href="assignments/list.php">Xem bài tập</a>
            <a href="users/edit.php">Chỉnh sửa thông tin</a>
        <?php endif; ?>
        <a href="students.php">Danh sách sinh viên</a>
        <a href="teachers.php">Danh sách giảng viên</a>
        <a href="logout.php">Đăng xuất</a>
    </div>