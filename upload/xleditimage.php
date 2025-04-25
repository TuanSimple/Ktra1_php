<?php
session_start();
require_once '../connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cropped_image']) && isset($_POST['id'])) {
    $imageData = $_POST['cropped_image'];
    $imageId = intval($_POST['id']);

    if (strpos($imageData, 'data:image/png;base64,') === 0) {
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $decodedData = base64_decode($imageData);

        // Đặt tên file mới
        $newFileName = 'cropped_' . time() . '.png';
        $uploadDir = '../Uploads/';
        $filePath = $uploadDir . $newFileName;

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Ghi file
        if (file_put_contents($filePath, $decodedData)) {
            $fileSize = filesize($filePath);
            $fileType = 'image/png';

            // Cập nhật vào CSDL
            $stmt = $ocon->prepare("UPDATE Images SET file_name=?, file_type=?, file_size=?, file_path=? WHERE id=?");
            $stmt->bind_param("ssisi", $newFileName, $fileType, $fileSize, $filePath, $imageId);
            if ($stmt->execute()) {
                header("Location: list_images.php?msg=updated");
                exit;
            } else {
                echo "Lỗi cập nhật CSDL.";
            }
        } else {
            echo "Không thể lưu ảnh.";
        }
    } else {
        echo "Ảnh không hợp lệ.";
    }
} else {
    echo "Dữ liệu không hợp lệ.";
}
?>