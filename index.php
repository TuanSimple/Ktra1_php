<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: users/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xử lý ảnh</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: start;
            align-items: center;
            background-color: #f8f9fa;
        }

        .navbar {
            width: 50%;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        #mainContent {
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 50%;
        }

        .btn-custom {
            width: 120px;
            height: 40px;
            font-size: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #6c757d; /* Màu xám */
            color: white;
            border: none;
            border-radius: 5px;
        }

        .btn-custom i {
            margin-right: 5px;
        }

        .btn-custom:hover {
            background-color: #5a6268; /* Màu xám đậm hơn khi hover */
        }
        .btn-equal {
            width: 200px;
            text-align: center; 
        }
    </style>
</head>

<body>
    <ul class="nav navbar p-3 d-flex align-items-center">
        <li class="nav-item me-auto">
            <a class="nav-link fs-5 fw-bold text-secondary" href="index.php">
                <i class="bi bi-house-door-fill"></i>
            </a>
        </li>

        <div class="d-flex justify-content-center align-items-center flex-grow-1">
            <span class="fs-5 fw-bold text-secondary me-4">Xin chào, <?php echo $_SESSION['fullname']; ?></span>
            <li class="nav-item">
                <a class="nav-link fs-5 fw-bold text-secondary" href="users/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </li>
        </div>
    </ul>

    <div id="mainContent">
        <h3>Chào mừng bạn đến với hệ thống xử lý ảnh</h3>
        <p>Hãy chọn một chức năng để bắt đầu.</p>
        <hr>
        <div class="d-flex justify-content-center gap-3 mt-4 flex-wrap">
            <a href="upload/upload.php" class="nav-link fs-5 fw-bold text-secondary border border-secondary rounded p-2 btn-equal">
                <i class="bi bi-cloud-upload-fill"></i> Upload
            </a>
            <a href="list_images.php" class="nav-link fs-5 fw-bold text-secondary border border-secondary rounded p-2 btn-equal">
                <i class="bi bi-scissors"></i> Cắt ảnh
            </a>
            <a href="list_images.php" class="nav-link fs-5 fw-bold text-secondary border border-secondary rounded p-2 btn-equal">
                <i class="bi bi-arrow-repeat"></i> Xoay ảnh
            </a>
            <a href="list_images.php" class="nav-link fs-5 fw-bold text-secondary border border-secondary rounded p-2 btn-equal">
                <i class="bi bi-arrows-angle-expand"></i> Đổi kích thước
            </a>
            <a href="list_images.php" class="nav-link fs-5 fw-bold text-secondary border border-secondary rounded p-2 btn-equal">
                <i class="bi bi-card-image"></i> Danh sách ảnh
            </a>
            <a href="history.php" class="nav-link fs-5 fw-bold text-secondary border border-secondary rounded p-2 btn-equal">
                <i class="bi bi-clock-history"></i> Lịch sử upload
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>