<?php
session_start();
require_once '../connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID ảnh không hợp lệ.";
    exit;
}

$image_id = intval($_GET['id']);
$stmt = $ocon->prepare("SELECT * FROM Images WHERE id = ?");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$image = $stmt->get_result()->fetch_assoc();

if (!$image) {
    echo "Ảnh không tồn tại.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa ảnh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet">
    <style>
        #image {
            max-width: 100%;
            max-height: 500px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h3 class="text-center">Chỉnh sửa Ảnh</h3>
        <img id="image" src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Ảnh cần chỉnh sửa">

        <form method="POST" action="xleditimage.php">
            <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
            <input type="hidden" name="cropped_image" id="cropped_image">
            <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center">
                <button type="button" class="btn btn-warning" onclick="rotate()">Xoay 90°</button>
                <button type="button" class="btn btn-danger" onclick="flip()">Lật ngang</button>
                <button type="button" class="btn btn-success" onclick="cropImage()">Cắt ảnh</button>
                <button type="submit" class="btn btn-primary">Lưu ảnh</button>
                <button type="button" class="btn btn-secondary" onclick="resetImage()">Hủy</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script>
    const image = document.getElementById('image');
    const croppedInput = document.getElementById('cropped_image');
    const form = document.querySelector('form');
    let cropper = new Cropper(image, {
        aspectRatio: NaN,
        viewMode: 1
    });

    let flipX = 1;

    function rotate() {
        cropper.rotate(90);
    }

    function flip() {
        flipX = -flipX;
        cropper.scaleX(flipX);
    }

    function cropImage() {
        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300
        });

        if (canvas) {
            croppedInput.value = canvas.toDataURL('image/png');
            alert("Ảnh đã được cắt. Hãy nhấn 'Lưu ảnh' để lưu lại.");
        }
    }

    function resetImage() {
        cropper.reset();
        window.location.href = 'list_images.php'; // Quay lại trang danh sách
    }

    // Trước khi submit form (khi nhấn 'Lưu ảnh')
    form.onsubmit = function () {
        const canvas = cropper.getCroppedCanvas();
        if (canvas) {
            croppedInput.value = canvas.toDataURL('image/png');
        } else {
            alert("Không thể lấy ảnh từ cropper.");
            return false;
        }
    }
</script>

</body>

</html>