<?php
session_start();
session_unset();
session_destroy();

// Lấy URL để quay về sau khi đăng xuất
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'homepage.php';

// Bảo vệ: tránh redirect đến URL ngoài domain (open redirect)
if (strpos($redirect, '/') !== 0) {
    $redirect = 'homepage.php';
}

header("Location: $redirect");
exit;
?>
