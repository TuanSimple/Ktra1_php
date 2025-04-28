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

        // Lấy thông tin ảnh cũ từ cơ sở dữ liệu
        $stmt = $ocon->prepare("SELECT file_path FROM Images WHERE id = ?");
        $stmt->bind_param("i", $imageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();

        if (!$image) {
            echo "Ảnh không tồn tại.";
            exit;
        }

        $oldFilePath = $image['file_path']; // Đường dẫn ảnh cũ

        // Ghi đè ảnh cũ
        if (file_put_contents($oldFilePath, $decodedData)) {
            $fileSize = filesize($oldFilePath);
            $fileType = 'image/png';

            // Cập nhật thông tin ảnh trong cơ sở dữ liệu
            $stmt = $ocon->prepare("UPDATE Images SET file_type=?, file_size=? WHERE id=?");
            $stmt->bind_param("sii", $fileType, $fileSize, $imageId);
            if ($stmt->execute()) {
                // Tạo lại ảnh thumbnail
                $thumbPath = str_replace('images/compressed/', 'images/thumbs/', $oldFilePath);
                createThumbnail($oldFilePath, $thumbPath);

                header("Location: list_images.php?msg=updated");
                exit;
            } else {
                echo "Lỗi cập nhật CSDL.";
            }
        } else {
            echo "Không thể ghi đè ảnh.";
        }
    } else {
        echo "Ảnh không hợp lệ.";
    }
} else {
    echo "Dữ liệu không hợp lệ.";
}

// Hàm tạo thumbnail
function createThumbnail($sourcePath, $thumbPath, $thumbWidth = 200) {
    list($width, $height, $type) = getimagesize($sourcePath);
    $ratio = $width / $height;

    $thumbHeight = $thumbWidth / $ratio;

    $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        default:
            echo "Định dạng ảnh không được hỗ trợ.";
            return false;
    }

    // Resize ảnh
    imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

    // Lưu ảnh thumbnail
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbImage, $thumbPath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbImage, $thumbPath);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbImage, $thumbPath);
            break;
    }

    // Giải phóng bộ nhớ
    imagedestroy($thumbImage);
    imagedestroy($sourceImage);

    return true;
}
?>