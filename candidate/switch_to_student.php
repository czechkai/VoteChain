<?php
require_once '../includes/config.php';
requireRole('candidate');

if (!$pdo) {
    header('Location: dashboard.php');
    exit;
}

$profileId = $_SESSION['profile_id'] ?? null;
if (!$profileId) {
    header('Location: ../auth/login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE profiles SET role = 'student' WHERE id = ?");
    $stmt->execute([$profileId]);

    $_SESSION['role'] = 'student';
    $_SESSION['candidate_application_mode'] = hasCandidateApplication($pdo, $profileId) ? 1 : 0;

    header('Location: ../student/dashboard.php');
    exit;
} catch (Exception $e) {
    error_log('Switch to student error: ' . $e->getMessage());
    header('Location: dashboard.php');
    exit;
}
