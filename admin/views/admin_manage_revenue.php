<?php
// admin_manage_revenue.php
$page_title = 'Báo cáo Doanh thu';
include 'admin_header.php'; // includes DB and checks admin, outputs header

// Aggregate revenue by month (last 12 months)
$revenue_sql = "SELECT YEAR(booking_date) AS y, MONTH(booking_date) AS m, 
					   SUM(total_price) AS total
				FROM bookings
				WHERE status = 'Confirmed'
				GROUP BY y, m
				ORDER BY y DESC, m DESC
				LIMIT 12";
$res = $conn->query($revenue_sql);

// Total revenue overall
$total_row = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status='Confirmed'")->fetch_assoc();
$total_revenue = $total_row['total'] ?? 0;
?>

<div class="admin-card">
	<h2>Báo cáo Doanh thu</h2>
	<p>Tổng doanh thu: <strong><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</strong></p>

	<table class="table">
		<thead>
			<tr>
				<th>Tháng</th>
				<th>Tổng doanh thu</th>
			</tr>
		</thead>
		<tbody>
		<?php
		if ($res && $res->num_rows > 0) {
			while ($row = $res->fetch_assoc()) {
				$monthLabel = sprintf('%02d/%04d', $row['m'], $row['y']);
				echo '<tr><td>' . $monthLabel . '</td><td>' . number_format($row['total'], 0, ',', '.') . 'đ</td></tr>';
			}
		} else {
			echo '<tr><td colspan="2">Chưa có dữ liệu doanh thu.</td></tr>';
		}
		?>
		</tbody>
	</table>
</div>

<?php
mysqli_close($conn);
include 'admin_footer.php';
?>
