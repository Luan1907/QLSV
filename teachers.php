<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];

// Lấy danh sách sinh viên
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'teacher'");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);


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


        <?php if (isset($error))
            echo "<p style='color:red;'>$error</p>"; ?>

        <table class="student-table">
            <tr>
                <th>Avatar</th>
                <th>Tên đăng nhập</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
               
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td>
                        <?php if ($student['avatar']): ?>
                            <img src="uploads/avatars/<?= htmlspecialchars($student['avatar']) ?>" alt="avatar" width="40"
                                height="40" >
                        <?php else: ?>
                            <img src="default-avatar.jpg" alt="avatar" width="40">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="profile_view.php?id=<?= $student['id'] ?>">
                            <?= htmlspecialchars($student['username']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td><?= htmlspecialchars($student['phone']) ?></td>
                   
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>

</html>