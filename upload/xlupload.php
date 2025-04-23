<?php
session_start();
require_once('../connection.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: ../users/login.php");
    exit;
}

if (isset($_FILES['fupload']) && !empty($_FILES['fupload']['name'][0])) {
    // Mảng lưu các lỗi
    $errors = array();

    // Mảng lưu các file được phép upload
    $expensions = array("jpeg", "jpg", "png");

    // Lấy user_id từ session
    $username = $_SESSION['username'];
    $userQuery = "SELECT id FROM users WHERE username = '$username'";
    $userResult = $ocon->query($userQuery);
    $user = $userResult->fetch_assoc();
    $user_id = $user['id'];

    foreach ($_FILES['fupload']['name'] as $key => $file_name) {
        $file_size = $_FILES['fupload']['size'][$key];
        $file_tmp = $_FILES['fupload']['tmp_name'][$key];
        $file_type = $_FILES['fupload']['type'][$key];
        $arr_name = explode('.', $file_name);
        $file_ext = strtolower(end($arr_name));

        // Kiểm tra định dạng file
        if (in_array($file_ext, $expensions) === false) {
            $errors[] = "File <strong>$file_name</strong>: Không chấp nhận định dạng ảnh có đuôi này, mời bạn chọn JPEG hoặc PNG.";
            $logSql = "INSERT INTO imageuploadlogs (image_id, user_id, status, message) 
                       VALUES (NULL, $user_id, 'fail', 'Định dạng file không hợp lệ: $file_name')";
            $ocon->query($logSql);
            continue;
        }

        // Kiểm tra kích thước file
        if ($file_size > 2097152) {
            $errors[] = "File <strong>$file_name</strong>: Kích cỡ file nên là 2 MB.";
            $logSql = "INSERT INTO imageuploadlogs (image_id, user_id, status, message) 
                       VALUES (NULL, $user_id, 'fail', 'Kích thước file quá lớn: $file_name')";
            $ocon->query($logSql);
            continue;
        }

        // Kiểm tra trùng tên file trong cơ sở dữ liệu
        $checkSql = "SELECT * FROM images WHERE file_name = '$file_name'";
        $checkResult = $ocon->query($checkSql);
        if ($checkResult->num_rows > 0) {
            $errors[] = "File <strong>$file_name</strong>: File đã tồn tại.";
            $logSql = "INSERT INTO imageuploadlogs (image_id, user_id, status, message) 
                       VALUES (NULL, $user_id, 'fail', 'File đã tồn tại: $file_name')";
            $ocon->query($logSql);
            continue;
        }

        // Nếu không có lỗi, tiến hành upload
        if (empty($errors)) {
            if (!is_dir("../images")) {
                mkdir("../images", 0777, true); // Tạo thư mục nếu chưa tồn tại
            }
            $file_path = "../images/" . $file_name;
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Lưu thông tin file vào bảng Images
                $sql = "INSERT INTO images (file_name, file_type, file_size, file_path, user_id) 
                        VALUES ('$file_name', '$file_type', $file_size, '$file_path', $user_id)";
                if ($ocon->query($sql) === TRUE) {
                    $image_id = $ocon->insert_id;
                    $logSql = "INSERT INTO imageuploadlogs (image_id, user_id, status, message) 
                               VALUES ($image_id, $user_id, 'success', 'Upload thành công')";
                    $ocon->query($logSql);
                } else {
                    $errors[] = "File <strong>$file_name</strong>: Upload thành công nhưng không thể lưu vào cơ sở dữ liệu.";
                    $logSql = "INSERT INTO imageuploadlogs (image_id, user_id, status, message) 
                               VALUES (NULL, $user_id, 'fail', 'Không thể lưu vào cơ sở dữ liệu: $file_name')";
                    $ocon->query($logSql);
                }
            } else {
                $errors[] = "File <strong>$file_name</strong>: Không thể upload file.";
                $logSql = "INSERT INTO imageuploadlogs (image_id, user_id, status, message) 
                           VALUES (NULL, $user_id, 'fail', 'Không thể upload file: $file_name')";
                $ocon->query($logSql);
            }
        }
    }

    // Lưu lỗi vào session để hiển thị trên trang history
    if (!empty($errors)) {
        $_SESSION['upload_errors'] = $errors;
    }

    // Chuyển hướng sang trang history.php
    header("Location: history.php");
    exit;
} else {
    // Trường hợp không chọn file
    $logSql = "INSERT INTO imageuploadlogs (image_id, user_id, status, message) 
               VALUES (NULL, $user_id, 'fail', 'Không có file nào được chọn để upload')";
    $ocon->query($logSql);

    $_SESSION['upload_errors'] = ["Không có file nào được chọn để upload."];
    header("Location: history.php");
    exit;
}
?>