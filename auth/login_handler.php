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
    $profile = getProfileByEmail($pdo, $identifier);
    if (!$profile) {
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

    $profileId = $profile['id'];
    $rawRole = $profile['role'] ?? 'student';
    $role = 'student';

    if ($rawRole === 'admin') {
        $role = 'admin';
        $_SESSION['candidate_application_mode'] = 0;
    } else {
        $hasApprovedCandidate = hasApprovedCandidateAccess($pdo, $profileId);
        $hasCandidateRequest = hasCandidateApplication($pdo, $profileId);

        // Candidate access requires both admin approval and explicit candidate role.
        if ($rawRole === 'candidate' && $hasApprovedCandidate) {
            $role = 'candidate';
            $_SESSION['candidate_application_mode'] = 0;
        } else {
            $role = 'student';
            $_SESSION['candidate_application_mode'] = $hasCandidateRequest ? 1 : 0;

            if ($rawRole === 'candidate') {
                $syncStmt = $pdo->prepare("UPDATE profiles SET role = 'student' WHERE id = ?");
                $syncStmt->execute([$profileId]);
            }
        }
    }

    $_SESSION['profile_id'] = $profileId;
    $_SESSION['email'] = $profile['email'];
    $_SESSION['first_name'] = $profile['first_name'];
    $_SESSION['last_name'] = $profile['last_name'];
    $_SESSION['role'] = $role;
    $_SESSION['student_id'] = $profile['student_id'];

    $redirect = '../student/dashboard.php';

    if ($role === 'admin') {
        $redirect = '../admin/dashboard.php';
    } elseif ($role === 'candidate') {
        $redirect = '../candidate/dashboard.php';
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful! Redirecting to dashboard...',
        'redirect' => $redirect
    ]);
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
}

exit;
?>
