<?php
session_start();
require_once('../connection.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: ../users/login.php");
    exit;
}

// Hàm nén và resize ảnh
function compressAndResizeImage($source, $destination, $maxWidth, $maxHeight, $quality) {
    list($width, $height, $imageType) = getimagesize($source);

    // Nếu ảnh nhỏ hơn maxWidth và maxHeight, sao chép ảnh gốc
    if ($width <= $maxWidth && $height <= $maxHeight) {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($source);
                imagejpeg($sourceImage, $destination, $quality);
                imagedestroy($sourceImage);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($source);
                imagepng($sourceImage, $destination, round($quality / 10));
                imagedestroy($sourceImage);
                break;
            default:
                return false; // Không hỗ trợ định dạng khác
        }
        return true;
    }

    // Tính toán kích thước mới
    $ratio = $width / $height;
    if ($maxWidth / $maxHeight > $ratio) {
        $newWidth = $maxHeight * $ratio;
        $newHeight = $maxHeight;
    } else {
        $newWidth = $maxWidth;
        $newHeight = $maxWidth / $ratio;
    }

    // Tạo ảnh mới với kích thước đã chỉnh
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Tạo ảnh từ file gốc dựa trên loại ảnh
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($source);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            break;
        default:
            return false; // Không hỗ trợ định dạng khác
    }

    // Resize ảnh
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Lưu ảnh đã nén
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $destination, $quality);
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $destination, round($quality / 10));
            break;
    }

    imagedestroy($newImage);
    imagedestroy($sourceImage);

    return true;
}

// Hàm tạo thumbnail
function createThumbnail($sourcePath, $thumbPath, $thumbWidth = 200) {
    $imageDetails = getimagesize($sourcePath);
    $width = $imageDetails[0];
    $height = $imageDetails[1];
    $mime = $imageDetails['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $img = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $img = imagecreatefrompng($sourcePath);
            imagealphablending($img, false);
            imagesavealpha($img, true);
            break;
        default:
            return false;
    }

    $thumbHeight = floor($height * ($thumbWidth / $width));
    $thumbImg = imagecreatetruecolor($thumbWidth, $thumbHeight);

    // Xử lý alpha cho PNG
    if ($mime === 'image/png') {
        imagealphablending($thumbImg, false);
        imagesavealpha($thumbImg, true);
    }

    imagecopyresampled($thumbImg, $img, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

    $success = false;
    switch ($mime) {
        case 'image/jpeg':
            $success = imagejpeg($thumbImg, $thumbPath, 80);
            break;
        case 'image/png':
            $success = imagepng($thumbImg, $thumbPath, 8); // 0 (chất lượng cao) -> 9 (chất lượng thấp)
            break;
    }

    imagedestroy($img);
    imagedestroy($thumbImg);

    return $success;
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
            $errors[] = "File <strong>$file_name</strong>: Không chấp nhận định dạng ảnh có đuôi này, mời bạn chọn JPEG, JPG hoặc PNG.";
            continue;
        }

        // Kiểm tra kích thước file
        if ($file_size > 2097152) {
            $errors[] = "File <strong>$file_name</strong>: Kích cỡ file nên là 2 MB.";
            continue;
        }

        // Nếu không có lỗi, tiến hành upload
        if (empty($errors)) {
            if (!is_dir("../images/compressed")) {
                mkdir("../images/compressed", 0777, true); // Tạo thư mục nếu chưa tồn tại
            }
            if (!is_dir("../images/thumbs")) {
                mkdir("../images/thumbs", 0777, true); // Tạo thư mục thumbnails nếu chưa tồn tại
            }

            $compressedPath = "../images/compressed/" . $file_name;
            $thumbPath = "../images/thumbs/" . $file_name;

            // Nén và resize ảnh trước khi lưu
            if (compressAndResizeImage($file_tmp, $compressedPath, 800, 800, 75)) {
                // Lấy dung lượng ảnh sau khi resize
                $resizedFileSize = filesize($compressedPath);

                // Tạo thumbnail
                createThumbnail($compressedPath, $thumbPath);

                // Lưu thông tin file vào bảng Images
                $sql = "INSERT INTO images (file_name, file_type, file_size, file_path, user_id) 
                        VALUES ('$file_name', '$file_type', $resizedFileSize, '$compressedPath', $user_id)";
                if ($ocon->query($sql)) {
                    // Lấy ID của ảnh vừa được thêm
                    $imageId = $ocon->insert_id;

                    // Ghi log vào bảng imageuploadlogs
                    $status = 'success';
                    $message = 'Upload thành công';
                    $logSql = "INSERT INTO imageuploadlogs (user_id, image_id, status, message, log_time) 
                               VALUES ($user_id, $imageId, '$status', '$message', NOW())";
                    $ocon->query($logSql);
                } else {
                    // Ghi log lỗi nếu không thể lưu ảnh
                    $status = 'error';
                    $message = 'Không thể lưu ảnh vào cơ sở dữ liệu';
                    $logSql = "INSERT INTO imageuploadlogs (user_id, image_id, status, message, log_time) 
                               VALUES ($user_id, NULL, '$status', '$message', NOW())";
                    $ocon->query($logSql);
                }
            } else {
                $errors[] = "File <strong>$file_name</strong>: Không thể nén và resize ảnh.";
            }
        }
    }

    // Chuyển hướng sang trang history.php
    header("Location: history.php");
    exit;
} else {
    // Trường hợp không chọn file
    $_SESSION['upload_errors'] = ["Không có file nào được chọn để upload."];
    header("Location: history.php");
    exit;
}
?>