<?php
session_start();

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Xử lý đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $dob = trim($_POST['dob']);
    $student_id = trim($_POST['student_id']);
    $class = trim($_POST['class']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Kiểm tra lỗi
    $errors = [];
    
    // Kiểm tra mật khẩu
    if ($password !== $confirm_password) {
        $errors[] = "Mật khẩu xác nhận không khớp!";
    }
    
    // Kiểm tra username đã tồn tại chưa
    $users = [];
    if (file_exists('users.json')) {
        $users = json_decode(file_get_contents('users.json'), true);
    }
    
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $errors[] = "Tên đăng nhập đã tồn tại!";
            break;
        }
        
        if ($user['email'] === $email) {
            $errors[] = "Email đã được sử dụng!";
            break;
        }
    }
    
    // Nếu không có lỗi, lưu thông tin người dùng
    if (empty($errors)) {
        $new_user = [
            'id' => uniqid(),
            'fullname' => $fullname,
            'dob' => $dob,
            'student_id' => $student_id,
            'class' => $class,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $users[] = $new_user;
        file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
        
        // Chuyển hướng đến trang đăng nhập
        header("Location: login.php?registered=true");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 30px;
            padding-bottom: 30px;
        }
        .register-container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #337ab7;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn-primary {
            background-color: #337ab7;
        }
        .links {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <h2 class="text-center"><i class="glyphicon glyphicon-user"></i> Đăng ký</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fullname">Họ và Tên</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Nhập họ và tên" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dob">Ngày tháng năm sinh</label>
                            <input type="date" class="form-control" id="dob" name="dob" placeholder="mm/dd/yyyy">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="student_id">Mã Sinh viên</label>
                            <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Nhập mã sinh viên">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="class">Lớp</label>
                            <input type="text" class="form-control" id="class" name="class" placeholder="Nhập lớp">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ thường trú</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Nhập địa chỉ thường trú">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Nhập số điện thoại">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Đăng ký</button>
            </form>
            
            <div class="links">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
