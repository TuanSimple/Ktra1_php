<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }

        .form-container {
            width: 400px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container h3 {
            margin-bottom: 20px;
        }

        .text-center p {
            margin-top: 15px;
        }

        .header-text {
            position: absolute;
            top: 20px;
            text-align: center;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="header-text">
        <h1>Hệ thống xử lý ảnh</h1>
        <p>Chào mừng bạn đến với hệ thống xử lý ảnh. Hãy đăng nhập để tiếp tục!</p>
    </div>
    <div class="form-container">
        <h3 class="text-center">Đăng nhập</h3>
        <form action="xllogin.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <p>Chưa có tài khoản? <a href="signup.php">Đăng ký ngay</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>