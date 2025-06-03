<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
if ($user['role'] !== 'student') {
    echo "Chức năng này chỉ dành cho sinh viên.";
    exit();
}

// Xử lý nộp bài
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assignment_id'])) {
    $assignment_id = $_POST['assignment_id'];
    $student_id = $user['id'];
    $file_name = "";

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $allowed_ext = ['pdf', 'doc', 'docx', 'zip', 'rar'];
        $max_size = 5 * 1024 * 1024; // 5MB

        $original_name = $_FILES['file']['name'];
        $file_size = $_FILES['file']['size'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            echo "<p style='color:red;'>Chỉ cho phép các định dạng: pdf, doc, docx, zip, rar.</p>";
            exit();
        }

        if ($file_size > $max_size) {
            echo "<p style='color:red;'>Dung lượng file vượt quá 5MB.</p>";
            exit();
        }

        // Đảm bảo thư mục tồn tại
        if (!is_dir("uploads/submissions")) {
            mkdir("uploads/submissions", 0777, true);
        }

        $file_name = time() . "_" . basename($original_name);
        $target_path = "uploads/submissions/" . $file_name;

        if (!move_uploaded_file($file_tmp, $target_path)) {
            echo "<p style='color:red;'>Lỗi upload file.</p>";
            exit();
        }

        // Nếu đã nộp thì cập nhật, ngược lại thì thêm mới
        $check = $pdo->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
        $check->execute([$assignment_id, $student_id]);

        if ($check->rowCount() > 0) {
            $update = $pdo->prepare("UPDATE submissions SET file = ?, submitted_at = NOW() WHERE assignment_id = ? AND student_id = ?");
            $update->execute([$file_name, $assignment_id, $student_id]);
        } else {
            $insert = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, file, submitted_at) VALUES (?, ?, ?, NOW())");
            $insert->execute([$assignment_id, $student_id, $file_name]);
        }
    } else {
        echo "<p style='color:red;'>Vui lòng chọn file để nộp.</p>";
    }
}

// Lấy danh sách bài tập
$stmt = $pdo->query("
    SELECT a.*, u.full_name AS teacher_name
    FROM assignments a
    JOIN users u ON a.teacher_id = u.id
    ORDER BY a.created_at DESC
");
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách bài đã nộp
$submitted = $pdo->prepare("SELECT assignment_id, file, submitted_at FROM submissions WHERE student_id = ?");
$submitted->execute([$user['id']]);
$submitted_map = [];
foreach ($submitted->fetchAll(PDO::FETCH_ASSOC) as $s) {
    $submitted_map[$s['assignment_id']] = $s;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Nộp bài tập</title>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <h2>Danh sách bài tập</h2>
        <table class="student-table" border="1" cellpadding="10">
            <tr>
                <th>Tiêu đề</th>
                <th>Tập tin</th>
                <th>Người giao</th>
                <th>Trạng thái</th>
                <th>Nộp bài</th>
            </tr>
            <?php foreach ($assignments as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td>
                        <?php if ($row['file']): ?>
                            <a href="uploads/assignments/<?= htmlspecialchars($row['file']) ?>"
                                download><?= htmlspecialchars($row['file']) ?></a>
                        <?php else: ?>
                            Không có file
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                    <td>
                        <?php if (isset($submitted_map[$row['id']])): ?>
                            Đã nộp lúc <?= $submitted_map[$row['id']]['submitted_at'] ?><br>
                            <a href="uploads/submissions/<?= htmlspecialchars($submitted_map[$row['id']]['file']) ?>"
                                download>Tải bài đã nộp</a>
                        <?php else: ?>
                            Chưa nộp
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="assignment_id" value="<?= $row['id'] ?>">
                            <input type="file" name="file" required>
                            <input type="submit" value="Nộp bài">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>

</html>