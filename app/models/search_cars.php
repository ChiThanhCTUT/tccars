<?php
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../../config/db_connect.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$price = isset($_GET['price']) ? $_GET['price'] : '';

$params = [];
$sql = "SELECT car_id, brand, model, price_per_day, image_url, description FROM cars WHERE is_available = 1";
$where = [];

if ($q !== '') {
    // Search across brand, model, description
    $where[] = "(brand LIKE ? OR model LIKE ? OR description LIKE ?)";
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// price filters come as special values matching the select in UI
if ($price !== '') {
    if ($price === '500') {
        $where[] = "price_per_day <= ?";
        $params[] = 500000;
    } elseif ($price === '700') {
        $where[] = "price_per_day > ? AND price_per_day <= ?";
        $params[] = 500000;
        $params[] = 700000;
    } elseif ($price === '1000') {
        $where[] = "price_per_day > ? AND price_per_day <= ?";
        $params[] = 700000;
        $params[] = 1000000;
    } elseif ($price === '1001') {
        $where[] = "price_per_day > ?";
        $params[] = 1000000;
    }
}

if (!empty($where)) {
    $sql .= ' AND ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY car_id';

$stmt = mysqli_prepare($conn, $sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed', 'detail' => mysqli_error($conn)]);
    exit;
}

// bind params dynamically
if (!empty($params)) {
    $types = '';
    foreach ($params as $p) {
        if (is_int($p)) $types .= 'i'; else $types .= 's';
    }
    // mysqli_prepare requires variables to bind by reference
    $bind_names = [];
    $bind_names[] = $types;
    for ($i=0; $i<count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
}

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['error' => 'Execute failed', 'detail' => mysqli_stmt_error($stmt)]);
    exit;
}

$res = mysqli_stmt_get_result($stmt);
$cars = [];
while ($row = mysqli_fetch_assoc($res)) {
    $cars[] = [
        'car_id' => $row['car_id'],
        'brand' => $row['brand'],
        'model' => $row['model'],
        'price_per_day' => (int)$row['price_per_day'],
        'image_url' => $row['image_url'],
        'description' => $row['description']
    ];
}

echo json_encode(['data' => $cars], JSON_UNESCAPED_UNICODE);
exit;

?>
