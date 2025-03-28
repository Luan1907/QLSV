<?php
session_start();
require '../includes/db.php';

// Kiểm tra quyền giáo viên
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    header('Location: ../index.php');
    exit;
}

// Lấy ID sinh viên từ URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Kiểm tra sinh viên tồn tại và đúng role 'student'
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if ($student) {
        // Nếu có avatar thì xóa file avatar trên server
        if (!empty($student['avatar']) && file_exists('../uploads/avatars/' . $student['avatar'])) {
            unlink('../uploads/avatars/' . $student['avatar']);
        }

        // Xóa sinh viên khỏi CSDL
        $del_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $del_stmt->execute([$id]);

        // Bạn có thể thêm phần xóa các dữ liệu liên quan (tin nhắn, bài nộp...) nếu cần
    }
}

// Quay lại dashboard
header('Location: ../students.php');
exit;
?>