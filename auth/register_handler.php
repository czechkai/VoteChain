<?php
/**
 * Registration Handler
 * Processes form submission and stores profile in Supabase
 */

include '../includes/config.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

// Collect and sanitize form data
$first_name = sanitize($_POST['fname'] ?? '');
$last_name = sanitize($_POST['lname'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$student_id = sanitize($_POST['sid'] ?? '');
$year_level = sanitize($_POST['year_level'] ?? '');
$faculty_code = sanitize($_POST['faculty'] ?? '');
$program_code = sanitize($_POST['program'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate required fields
if (!$first_name || !$last_name || !$email || !$student_id || !$faculty_code || !$program_code || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate password match
if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

// Validate password strength
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit;
}

// Check database connection
if (!$pdo) {
    http_response_code(500);
    $detail = isset($db_error) && $db_error ? $db_error : 'Unknown database error';
    echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $detail]);
    exit;
}

// Create profile
$profile_data = [
    'first_name' => $first_name,
    'last_name' => $last_name,
    'email' => $email,
    'student_id' => $student_id,
    'year_level' => $year_level,
    'faculty_code' => $faculty_code,
    'program_code' => $program_code,
    'password' => $password,
    'role' => 'student'
];

$result = createProfile($pdo, $profile_data);

if ($result['success']) {
    // Log them in automatically
    $auth_result = authenticateProfile($pdo, $email, $password);
    
    if ($auth_result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Please log in.',
            'redirect' => '../auth/login.php'
        ]);
    } else {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Please log in.',
            'redirect' => '../auth/login.php'
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $result['message']
    ]);
}

exit;
?>