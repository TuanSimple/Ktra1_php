<?php
// Lấy ID ảnh từ tham số URL
$maAnh = $_GET["id"];

// Kết nối cơ sở dữ liệu
require_once("../connection.php");

// Truy vấn để lấy đường dẫn file ảnh
$sql_select = "SELECT file_path FROM images WHERE id = $maAnh";
$result = $ocon->query($sql_select);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $filePath = $row['file_path'];

    // Xóa file gốc nếu tồn tại
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Xóa thumbnail nếu tồn tại
    $thumbPath = str_replace('uploads/', 'uploads/thumbs/', $filePath);
    if (file_exists($thumbPath)) {
        unlink($thumbPath);
    }

    // Xóa bản ghi trong CSDL
    $sql_del = "DELETE FROM images WHERE id = $maAnh";
    if ($ocon->query($sql_del) === TRUE) {
        header("Location: list_images.php"); // điều hướng về danh sách
        exit;
    } else {
        echo "Lỗi khi xóa dữ liệu: " . $ocon->error;
    }
} else {
    echo "Ảnh không tồn tại!";
}

// Đóng kết nối
$ocon->close();
?>