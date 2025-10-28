<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: homepage.php");
    exit();
}

$quyen = $_SESSION['role'];
$ma_dang_nhap = $_SESSION['username'];
$ma_sv = $_SESSION['username'];

if (isset($_POST['them_hoat_dong']) && ($quyen == 'admin' || $quyen == 'canbo')) {
    $ten = $_POST['ten_hoat_dong'];
    $ngay = $_POST['ngay'];
    $dia_diem = $_POST['dia_diem'];
    $mo_ta = $_POST['mo_ta'];

    $stmt = $conn->prepare("INSERT INTO sinh_hoat (ten_hoat_dong, ngay, dia_diem, mo_ta) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $ten, $ngay, $dia_diem, $mo_ta);
    $stmt->execute();

    // Chuyển hướng lại để tránh gửi lại form khi reload
    header("Location: sinhhoat.php");
    exit();
}
if (isset($_POST['sua_hoat_dong']) && ($quyen == 'admin' || $quyen == 'canbo')) {
    $id = $_POST['id'];
    $ten = $_POST['ten_hoat_dong'];
    $ngay = $_POST['ngay'];
    $dia_diem = $_POST['dia_diem'];
    $mo_ta = $_POST['mo_ta'];

    $stmt = $conn->prepare("UPDATE sinh_hoat SET ten_hoat_dong = ?, ngay = ?, dia_diem = ?, mo_ta = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $ten, $ngay, $dia_diem, $mo_ta, $id);
    $stmt->execute();

    header("Location: sinhhoat.php");
    exit();
}



// Xử lý các hành động: xóa hoặc đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $id = intval($_POST['id']);
    

    if ($action == "delete" && ($quyen == "admin" || $quyen == "canbo")) {
        // Xóa tất cả bản ghi trong tham_gia_sinh_hoat liên quan trước
$stmt = $conn->prepare("DELETE FROM tham_gia_sinh_hoat WHERE sinh_hoat_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Sau đó xóa hoạt động chính
$stmt = $conn->prepare("DELETE FROM sinh_hoat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
    }

    if ($action == "dangky") {
        $sinh_hoat_id = $id; // gán ID hoạt động
        $stmt = $conn->prepare("INSERT IGNORE INTO tham_gia_sinh_hoat (ma_sv, sinh_hoat_id) VALUES (?, ?)");
        $stmt->bind_param("si", $ma_sv, $sinh_hoat_id);
        $stmt->execute();
    }
    
}

// Lấy danh sách sinh hoạt Đoàn
$sql = "SELECT * FROM sinh_hoat ORDER BY ngay DESC";
$result = $conn->query($sql);
$dsHoatDong = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dsHoatDong[] = $row;
    }
}
$dsDangKy = [];
$sql_dk = "SELECT t.sinh_hoat_id, d.ho_ten, d.ma_sv 
           FROM tham_gia_sinh_hoat t 
           JOIN doanvien d ON t.ma_sv = d.ma_sv";
$result_dk = $conn->query($sql_dk);
if ($result_dk && $result_dk->num_rows > 0) {
    while ($row = $result_dk->fetch_assoc()) {
        $dsDangKy[$row['sinh_hoat_id']][] = $row;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Lý Đoàn Viên - Đoàn Thanh niên cộng sản Hồ Chí Minh - PTIT</title>
    <!-- Bootstrap 3 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-blue: #1a75ff; /* Màu xanh blue chính */
            --secondary-blue: #4d94ff; /* Màu xanh blue nhạt hơn */
            --dark-blue: #0052cc; /* Màu xanh blue đậm */
            --light-blue: #e6f0ff; /* Màu xanh blue rất nhạt */
            --accent-red: #e30613; /* Màu đỏ từ logo PTIT */
            --accent-yellow: #ffcc00; /* Màu vàng từ ngôi sao */
            --accent-green: #1e7e34; /* Màu xanh lá từ logo Đoàn */
        }

        body {
            font-family: 'Roboto', sans-serif;
            padding-top: 70px;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Navbar styling */
        .navbar-default {
            background-color: var(--primary-blue);
            border-color: var(--dark-blue);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-default .navbar-brand,
        .navbar-default .navbar-nav > li > a {
            color: white;
            transition: all 0.3s ease;
        }

        .navbar-default .navbar-nav > .active > a,
        .navbar-default .navbar-nav > .active > a:focus,
        .navbar-default .navbar-nav > .active > a:hover,
        .navbar-default .navbar-nav > li > a:hover {
            background-color: var(--dark-blue);
            color: white;
        }

        .navbar-default .navbar-toggle {
            border-color: white;
        }

        .navbar-default .navbar-toggle .icon-bar {
            background-color: white;
        }

        .navbar-default .navbar-toggle:focus,
        .navbar-default .navbar-toggle:hover {
            background-color: var(--dark-blue);
        }

        .dropdown-menu {
            background-color: var(--secondary-blue);
            border: none;
            border-radius: 0;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
            animation: fadeIn 0.3s ease;
        }

        .dropdown-menu > li > a {
            color: white;
            padding: 10px 20px;
            transition: all 0.2s ease;
        }

        .dropdown-menu > li > a:hover {
            background-color: var(--dark-blue);
            color: white;
            transform: translateX(5px);
        }

        /* Logo styling */
        .navbar-brand {
            padding: 5px 15px;
        }

        .navbar-brand img {
            height: 40px;
            display: inline-block;
            margin-right: 10px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.05);
        }

        /* Button styling */
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--dark-blue);
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--dark-blue);
            border-color: var(--dark-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Panel styling */
        .panel-default {
            border-color: var(--primary-blue);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .panel-default:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }

        .panel-default > .panel-heading {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        /* News item styling */
        .news-item {
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            transition: all 0.3s ease;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
        }

        .news-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .news-item h3 {
            color: var(--primary-blue);
            font-weight: 600;
        }

        .news-item img {
            width: 100%;
            height: auto;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .news-item:hover img {
            transform: scale(1.02);
        }

        /* Section headers */
        h2 {
            color: var(--primary-blue);
            border-bottom: 2px solid var(--accent-yellow);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        /* Profile section */
        .profile-section {
            background-color: var(--light-blue);
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary-blue);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Login dropdown */
        .login-dropdown {
            width: 300px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
        }

         /* Footer styling */
         .footer {
            background-color: var(--dark-blue);
            color: white;
            padding: 40px 0;
            margin-top: 50px;
            box-shadow: 0 -5px 10px rgba(0, 0, 0, 0.1);
        }

        .footer h4 {
            color: var(--accent-yellow);
            border-bottom: 1px solid var(--accent-yellow);
            padding-bottom: 10px;
            font-weight: 600;
        }

        .footer a {
            color: #fff;
            transition: all 0.3s ease;
        }

        .footer a:hover {
            color: var(--accent-yellow);
            text-decoration: none;
        }


        /* List group styling */
        .list-group-item {
            border-left: 3px solid var(--secondary-blue);
            transition: all 0.3s ease;
            margin-bottom: 5px;
            border-radius: 4px !important;
        }

        .list-group-item:hover {
            background-color: var(--light-blue);
            transform: translateX(5px);
        }

        /* Carousel styling - làm rộng hơn */
.carousel {
    margin-left: -15px;
    margin-right: -15px;
    width: calc(100% + 20px);
}

.carousel-inner > .item > img {
    width: 100%;
    height: auto;
    max-height: 400px; /* Điều chỉnh chiều cao tối đa nếu cần */
    object-fit: cover;
}

.carousel-caption {
    background-color: rgba(0, 0, 0, 0.6);
    padding: 20px;
    border-radius: 8px;
    max-width: 80%;
    margin: 0 auto;
    bottom: 30px;
}


        /* Animation classes */
        .fade-in {
            animation: fadeIn 1s ease;
        }

        .slide-up {
            animation: slideUp 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive adjustments */
        @media (max-width: 767px) {
            .navbar-default .navbar-nav .open .dropdown-menu > li > a {
                color: white;
            }
            .navbar-default .navbar-nav .open .dropdown-menu > li > a:hover {
                background-color: var(--dark-blue);
            }
            .carousel-caption {
                padding: 10px;
                max-width: 90%;
            }
            .news-item {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header/Navbar -->
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="homepage.php">
                <img src="https://vn-test-11.slatic.net/p/0faa4de381517b7a7e98acd87d98a43c.png" alt="PTIT Logo">
                <img src="https://i.gyazo.com/ad27bc12ca81e862ceb35328122757ee.png" alt="Đoàn Thanh Niên Logo" style="height: 38px;">
            </a>
        </div>

        <div class="collapse navbar-collapse" id="navbar">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="homepage.php"><i class="fa fa-home"></i> Trang chủ</a></li>
                <li><a href="sinhhoat.php"><i class="fa fa-calendar"></i> Quản lý sinh hoạt Đoàn</a></li>
                <li><a href="quanlydoanvien.php"><i class="fa fa-users"></i> Quản lý đoàn viên</a></li>
               
                <?php if (!isset($_SESSION['username'])): ?>
                    <!-- Hiển thị khi chưa đăng nhập -->
                    <li class="dropdown" id="loginDropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-sign-in"></i> Đăng nhập <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu login-dropdown" style="padding: 15px; min-width: 300px;">
                            <form method="POST" action="login.php">
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                <div class="form-group">
                                    <label for="username">Tên đăng nhập</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Mật khẩu</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
                                <div class="text-center" style="margin-top: 10px;">
                                    <a href="register.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Chưa có tài khoản?</a>
                                </div>
                            </form>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Sau khi đăng nhập -->
                    <li class="dropdown" id="userDropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-user-circle"></i> 
                            <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> 
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="thongtincanhan.php"><i class="fa fa-id-card"></i> Thông tin cá nhân</a></li>
                            <li>
                                <form method="POST" action="logout.php" style="margin: 0; padding: 10px;">
                                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fa fa-sign-out"></i> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <h2 class="text-center">Danh sách sinh hoạt Đoàn</h2>

    <?php if ($quyen == 'admin' || $quyen == 'canbo'): ?>
        <div class="text-right add-btn">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addHoatDongModal">+ Thêm hoạt động</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($dsHoatDong as $hd): ?>
            <div class="col-sm-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <strong><?php echo htmlspecialchars($hd['ten_hoat_dong']); ?></strong>
                        <span class="pull-right"><?php echo date("d/m/Y", strtotime($hd['ngay'])); ?></span>
                    </div>
                    <div class="panel-body">
                        <p><strong>Địa điểm:</strong> <?php echo htmlspecialchars($hd['dia_diem']); ?></p>
                        <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($hd['mo_ta'])); ?></p>
                    </div>
                    <?php
        $is_dangky = false;
        foreach ($dsDangKy[$hd['id']] ?? [] as $sv) {
            if ($sv['ma_sv'] == $ma_sv) {
                $is_dangky = true;
                break;
            }
        }
      ?>
                    <div class="panel-footer text-right">
                    <?php if (!$is_dangky): ?>
    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="dangky" />
        <input type="hidden" name="id" value="<?php echo $hd['id']; ?>" />
        <button type="submit" class="btn btn-success btn-xs">Đăng ký</button>
    </form>
<?php else: ?>
    <span class="label label-success">Đã đăng ký</span>
    
<?php endif; ?>


                        <?php if ($quyen == 'admin' || $quyen == 'canbo'): ?>
                            <button class="btn btn-info btn-xs" data-toggle="modal" data-target="#dsDangKyModal<?php echo $hd['id']; ?>">DS tham gia</button>
                            <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editModal<?php echo $hd['id']; ?>">Sửa</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="id" value="<?php echo $hd['id']; ?>" />
                                <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Xóa hoạt động này?');">Xóa</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Modal Sửa Hoạt Động -->
<div class="modal fade" id="editModal<?php echo $hd['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $hd['id']; ?>">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="id" value="<?php echo $hd['id']; ?>">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="editModalLabel<?php echo $hd['id']; ?>">Sửa hoạt động</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
              <label>Tên hoạt động</label>
              <input type="text" name="ten_hoat_dong" class="form-control" value="<?php echo htmlspecialchars($hd['ten_hoat_dong']); ?>" required>
          </div>
          <div class="form-group">
              <label>Ngày tổ chức</label>
              <input type="date" name="ngay" class="form-control" value="<?php echo $hd['ngay']; ?>" required>
          </div>
          <div class="form-group">
              <label>Địa điểm</label>
              <input type="text" name="dia_diem" class="form-control" value="<?php echo htmlspecialchars($hd['dia_diem']); ?>" required>
          </div>
          <div class="form-group">
              <label>Mô tả</label>
              <textarea name="mo_ta" class="form-control"><?php echo htmlspecialchars($hd['mo_ta']); ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="sua_hoat_dong" class="btn btn-success">Lưu thay đổi</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
        </div>
      </form>
    </div>
  </div>
</div>
            <?php foreach ($dsHoatDong as $hd): ?>
    <!-- Modal Danh sách đăng ký -->
    <div class="modal fade" id="dsDangKyModal<?php echo $hd['id']; ?>" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Danh sách sinh viên đã đăng ký: <?php echo htmlspecialchars($hd['ten_hoat_dong']); ?></h4>
          </div>
          <div class="modal-body">
            <?php if (!empty($dsDangKy[$hd['id']])): ?>
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Mã SV</th>
                      <th>Họ tên</th>
                      <th>Trạng thái</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($dsDangKy[$hd['id']] as $sv): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($sv['ma_sv']); ?></td>
                        <td><?php echo htmlspecialchars($sv['ho_ten']); ?></td>
                        <td><span class="label label-success">Đã đăng ký</span></td>
                      </tr>
                      
                    <?php endforeach; ?>
                  </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">Chưa có sinh viên nào đăng ký hoạt động này.</div>
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
<?php endforeach; ?>
        <?php endforeach; ?>
        <?php if (empty($dsHoatDong)): ?>
            <div class="col-sm-12">
                <div class="alert alert-warning text-center">Chưa có hoạt động sinh hoạt nào.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Thêm Hoạt Động -->
<div class="modal fade" id="addHoatDongModal" tabindex="-1" role="dialog" aria-labelledby="addHoatDongModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="addHoatDongModalLabel">Thêm hoạt động sinh hoạt Đoàn</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Tên hoạt động</label>
                <input type="text" name="ten_hoat_dong" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Ngày tổ chức</label>
                <input type="date" name="ngay" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Địa điểm</label>
                <input type="text" name="dia_diem" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="mo_ta" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="them_hoat_dong" class="btn btn-primary">Thêm</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Footer -->
<footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4>Hệ Thống Quản Lý Đoàn Viên</h4>
                    <p>Đoàn Thanh niên cộng sản Hồ Chí Minh</p>
                    <p>Học viện Công Nghệ Bưu Chính Viễn Thông</p>
                </div>
                <div class="col-md-4">
                    <h4>Liên hệ</h4>
                    <p><i class="fa fa-map-marker"></i> 122 Hoàng Quốc Việt, Cầu Giấy, Hà Nội</p>
                    <p><i class="fa fa-phone"></i> (024) 3756 2963</p>
                    <p><i class="fa fa-envelope"></i> doanthanhnien@ptit.edu.vn</p>
                </div>
                <div class="col-md-4">
                    <h4>Các đường dẫn liên kết</h4>
                    <p>
                        <a href="https://ptit.edu.vn/sinh-vien/doan-thanh-nien/" target="_blank" class="btn btn-social">
                            <i class="fa fa-globe"></i> Trang chủ Đoàn Thanh niên
                        </a>
                    </p>
                    <p>
                        <a href="https://www.facebook.com/DoanThanhNienHVCNBCVT" target="_blank" class="btn btn-social">
                            <i class="fa fa-facebook"></i> Fanpage Facebook
                        </a>
                    </p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; 2025 Đoàn Thanh niên PTIT. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        <?php if ($is_admin): ?>
            // Nếu đã đăng nhập: ẩn login, hiện user dropdown
            document.getElementById("loginDropdown").style.display = "none";
            document.getElementById("userDropdown").style.display = "block";
            document.getElementById("userName").textContent = "Admin";
        <?php else: ?>
            // Nếu chưa đăng nhập: ẩn user dropdown
            document.getElementById("loginDropdown").style.display = "block";
            document.getElementById("userDropdown").style.display = "none";
        <?php endif; ?>
    });
</script>

    
    <?php if (isset($_SESSION['login_error'])): ?>
<script>alert("<?= $_SESSION['login_error'] ?>");</script>
<?php unset($_SESSION['login_error']); endif; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>
