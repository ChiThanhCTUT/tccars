<?php
// We use admin_header.php which already includes db_connect and session checks
include 'admin_header.php';
?>

            <div class="admin-actions-row">
                <div class="admin-actions">
                    <a href="admin_form_car.php" class="btn">+ Thêm xe mới</a>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="admin-card mb-12">
                <div class="form-row">
                    <?php
                    // Đếm tổng số xe
                    $cars = $conn->query("SELECT COUNT(*) as total FROM cars")->fetch_assoc();
                    // Đếm đơn chờ duyệt
                    $pending = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status='Pending'")->fetch_assoc();
                    // Tổng doanh thu
                    $revenue = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status='Confirmed'")->fetch_assoc();
                    ?>
                    <div class="field">
                        <label>Tổng số xe</label>
                        <h3><?php echo $cars['total']; ?> xe</h3>
                    </div>
                    <div class="field">
                        <label>Đơn chờ duyệt</label>
                        <h3><?php echo $pending['total']; ?> đơn</h3>
                    </div>
                    <div class="field">
                        <label>Doanh thu</label>
                        <h3><?php echo number_format($revenue['total'], 0, ',', '.'); ?>đ</h3>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="admin-card">
                <h2>Đơn thuê gần đây</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Xe</th>
                            <th>Ngày thuê</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Lấy 5 đơn gần nhất
                    $sql = "SELECT b.*, u.username, c.brand, c.model 
                           FROM bookings b 
                           JOIN users u ON b.user_id = u.id
                           JOIN cars c ON b.car_id = c.car_id
                           ORDER BY b.booking_date DESC LIMIT 5";
                    $result = $conn->query($sql);
                    
                    while ($booking = $result->fetch_assoc()) {
                        $status_class = $booking['status'] == 'Confirmed' ? 'success' : 
                                      ($booking['status'] == 'Cancelled' ? 'danger' : 'muted');
                        echo "<tr>
                                <td>#{$booking['booking_id']}</td>
                                <td>{$booking['username']}</td>
                                <td>{$booking['brand']} {$booking['model']}</td>
                                <td>" . date('d/m/Y', strtotime($booking['start_date'])) . "</td>
                                <td><span class='badge {$status_class}'>{$booking['status']}</span></td>
                                <td class='text-right'>" . number_format($booking['total_price'], 0, ',', '.') . "đ</td>
                            </tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
<?php
mysqli_close($conn);
// include footer to close tags
include 'admin_footer.php';
?>