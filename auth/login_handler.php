<?php
/**
 * Login Handler
 * Verifies credentials against Supabase profiles table
 */

include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

$identifier = sanitize($_POST['identifier'] ?? '');
$password = $_POST['password'] ?? '';

if (!$identifier || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!$pdo) {
    http_response_code(500);
    $detail = isset($db_error) && $db_error ? $db_error : 'Unknown database error';
    echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $detail]);
    exit;
}

try {
    if (strpos($identifier, '@') !== false) {
        $profile = getProfileByEmail($pdo, $identifier);
    } else {
        $profile = getProfileByStudentId($pdo, $identifier);
    }

    if (!$profile) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    if (!password_verify($password, $profile['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    $_SESSION['profile_id'] = $profile['id'];
    $_SESSION['email'] = $profile['email'];
    $_SESSION['first_name'] = $profile['first_name'];
    $_SESSION['last_name'] = $profile['last_name'];
    $_SESSION['role'] = $profile['role'];
    $_SESSION['student_id'] = $profile['student_id'];

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful! Redirecting to dashboard...',
        'redirect' => '../student/dashboard.php'
    ]);
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
}

exit;
?>
