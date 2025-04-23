<?php
session_start();
require_once('../connection.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: users/login.php");
    exit;
}

// Lấy user_id từ session
$username = $_SESSION['username'];
$userQuery = "SELECT id FROM users WHERE username = '$username'";
$userResult = $ocon->query($userQuery);
$user = $userResult->fetch_assoc();
$user_id = $user['id'];

// Truy vấn danh sách ảnh
$sql = "
    SELECT 
        images.id AS image_id,
        images.file_name,
        ROUND(images.file_size / 1024, 2) AS file_size_kb,
        images.file_path
    FROM Images
    WHERE images.user_id = $user_id
    ORDER BY images.upload_time DESC
";
$result = $ocon->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Ảnh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3 class="text-center">Danh sách Ảnh</h3>
        <table class="table table-bordered mt-4">
            <thead class="table-dark">
                <tr>
                    <th>STT</th>
                    <th>Tên ảnh</th>
                    <th>Kích thước file</th>
                    <th>Ảnh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $stt = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo htmlspecialchars($row['file_name']); ?></td>
                            <td><?php echo $row['file_size_kb'] . ' KB'; ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($row['file_path']); ?>" 
                                     alt="Thumbnail" 
                                     style="width: 100px; height: auto;">
                            </td>
                            <td>
                                <!-- Nút Sửa -->
                                <a href="editimage.php?id=<?php echo $row['image_id']; ?>" class="btn btn-info btn-sm text-white">Sửa</a>
                                <!-- Nút Xóa -->
                                <a href="deleteimage.php?id=<?php echo $row['image_id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa ảnh này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Không có dữ liệu</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center mt-4">
            <a href="upload.php" class="btn btn-secondary">Quay lại trang Upload</a>
            <a href="../index.php" class="btn btn-primary">Về Trang Chính</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>