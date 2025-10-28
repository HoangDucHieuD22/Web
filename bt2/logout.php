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
    <title>Quản lý sinh hoạt Đoàn</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        .panel-heading strong {
            font-size: 16px;
        }
        .panel-footer .btn {
            margin-left: 5px;
        }
        .panel {
            min-height: 250px;
        }
        .add-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
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





    
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>
