<?php
include __DIR__ . '/partials/header.php';
include __DIR__ . '/../../config/db_connect.php';

// Verify car_id is provided
if (!isset($_GET['car_id'])) {
  header('Location: trangchu.php');
  exit;
}

$car_id = intval($_GET['car_id']);

// Use explicit columns to avoid schema surprises and make errors clearer
$sql = "SELECT car_id, brand, model, image_url, price_per_day, description, is_available FROM cars WHERE car_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
  // Log DB error and redirect with a short message
  error_log('DB prepare failed in chitetxe.php: ' . $conn->error);
  header('Location: trangchu.php?error=db_prepare');
  exit;
}

$stmt->bind_param("i", $car_id);
if (!$stmt->execute()) {
  error_log('Prepared execute failed: ' . $stmt->error . ' -- SQL: ' . $sql);
  echo '<pre>Stmt Error: ' . htmlspecialchars($stmt->error) . '</pre>';
  exit;
}

$result = $stmt->get_result();
if ($result === false) {
  error_log('DB get_result failed in chitetxe.php: ' . $conn->error);
  header('Location: trangchu.php?error=db_result');
  exit;
}

if ($result->num_rows === 0) {
  header('Location: trangchu.php');
  exit;
}

$car = $result->fetch_assoc();
    $displayName = trim(($car['brand'] ?? '') . ' ' . ($car['model'] ?? ''));
    $image_url = $car['image_url'] ?? ($base . '/public/images/placeholder.png');
    $price_per_day = isset($car['price_per_day']) ? $car['price_per_day'] : ($car['price'] ?? 0);
  $is_available = isset($car['is_available']) ? (int)$car['is_available'] : 1;
  $stmt->close();

?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chi tiết xe - <?php echo htmlspecialchars($displayName); ?></title>
    <link rel="stylesheet" href="http://localhost:8080/tyqgwsgr_DbXe/public/css/main.css" />
  </head>
 
  <body>

    <div class="car-detail-container">
      <img
        class="car-detail-img"
        src="<?php echo htmlspecialchars($image_url); ?>"
        alt="<?php echo htmlspecialchars($displayName); ?>"
      />
      <div class="car-detail-info">
        <h2><?php echo htmlspecialchars($displayName); ?></h2>
        <p>Giá thuê: <b><?php echo number_format($price_per_day, 0); ?>đ/ngày</b></p>
        <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
        <p>Trạng thái: <?php echo $car['is_available'] ? 'Có sẵn' : 'Đã được đặt'; ?></p>
      </div>

      <?php if ($car['is_available']): ?>
      <form id="dat-xe-form" class="form-narrow" action="booking.php" method="GET">
        <h3>Đặt xe này</h3>
        <input type="hidden" name="car_id" value="<?php echo $car_id; ?>" />
        <button type="submit">Tiến hành đặt xe</button>
      </form>
      <?php else: ?>
      <div class="form-error">Xe hiện không khả dụng để đặt.</div>
      <?php endif; ?>
    </div>
    <?php mysqli_close($conn); ?>

  </body>
</html>
