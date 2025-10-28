<?php
session_start();
include 'db.php';

// Mặc định chưa đăng nhập
$is_logged_in = false;
$is_admin = false;
$is_doanvien = false;
$username = '';
$ma_sv = '';

// Nếu đã đăng nhập
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    $is_logged_in = true;
    $username = $_SESSION['username'];
    $ma_sv = $_SESSION['ma_sv'] ?? '';

    if ($_SESSION['role'] === 'admin') {
        $is_admin = true;
    } elseif ($_SESSION['role'] === 'doanvien') {
        $is_doanvien = true;
    }
}
// Lấy 3 đoàn viên tiêu biểu nhất (ưu tiên GPA cao -> ĐRL cao -> số hoạt động)
$top_sql = "SELECT dv.ma_sv, dv.ho_ten, dv.khoa, dv.nganh, dv.GPA, dv.ĐRL,
                   COUNT(tg.id) AS so_lan_tham_gia
            FROM doanvien dv
            LEFT JOIN tham_gia_sinh_hoat tg ON dv.ma_sv = tg.ma_sv AND tg.trang_thai = 'Tham gia'
            WHERE dv.ma_sv NOT IN (SELECT ma_sv FROM taikhoan WHERE role = 'admin')
            GROUP BY dv.ma_sv
            ORDER BY dv.GPA DESC, dv.ĐRL DESC, so_lan_tham_gia DESC
            LIMIT 3";
$top_result = $conn->query($top_sql);


?>

<!DOCTYPE html>
<html lang="vi">
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



    <!-- Main Content -->
    <div class="container">
        <!-- Carousel -->
<div id="mainCarousel" class="carousel slide" data-ride="carousel" style="margin-bottom: 30px;">
    <!-- Indicators -->
    <ol class="carousel-indicators">
        <li data-target="#mainCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#mainCarousel" data-slide-to="1"></li>
        <li data-target="#mainCarousel" data-slide-to="2"></li>
    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner" role="listbox">
        <div class="item active">
            <img src="https://pplx-res.cloudinary.com/image/upload/v1744205697/user_uploads/MrecHUdNAtbhgQc/image.jpg" alt="Tháng Thanh Niên 2025" style="width: 100%;">

        </div>
        <div class="item">
            <img src="https://pplx-res.cloudinary.com/image/upload/v1744205722/user_uploads/cpotwWdYPSqLeOI/image.jpg" alt="Đại hội Đại biểu" style="width: 100%;">
            
        </div>
        <div class="item">
            <img src="https://pplx-res.cloudinary.com/image/upload/v1744205886/user_uploads/iNVLlTrghTEaNgb/image.jpg" alt="Kỷ niệm 74 năm" style="width: 100%;">
            
        </div>
    </div>

    <!-- Controls -->
    <a class="left carousel-control" href="#mainCarousel" role="button" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="right carousel-control" href="#mainCarousel" role="button" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>
<?php if ($top_result && $top_result->num_rows > 0): ?>
<div class="container">
    <h3 class="text-center"><i class="fa fa-trophy"></i> Top 3 Đoàn viên tiêu biểu</h3>
    <div class="row text-center" style="margin-top: 30px;">
        <?php
        $top_members = [];
        while ($row = $top_result->fetch_assoc()) {
            $top_members[] = $row;
        }

        // Sắp xếp thứ tự hiển thị: Top 2 - Top 1 - Top 3
        $display_order = [$top_members[1] ?? null, $top_members[0] ?? null, $top_members[2] ?? null];
        $medals = ['🥈', '🥇', '🥉'];
        $panel_colors = ['#f0f0f0', '#fff8dc', '#fdf5e6'];
        ?>

        <?php foreach ($display_order as $index => $member): ?>
            <?php if (!$member) continue; ?>
            <div class="col-sm-4" style="<?= $index == 1 ? 'transform: scale(1.1);' : '' ?>">
                <div class="panel panel-default" style="box-shadow: 0 4px 10px rgba(0,0,0,0.2); background-color: <?= $panel_colors[$index] ?>;">
                    <div class="panel-heading text-center" style="font-size: 18px; font-weight: bold;">
                        <?= $medals[$index] ?> <?= htmlspecialchars($member['ho_ten']) ?>
                    </div>
                    <div class="panel-body text-left">
                        <p><strong>Mã SV:</strong> <?= htmlspecialchars($member['ma_sv']) ?></p>
                        <p><strong>Khoa:</strong> <?= htmlspecialchars($member['khoa']) ?></p>
                        <p><strong>Ngành:</strong> <?= htmlspecialchars($member['nganh']) ?></p>
                        <p><strong>GPA:</strong> <?= htmlspecialchars($member['GPA']) ?></p>
                        <p><strong>ĐRL:</strong> <?= htmlspecialchars($member['ĐRL']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>




        

        <!-- News Section -->
<div id="newsSection" class="fade-in">
    <h2><i class="fa fa-newspaper-o"></i> Tin tức nổi bật</h2>
    
    <!-- Tabs for News Categories -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#allNews" aria-controls="allNews" role="tab" data-toggle="tab">Tất cả tin tức</a></li>
        <li role="presentation"><a href="#doanhNews" aria-controls="doanhNews" role="tab" data-toggle="tab">Hoạt động Đoàn</a></li>
        <li role="presentation"><a href="#ptitNews" aria-controls="ptitNews" role="tab" data-toggle="tab">Tin PTIT</a></li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content">
        <!-- All News Tab -->
        <div role="tabpanel" class="tab-pane active" id="allNews">
            <div class="row">
                <div class="col-md-8">
                    <!-- Featured News -->
                    <div class="news-item">
                        <h3>Đoàn Thanh niên PTIT tổ chức Hội nghị học tập, quán triệt Nghị quyết</h3>
                        <p class="text-muted"><i class="fa fa-calendar"></i> 08/04/2025</p>
                        <img src="https://doanthanhnien.vn/Content/uploads/images/133853670605571223_z6365261675736_b023512ac815591fa7a0dfc1383fd52d.jpg" alt="Tin tức 1">
                        <p>Đoàn Thanh niên Học viện Công nghệ Bưu chính Viễn thông đã tổ chức thành công Hội nghị học tập, quán triệt Nghị quyết Đại hội đại biểu toàn quốc lần thứ XIII của Đảng. Hội nghị đã thu hút sự tham gia của đông đảo đoàn viên, thanh niên trong toàn Học viện.</p>
                        <a href="#" class="btn btn-primary">Đọc tiếp</a>
                    </div>
                    
                    <div class="news-item">
                        <h3>Chiến dịch tình nguyện Mùa hè xanh 2025</h3>
                        <p class="text-muted"><i class="fa fa-calendar"></i> 05/04/2025</p>
                        <img src="https://doanthanhnien.vn/Content/uploads/images/133686849284300942_%C4%90o%C3%A0n%20thanh%20ni%C3%AAn%20t%C3%ACnh%20nguy%E1%BB%87n%20Tr%C6%B0%E1%BB%9Dng%20%C4%90%E1%BA%A1i%20h%E1%BB%8Dc%20Kinh%20t%E1%BA%BF%20-%20%C4%90%E1%BA%A1i%20h%E1%BB%8Dc%20Qu%E1%BB%91c%20gia%20H%C3%A0%20N%E1%BB%99i%20trao%20t%E1%BA%B7ng%20%E2%80%9CS%C3%A2n%20ch%C6%A1i%20cho%20em%E2%80%9D%20t%E1%BA%A1i%20th%C3%B4n%20N%C3%A0%20Th%C6%B0a%20(x%C3%A3%20C%C3%B4n%20L%C3%B4n).jpg" alt="Tin tức 2">
                        <p>Đoàn Thanh niên PTIT chính thức phát động chiến dịch tình nguyện Mùa hè xanh 2025 với nhiều hoạt động ý nghĩa. Sinh viên sẽ có cơ hội tham gia các hoạt động tình nguyện tại các địa phương, góp phần xây dựng nông thôn mới và hỗ trợ cộng đồng.</p>
                        <a href="#" class="btn btn-primary">Đọc tiếp</a>
                    </div>
                    
                    <div class="news-item">
                        <h3>Đoàn Thanh niên PTIT đạt giải cao tại Hội thi Olympic các môn khoa học Mác-Lênin</h3>
                        <p class="text-muted"><i class="fa fa-calendar"></i> 01/04/2025</p>
                        <img src="https://doanthanhnien.vn/Content/uploads/images/133288612233655029_47dc4e4cf4342a6a7325.jpg" alt="Tin tức 3">
                        <p>Đoàn Thanh niên Học viện Công nghệ Bưu chính Viễn thông đã xuất sắc đạt giải Nhất toàn đoàn tại Hội thi Olympic các môn khoa học Mác-Lênin và tư tưởng Hồ Chí Minh cấp Bộ năm 2025. Đây là thành tích đáng tự hào, khẳng định sự nỗ lực không ngừng của đoàn viên, thanh niên PTIT trong học tập và nghiên cứu.</p>
                        <a href="#" class="btn btn-primary">Đọc tiếp</a>
                    </div>
                    
                    <div class="news-item">
                        <h3>Học viện Công nghệ Bưu chính Viễn thông và Công ty Cổ phần Rikkeisoft hợp tác trong đào tạo từ xa</h3>
                        <p class="text-muted"><i class="fa fa-calendar"></i> 03/02/2025</p>
                        <img src="https://ptit.edu.vn/wp-content/uploads/old/2023/08/20-1.jpg" alt="Tin tức 4">
                        <p>PTIT và Rikkeisoft ký kết hợp tác đào tạo từ xa ngành Công nghệ thông tin, mở ra cơ hội phát triển nguồn nhân lực chất lượng cao, đáp ứng nhu cầu của doanh nghiệp và xã hội.</p>
                        <a href="#" class="btn btn-primary">Đọc tiếp</a>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="disabled">
                                <a href="#" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <li class="active"><a href="#">1</a></li>
                            <li><a href="#">2</a></li>
                            <li><a href="#">3</a></li>
                            <li>
                                <a href="#" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                
                <div class="col-md-4">
                    <!-- Upcoming Events Panel -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-calendar"></i> Sự kiện sắp diễn ra</h3>
                        </div>
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <h4>Cuộc thi Ý tưởng sáng tạo khởi nghiệp 2025</h4>
                                    <p><i class="fa fa-clock-o"></i> 15/04/2025</p>
                                </li>
                                <li class="list-group-item">
                                    <h4>Hội thảo Kỹ năng mềm cho sinh viên</h4>
                                    <p><i class="fa fa-clock-o"></i> 20/04/2025</p>
                                </li>
                                <li class="list-group-item">
                                    <h4>Ngày hội việc làm PTIT 2025</h4>
                                    <p><i class="fa fa-clock-o"></i> 25/04/2025</p>
                                </li>
                                <li class="list-group-item">
                                    <h4>Lễ kỷ niệm 94 năm ngày thành lập Đoàn TNCS Hồ Chí Minh</h4>
                                    <p><i class="fa fa-clock-o"></i> 26/03/2025</p>
                                </li>
                                <li class="list-group-item">
                                    <h4>Hội nghị khoa học sinh viên năm 2025</h4>
                                    <p><i class="fa fa-clock-o"></i> 10/05/2025</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Featured News Panel -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-star"></i> Tin nổi bật</h3>
                        </div>
                        <div class="panel-body">
                            <div class="media">
                                <div class="media-left">
                                    <img class="media-object" src="" alt="Tin nổi bật 1">
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">Tháng Thanh Niên 2025</h4>
                                    <p>Kỷ niệm 94 năm thành lập Đoàn TNCS Hồ Chí Minh</p>
                                </div>
                            </div>
                            <hr>
                            <div class="media">
                                <div class="media-left">
                                    <img class="media-object" src="" alt="Tin nổi bật 2">
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">Đại hội Đại biểu Đoàn TNCS Hồ Chí Minh</h4>
                                    <p>Học viện PTIT lần thứ X, nhiệm kỳ 2024-2027</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links Panel -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-link"></i> Liên kết nhanh</h3>
                        </div>
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item"><a href="https://ptit.edu.vn" target="_blank"><i class="fa fa-angle-right"></i> Website Học viện PTIT</a></li>
                                <li class="list-group-item"><a href="https://portal.ptit.edu.vn" target="_blank"><i class="fa fa-angle-right"></i> Cổng thông tin sinh viên</a></li>
                                <li class="list-group-item"><a href="https://ptit.edu.vn/category/doan-thanh-nien" target="_blank"><i class="fa fa-angle-right"></i> Tin tức Đoàn Thanh niên</a></li>
                                <li class="list-group-item"><a href="https://ptit.edu.vn/tin-tuc-su-kien/tin-tuc/tin-tuc-chung" target="_blank"><i class="fa fa-angle-right"></i> Tin tức chung PTIT</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Doan News Tab -->
        <div role="tabpanel" class="tab-pane" id="doanhNews">
            <div class="row">
                <div class="col-md-12">
                    <div class="news-item">
                        <h3>Đoàn Thanh niên PTIT tổ chức Hội nghị học tập, quán triệt Nghị quyết</h3>
                        <p class="text-muted"><i class="fa fa-calendar"></i> 08/04/2025</p>
                        <img src="https://doanthanhnien.vn/Content/uploads/images/133853670605571223_z6365261675736_b023512ac815591fa7a0dfc1383fd52d.jpg" alt="Tin tức 1">
                        <p>Đoàn Thanh niên Học viện Công nghệ Bưu chính Viễn thông đã tổ chức thành công Hội nghị học tập, quán triệt Nghị quyết Đại hội đại biểu toàn quốc lần thứ XIII của Đảng. Hội nghị đã thu hút sự tham gia của đông đảo đoàn viên, thanh niên trong toàn Học viện.</p>
                        <a href="#" class="btn btn-primary">Đọc tiếp</a>
                    </div>
                    
                    <div class="news-item">
                        <h3>Chiến dịch tình nguyện Mùa hè xanh 2025</h3>
                        <p class="text-muted"><i class="fa fa-calendar"></i> 05/04/2025</p>
                        <img src="https://doanthanhnien.vn/Content/uploads/images/133686849284300942_%C4%90o%C3%A0n%20thanh%20ni%C3%AAn%20t%C3%ACnh%20nguy%E1%BB%87n%20Tr%C6%B0%E1%BB%9Dng%20%C4%90%E1%BA%A1i%20h%E1%BB%8Dc%20Kinh%20t%E1%BA%BF%20-%20%C4%90%E1%BA%A1i%20h%E1%BB%8Dc%20Qu%E1%BB%91c%20gia%20H%C3%A0%20N%E1%BB%99i%20trao%20t%E1%BA%B7ng%20%E2%80%9CS%C3%A2n%20ch%C6%A1i%20cho%20em%E2%80%9D%20t%E1%BA%A1i%20th%C3%B4n%20N%C3%A0%20Th%C6%B0a%20(x%C3%A3%20C%C3%B4n%20L%C3%B4n).jpg" alt="Tin tức 2">
                        <p>Đoàn Thanh niên PTIT chính thức phát động chiến dịch tình nguyện Mùa hè xanh 2025 với nhiều hoạt động ý nghĩa. Sinh viên sẽ có cơ hội tham gia các hoạt động tình nguyện tại các địa phương, góp phần xây dựng nông thôn mới và hỗ trợ cộng đồng.</p>
                        <a href="#" class="btn btn-primary">Đọc tiếp</a>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="disabled">
                                <a href="#" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <li class="active"><a href="#">1</a></li>
                            <li><a href="#">2</a></li>
                            <li>
                                <a href="#" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        
        <!-- PTIT News Tab -->
        <div role="tabpanel" class="tab-pane" id="ptitNews">
            <div class="row">
                <div class="col-md-12">
                    <div class="news-item">
                        <h3>Học viện Công nghệ Bưu chính Viễn thông và Công ty Cổ phần Rikkeisoft hợp tác trong đào tạo từ xa</h3>
                        <p class="text-muted"><i class="fa fa-calendar"></i> 03/02/2025</p>
                        <img src="https://ptit.edu.vn/wp-content/uploads/old/2023/08/20-1.jpg" alt="Tin tức 4">
                        <p>PTIT và Rikkeisoft ký kết hợp tác đào tạo từ xa ngành Công nghệ thông tin, mở ra cơ hội phát triển nguồn nhân lực chất lượng cao, đáp ứng nhu cầu của doanh nghiệp và xã hội.</p>
                        <a href="#" class="btn btn-primary">Đọc tiếp</a>
                    </div>
                    
                    <div class="news-item">
                        <h3>Đoàn Thanh niên PTIT đạt giải cao tại Hội thi Olympic các môn khoa học Mác-Lênin</h3>
                        <p class="text-muted"><i class="fa fa-calendar"></i> 01/04/2025</p>
                        <img src="https://doanthanhnien.vn/Content/uploads/images/133288612233655029_47dc4e4cf4342a6a7325.jpg" alt="Tin tức 3">
                        <p>Đoàn Thanh niên Học viện Công nghệ Bưu chính Viễn thông đã xuất sắc đạt giải Nhất toàn đoàn tại Hội thi Olympic các môn khoa học Mác-Lênin và tư tưởng Hồ Chí Minh cấp Bộ năm 2025. Đây là thành tích đáng tự hào, khẳng định sự nỗ lực không ngừng của đoàn viên, thanh niên PTIT trong học tập và nghiên cứu.</p>
                        <a href="#" class="btn btn-primary">Đọc tiếp</a>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="disabled">
                                <a href="#" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <li class="active"><a href="#">1</a></li>
                            <li><a href="#">2</a></li>
                            <li>
                                <a href="#" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
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


    <!-- JS -->
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <?php if (isset($_SESSION['login_error'])): ?>
<script>alert("<?= $_SESSION['login_error'] ?>");</script>
<?php unset($_SESSION['login_error']); endif; ?>

</body>
</html>