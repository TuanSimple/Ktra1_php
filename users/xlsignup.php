<?php
require_once('../connection.php');

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Mã hóa mật khẩu
$fullname = $_POST['fullname'];

// Kiểm tra username đã tồn tại
$checkSql = "SELECT * FROM users WHERE username = '$username'";
$result = $ocon->query($checkSql);

if ($result->num_rows > 0) {
    echo "<script>
        alert('Tên đăng nhập đã tồn tại!');
        window.location.href = 'signup.php';
    </script>";
} else {
    // Thực hiện thêm mới nếu username chưa tồn tại
    $sql = "INSERT INTO users (username, password, fullname) VALUES ('$username', '$password', '$fullname')";
    if ($ocon->query($sql) === TRUE) {
        echo "<script>
            alert('Đăng ký thành công!');
            window.location.href = 'login.php';
        </script>";
    } else {
        echo "<script>
            alert('Đăng ký thất bại!');
            window.location.href = 'signup.php';
        </script>";
    }
}
?>