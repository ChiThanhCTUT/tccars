<?php
/**
 * File Path Diagnostic Report
 * Kiểm tra tất cả các đường dẫn trong ứng dụng MVC
 */

$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'base_path' => __DIR__,
    'structure_check' => [],
    'path_issues' => []
];

// Define expected directory structure
$expected_dirs = [
    'config/' => 'Central configuration',
    'app/' => 'Application (MVC)',
    'app/models/' => 'Business logic',
    'app/views/' => 'User interface',
    'app/controllers/' => 'Request handlers',
    'admin/' => 'Admin panel',
    'admin/models/' => 'Admin models',
    'admin/views/' => 'Admin views',
    'public/' => 'Static assets',
    'public/css/' => 'Stylesheets',
    'public/js/' => 'JavaScript files',
    'public/images/' => 'Image storage'
];

// Check if directories exist
foreach ($expected_dirs as $dir => $purpose) {
    $path = __DIR__ . '/' . $dir;
    $exists = is_dir($path);
    $report['structure_check'][] = [
        'path' => $dir,
        'purpose' => $purpose,
        'exists' => $exists,
        'status' => $exists ? '✓ OK' : '✗ MISSING'
    ];

    if (!$exists) {
        $report['path_issues'][] = "Missing directory: $dir ($purpose)";
    }
}

// Check critical files
$critical_files = [
    'config/db_connect.php' => 'Database connection',
    'app/views/partials/header.php' => 'Header partial',
    'app/models/process_booking.php' => 'Booking processor',
    'app/models/search_cars.php' => 'Car search API',
    'app/controllers/xulydangnhap.php' => 'Login handler',
    'app/controllers/xulydangky.php' => 'Registration handler',
    'admin/views/admin_header.php' => 'Admin header',
    'public/js/chucnang.js' => 'Main JavaScript',
    'public/css/main.css' => 'Main CSS'
];

$report['critical_files'] = [];
foreach ($critical_files as $file => $purpose) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $report['critical_files'][] = [
        'file' => $file,
        'purpose' => $purpose,
        'exists' => $exists,
        'status' => $exists ? '✓ OK' : '✗ MISSING',
        'size_bytes' => $exists ? filesize($path) : 0
    ];

    if (!$exists) {
        $report['path_issues'][] = "Missing critical file: $file ($purpose)";
    }
}

// Check for old/deprecated paths
$report['deprecated_check'] = [
    'USER/HTML/' => 'Old user HTML folder (should use app/views/)',
    'USER/css/' => 'Old CSS folder (should use public/css/)',
    'USER/js/' => 'Old JS folder (should use public/js/)',
    'USER/CSDl/' => 'Old models folder (should use app/models/)',
    'admin/HTML/' => 'Old admin HTML folder (should use admin/views/)',
    'admin/CSS/' => 'Old admin CSS folder (should use admin/views/)'
];

$report['deprecated_paths'] = [];
foreach ($report['deprecated_check'] as $old_path => $reason) {
    $path = __DIR__ . '/' . $old_path;
    $exists = is_dir($path) || file_exists($path);
    $report['deprecated_paths'][] = [
        'path' => $old_path,
        'reason' => $reason,
        'still_exists' => $exists,
        'status' => $exists ? '⚠ WARNING - Should be removed' : '✓ Removed'
    ];
}

// Check include/require paths in key files
$report['include_path_check'] = [];
$files_to_check = [
    'app/views/booking.php',
    'app/models/process_booking.php',
    'app/controllers/xulydangnhap.php',
    'admin/views/admin_header.php'
];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $content = file_get_contents($full_path);
        
        // Check for problematic patterns
        $issues = [];
        
        if (strpos($content, '/USER/') !== false) {
            $issues[] = "Contains /USER/ path reference";
        }
        if (strpos($content, '/app/HTML') !== false) {
            $issues[] = "Contains /app/HTML path reference";
        }
        if (strpos($content, '/admin/HTML') !== false) {
            $issues[] = "Contains /admin/HTML path reference";
        }
        if (preg_match('/include|require/i', $content) && strpos($content, '../../config') === false && strpos($content, '__DIR__') === false) {
            // Some includes might be OK with relative paths if properly positioned
        }
        
        $report['include_path_check'][] = [
            'file' => $file,
            'issues' => count($issues) > 0 ? $issues : ['No issues detected'],
            'status' => count($issues) > 0 ? '⚠ Needs review' : '✓ OK'
        ];
    }
}

// Database connectivity check
$report['database_check'] = [
    'host' => 'localhost',
    'database' => 'tyqgwsgr_dbxe',
    'required_tables' => ['users', 'cars', 'bookings']
];

// Test database connection
try {
    $conn = mysqli_connect('localhost', 'root', '', 'tyqgwsgr_dbxe');
    if ($conn) {
        $result = mysqli_query($conn, "SHOW TABLES");
        $tables = [];
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }
        $report['database_check']['actual_tables'] = $tables;
        $report['database_check']['status'] = 'Connected';
        
        // Check each required table
        foreach ($report['database_check']['required_tables'] as $table) {
            $exists = in_array($table, $tables);
            $report['database_check'][$table . '_exists'] = $exists;
        }
        
        mysqli_close($conn);
    } else {
        $report['database_check']['status'] = 'Connection Failed: ' . mysqli_connect_error();
    }
} catch (Exception $e) {
    $report['database_check']['status'] = 'Error: ' . $e->getMessage();
}

// Summary
$report['summary'] = [
    'total_directories_checked' => count($report['structure_check']),
    'directories_ok' => count(array_filter($report['structure_check'], fn($d) => $d['exists'])),
    'total_files_checked' => count($report['critical_files']),
    'files_ok' => count(array_filter($report['critical_files'], fn($f) => $f['exists'])),
    'total_issues' => count($report['path_issues']),
    'deprecated_paths_found' => count(array_filter($report['deprecated_paths'], fn($p) => $p['still_exists']))
];

// Output as JSON with formatting for readability
header('Content-Type: application/json; charset=utf-8');
echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
