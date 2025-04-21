<!-- filepath: c:\xampp\htdocs\KiemTra2\history.php -->
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

// Truy vấn lịch sử upload
$sql = "
    SELECT 
        h.id AS log_id,
        COALESCE(images.file_name, 'Chưa có') AS file_name,
        COALESCE(images.file_type, 'Chưa có') AS file_type,
        COALESCE(ROUND(images.file_size / 1024, 2), 'Chưa có') AS file_size_kb,
        h.status,
        h.message,
        h.log_time
    FROM History h
    LEFT JOIN Images images ON h.image_id = images.id
    WHERE h.user_id = $user_id
    ORDER BY h.log_time DESC
";
$result = $ocon->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3 class="text-center">Lịch sử Upload</h3>
        <table class="table table-bordered mt-4">
            <thead class="table-dark">
                <tr>
                    <th>STT</th>
                    <th>Tên file ảnh</th>
                    <th>Loại file</th>
                    <th>Kích thước file</th>
                    <th>Trạng thái</th>
                    <th>Thông báo</th>
                    <th>Thời gian upload</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $stt = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo $row['file_name']; ?></td>
                            <td><?php echo $row['file_type']; ?></td>
                            <td><?php echo $row['file_size_kb'] . ' KB'?></td>
                            <td>
                                <?php if ($row['status'] === 'success'): ?>
                                    <span class="badge bg-success">Thành công</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Thất bại</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo $row['log_time']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Không có dữ liệu</td>
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