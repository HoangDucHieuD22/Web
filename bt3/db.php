<?php
// Cấu hình kết nối database
$servername = "localhost";    // thường là localhost nếu dùng XAMPP hoặc local server
$username = "root";           // user MySQL mặc định
$password = "";               // mật khẩu MySQL, mặc định XAMPP thường để trống
$dbname = "doanvien_db";     // tên database bạn đã tạo

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối database thất bại: " . $conn->connect_error);
}

// Thiết lập charset để hỗ trợ tiếng Việt
$conn->set_charset("utf8");
?>
