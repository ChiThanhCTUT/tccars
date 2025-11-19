<?php
// admin_form_car.php

$page_title = "Thêm Xe Mới"; // Tiêu đề mặc định
include 'admin_header.php'; // Đã bao gồm db_connect và kiểm tra admin

// 1. KHỞI TẠO BIẾN
$is_editing = false;
$car_id = null;
// Mảng $car rỗng để điền vào form "Thêm mới" (tránh lỗi)
$car = [
    'brand' => '',
    'model' => '',
    'year' => date('Y'), // Tự điền năm hiện tại
    'description' => '',
    'price_per_day' => '',
    'image_url' => '',
    'is_available' => 1
];
$message = ""; // Biến lưu thông báo

// 2. KIỂM TRA CHẾ ĐỘ "SỬA" (GET)
// Nếu có edit_id trên URL, chúng ta đang Sửa
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $is_editing = true;
    $car_id = $_GET['edit_id'];
    $page_title = "Cập Nhật Xe";

    // Lấy thông tin xe từ CSDL để điền vào form
    $stmt = $conn->prepare("SELECT * FROM cars WHERE car_id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $car = $result->fetch_assoc(); // Ghi đè mảng $car
    } else {
        $message = "<p style='color:red;'>Lỗi: Không tìm thấy xe!</p>";
    }
    $stmt->close();
}

// 3. XỬ LÝ DỮ LIỆU KHI FORM ĐƯỢC GỬI (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $description = $_POST['description'];
    $price_per_day = $_POST['price_per_day'];
    $image_url = $_POST['image_url'];
    $is_available = $_POST['is_available'];
    $posted_car_id = $_POST['car_id'] ?? null; // Lấy ID (nếu có) từ form

    try {
        // CSRF token check
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $message = "<p style='color:red;'>Yêu cầu không hợp lệ (CSRF).</p>";
        } else {
        if ($posted_car_id) {
            // Chế độ UPDATE (Cập nhật)
            $sql = "UPDATE cars SET brand=?, model=?, year=?, description=?, price_per_day=?, image_url=?, is_available=? WHERE car_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisdsii", $brand, $model, $year, $description, $price_per_day, $image_url, $is_available, $posted_car_id);
            $message = "<p style='color:green;'>Cập nhật xe thành công!</p>";
        } else {
            // Chế độ INSERT (Thêm mới)
            $sql = "INSERT INTO cars (brand, model, year, description, price_per_day, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisdsi", $brand, $model, $year, $description, $price_per_day, $image_url, $is_available);
            $message = "<p style='color:green;'>Thêm xe mới thành công!</p>";
        }

        $stmt->execute();
        $stmt->close();
        }
        
        // Gửi thông báo và chuyển hướng về trang Quản lý Xe
        echo $message . " Đang chuyển hướng về trang quản lý...";
        echo "<meta http-equiv='refresh' content='2;url=admin_manage_cars.php'>";
        
        // Không include footer và không hiển thị form nữa
        exit(); 

    } catch (Exception $e) {
        $message = "<p style='color:red;'>Lỗi CSDL: " . $e->getMessage() . "</p>";
    }
}

// 4. HIỂN THỊ HTML (Form)
?>

<div class="container form-container">
    
    <?php echo $message; // Hiển thị thông báo (nếu có) ?>

    <form action="admin_form_car.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        
        <?php if ($is_editing): ?>
            <input type="hidden" name="car_id" value="<?php echo $car['car_id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="brand">Hãng xe (Brand)</label>
            <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($car['brand']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="model">Mẫu xe (Model)</label>
            <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($car['model']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="year">Năm sản xuất</label>
            <input type="number" id="year" name="year" value="<?php echo htmlspecialchars($car['year']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="price_per_day">Giá / ngày (VNĐ)</label>
            <input type="number" id="price_per_day" name="price_per_day" step="1000" value="<?php echo htmlspecialchars($car['price_per_day']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="image_url">Link hình ảnh (URL)</label>
            <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($car['image_url']); ?>">
        </div>
        
        <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($car['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="is_available">Trạng thái</label>
            <select id="is_available" name="is_available">
                <option value="1" <?php if ($car['is_available'] == 1) echo 'selected'; ?>>Có sẵn (Available)</option>
                <option value="0" <?php if ($car['is_available'] == 0) echo 'selected'; ?>>Đã thuê (Not Available)</option>
            </select>
        </div>
        
        <button type="submit" class="btn-submit"><?php echo $page_title; ?></button>
    </form>
    
    <a href="admin_manage_cars.php" style="display:inline-block; margin-top: 15px;">← Quay về Danh sách xe</a>
</div>
<?php
mysqli_close($conn);
include 'admin_footer.php'; // Include footer
?>