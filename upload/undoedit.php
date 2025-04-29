<?php
session_start();
require_once '../connection.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Lấy ID ảnh từ tham số URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID ảnh không hợp lệ.";
    exit;
}

$imageId = intval($_GET['id']);

// Lấy chi tiết ảnh từ cơ sở dữ liệu
$stmt = $ocon->prepare("SELECT file_path FROM Images WHERE id = ?");
$stmt->bind_param("i", $imageId);
$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();

if (!$image) {
    echo "Ảnh không tồn tại trong cơ sở dữ liệu.";
    exit;
}

$filePath = $image['file_path'];
$backupPath = str_replace('images/compressed/', 'images/backup/', $filePath);
$thumbPath = str_replace('images/compressed/', 'images/thumbs/', $filePath);

// Kiểm tra nếu ảnh backup tồn tại
if (!file_exists($backupPath)) {
    echo "Không tìm thấy ảnh gốc để hoàn tác.";
    exit;
}

// Thay thế ảnh trong compressed bằng ảnh backup
if (!copy($backupPath, $filePath)) {
    echo "Lỗi khi hoàn tác ảnh.";
    exit;
}

// Cập nhật lại ảnh trong thumbs
if (file_exists($thumbPath)) {
    unlink($thumbPath); // Xóa ảnh cũ trong thumbs
}
if (!createThumbnail($backupPath, $thumbPath)) {
    echo "Lỗi khi cập nhật ảnh thu nhỏ.";
    exit;
}

// Xóa ảnh trong backup
if (!unlink($backupPath)) {
    echo "Lỗi khi xóa ảnh backup.";
    exit;
}

// Chuyển hướng về trang danh sách ảnh
header("Location: list_images.php?message=undo_success");
exit;

// Hàm tạo ảnh thu nhỏ
function createThumbnail($sourcePath, $thumbPath, $thumbWidth = 150, $thumbHeight = 150) {
    $imageInfo = getimagesize($sourcePath);
    if ($imageInfo === false) {
        return false;
    }

    list($srcWidth, $srcHeight) = $imageInfo;
    $srcType = $imageInfo[2];

    switch ($srcType) {
        case IMAGETYPE_JPEG:
            $srcImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $srcImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $srcImage = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
    imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);

    switch ($srcType) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbImage, $thumbPath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbImage, $thumbPath, 6);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbImage, $thumbPath);
            break;
    }

    imagedestroy($srcImage);
    imagedestroy($thumbImage);

    return true;
}
?>