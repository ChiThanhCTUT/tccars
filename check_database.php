<?php
/**
 * Database Diagnostic Script
 * Kiểm tra toàn bộ cơ sở dữ liệu, schema, và integrity
 */

include_once __DIR__ . '/config/db_connect.php';

// Set header for JSON output
header('Content-Type: application/json; charset=utf-8');

$diagnostic = [
    'timestamp' => date('Y-m-d H:i:s'),
    'database' => 'tyqgwsgr_dbxe',
    'connection' => 'OK',
    'tables' => [],
    'issues' => []
];

// 1. Check connection
if (!$conn) {
    $diagnostic['connection'] = 'FAILED: ' . mysqli_connect_error();
    echo json_encode($diagnostic, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 2. Get all tables
$result = mysqli_query($conn, "SHOW TABLES");
$tables = [];
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// 3. For each table, get detailed schema
foreach (['users', 'cars', 'bookings'] as $table_name) {
    if (!in_array($table_name, $tables)) {
        $diagnostic['issues'][] = "CRITICAL: Table '$table_name' does not exist!";
        continue;
    }

    $table_info = [
        'name' => $table_name,
        'columns' => [],
        'row_count' => 0,
        'sample_data' => []
    ];

    // Get columns
    $cols = mysqli_query($conn, "DESCRIBE $table_name");
    while ($col = mysqli_fetch_assoc($cols)) {
        $table_info['columns'][] = [
            'name' => $col['Field'],
            'type' => $col['Type'],
            'null' => $col['Null'],
            'key' => $col['Key'],
            'default' => $col['Default']
        ];
    }

    // Get row count
    $count = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM $table_name");
    $count_row = mysqli_fetch_assoc($count);
    $table_info['row_count'] = (int)$count_row['cnt'];

    // Get sample rows (limit 3)
    $sample = mysqli_query($conn, "SELECT * FROM $table_name LIMIT 3");
    while ($row = mysqli_fetch_assoc($sample)) {
        $table_info['sample_data'][] = $row;
    }

    $diagnostic['tables'][$table_name] = $table_info;
}

// 4. Verify foreign key relationships
$diagnostic['relationships'] = [];

// Check bookings -> users
$fk_users = mysqli_query($conn, "SELECT COUNT(*) as unmatched FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE u.id IS NULL");
$fk_users_row = mysqli_fetch_assoc($fk_users);
if ($fk_users_row['unmatched'] > 0) {
    $diagnostic['issues'][] = "WARNING: " . $fk_users_row['unmatched'] . " booking(s) with missing user_id reference";
}

// Check bookings -> cars
$fk_cars = mysqli_query($conn, "SELECT COUNT(*) as unmatched FROM bookings b LEFT JOIN cars c ON b.car_id = c.car_id WHERE c.car_id IS NULL");
$fk_cars_row = mysqli_fetch_assoc($fk_cars);
if ($fk_cars_row['unmatched'] > 0) {
    $diagnostic['issues'][] = "WARNING: " . $fk_cars_row['unmatched'] . " booking(s) with missing car_id reference";
}

// 5. Data validation checks
// Check for users with NULL critical fields
$null_check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE username IS NULL OR email IS NULL OR password IS NULL");
$null_row = mysqli_fetch_assoc($null_check);
if ($null_row['cnt'] > 0) {
    $diagnostic['issues'][] = "WARNING: " . $null_row['cnt'] . " user(s) with NULL critical fields";
}

// Check for cars with NULL price_per_day
$cars_null = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM cars WHERE price_per_day IS NULL OR brand IS NULL OR model IS NULL");
$cars_null_row = mysqli_fetch_assoc($cars_null);
if ($cars_null_row['cnt'] > 0) {
    $diagnostic['issues'][] = "WARNING: " . $cars_null_row['cnt'] . " car(s) with NULL critical fields";
}

// 6. Query performance test
$diagnostic['query_tests'] = [
    'users_query' => 'SELECT id, username, email, is_admin FROM users WHERE username = ?',
    'cars_query' => 'SELECT car_id, brand, model, price_per_day, is_available FROM cars WHERE is_available = 1',
    'bookings_query' => 'SELECT booking_id, user_id, car_id, start_date, end_date, total_price, status FROM bookings WHERE status = "confirmed"'
];

// 7. Session and config check
$diagnostic['config'] = [
    'base_url' => defined('BASE_URL') ? BASE_URL : 'NOT DEFINED',
    'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE',
    'php_version' => phpversion(),
    'mysqli_version' => mysqli_get_server_info($conn)
];

mysqli_close($conn);

echo json_encode($diagnostic, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
