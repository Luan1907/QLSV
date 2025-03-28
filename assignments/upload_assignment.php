<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $teacher_id = $_SESSION['user']['id'];

    $file_name = "";
    if (!empty($_FILES['file']['name'])) {
        $file_name = time() . '_' . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], "../uploads/assignments/" . $file_name);
    }

    $stmt = $pdo->prepare("INSERT INTO assignments (teacher_id, title, description, file) VALUES (?, ?, ?, ?)");
    $stmt->execute([$teacher_id, $title, $description, $file_name]);

    header('Location: ../students.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giao bài tập</title>
</head>
<body>
    <h2>Giao bài tập</h2>
    <form action="upload_assignment.php" method="post" enctype="multipart/form-data">
        <label>Tiêu đề:</label><br>
        <input type="text" name="title" required><br>
        <label>Mô tả:</label><br>
        <textarea name="description"></textarea><br>
        <label>Tệp bài tập:</label><br>
        <input type="file" name="file" accept=".pdf,.docx,.zip"><br>
        <button type="submit">Tải lên</button>
    </form>
</body>
</html>
