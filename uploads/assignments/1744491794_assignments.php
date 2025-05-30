<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
if ($user['role'] !== 'teacher') {
    echo "Bạn không có quyền truy cập chức năng này.";
    exit();
}

// Xử lý form gửi bài tập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $teacher_id = $user['id'];
    $file_name = "";

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = time() . "_" . basename($_FILES["file"]["name"]);
        $target_path = "uploads/assignments/" . $file_name;
        move_uploaded_file($_FILES["file"]["tmp_name"], $target_path);
    }

    $stmt = $pdo->prepare("INSERT INTO assignments (teacher_id, title, description, file) VALUES (?, ?, ?, ?)");
    $stmt->execute([$teacher_id, $title, $description, $file_name]);
}

// Lấy danh sách bài tập
$stmt = $pdo->query("SELECT * FROM assignments ORDER BY created_at DESC");
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Danh sách bài tập đã giao</h2>
<table border="1" cellpadding="10">
    <tr><th>Tiêu đề</th><th>Mô tả</th><th>Tập tin</th><th>Ngày giao</th></tr>
    <?php foreach ($assignments as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
            <td>
                <?php if ($row['file']): ?>
                    <a href="uploads/assignments/<?= $row['file'] ?>" download><?= $row['file'] ?></a>
                <?php else: ?>
                    Không có file
                <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Giao bài tập mới</h2>
<form method="post" enctype="multipart/form-data">
    <label>Tiêu đề:</label><br>
    <input type="text" name="title" required><br><br>
    <label>Mô tả:</label><br>
    <textarea name="description"></textarea><br><br>
    <label>Chọn file bài tập:</label><br>
    <input type="file" name="file"><br><br>
    <input type="submit" value="Giao bài">
</form>
