<?php
session_start();
include 'db.php'; // kết nối database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'homepage.php';

    // Ngăn redirect ra ngoài domain
    if (!preg_match('/^[a-zA-Z0-9_\-\/.]+\.php$/', $redirect)) {
        $redirect = 'homepage.php';
    }

    // Kiểm tra tài khoản trong bảng `taikhoan`
    $stmt = $conn->prepare("SELECT * FROM taikhoan WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Nếu tìm thấy tài khoản
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Thiết lập session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // 'admin' hoặc 'doanvien'
            $_SESSION['ma_sv'] = $user['ma_sv'] ?? null;

           

            header("Location: $redirect");
            exit;
        } else {
            $_SESSION['login_error'] = "Sai mật khẩu!";
        }
    } else {
        $_SESSION['login_error'] = "Tài khoản không tồn tại!";
    }

    // Quay lại trang gọi đến
    header("Location: $redirect");
    exit;
}
?>
