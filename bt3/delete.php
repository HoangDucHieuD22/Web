<?php
session_start();
include 'db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: homepage.php');
    exit;
}

// Kiểm tra ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID không hợp lệ.";
    exit;
}

$id = intval($_GET['id']);

// Kiểm tra xem đoàn viên có tồn tại không
$result = $conn->query("SELECT * FROM doanvien WHERE stt = $id");
if ($result->num_rows === 0) {
    echo "Không tìm thấy đoàn viên với ID này.";
    exit;
}

// Thực hiện xóa
$sql = "DELETE FROM doanvien WHERE stt = $id";
if ($conn->query($sql) === TRUE) {
    header('Location: quanlydoanvien.php');
    exit;
} else {
    echo "Lỗi khi xóa: " . $conn->error;
}
?>
