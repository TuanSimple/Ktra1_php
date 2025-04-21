<?php
session_start();
require_once('../connection.php');


$username = $ocon->real_escape_string($_POST['username']);
$password = $_POST['password'];

// Truy vấn để lấy thông tin người dùng
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = $ocon->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Kiểm tra mật khẩu
    if (password_verify($password, $user['password'])) {
        // Đăng nhập thành công, lưu thông tin người dùng vào session
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        header("Location: ../index.php"); // Chuyển hướng về trang chính
        exit;
    } else {
        echo "Sai mật khẩu!";
    }
} else {
    echo "Tên đăng nhập không tồn tại!";
}
?>