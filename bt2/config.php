<?php
session_start();

// Đường dẫn đến file JSON lưu trữ thông tin người dùng
define('USERS_FILE', 'users.json');

// Hàm kiểm tra xem người dùng đã đăng nhập chưa
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm chuyển hướng nếu chưa đăng nhập
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Hàm chuyển hướng nếu đã đăng nhập
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: dashboard.php");
        exit;
    }
}

// Hàm đọc dữ liệu người dùng từ file JSON
function getUsers() {
    if (file_exists(USERS_FILE)) {
        $jsonData = file_get_contents(USERS_FILE);
        return json_decode($jsonData, true) ?: [];
    }
    return [];
}

// Hàm lưu dữ liệu người dùng vào file JSON
function saveUsers($users) {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

// Hàm tìm người dùng theo tên đăng nhập
function findUserByUsername($username) {
    $users = getUsers();
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    return null;
}

// Hàm tìm người dùng theo email
function findUserByEmail($email) {
    $users = getUsers();
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}
?>
