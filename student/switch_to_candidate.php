<?php
require_once '../includes/config.php';
requireRole('student');

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
    $stmt = $pdo->prepare("UPDATE profiles SET role = 'candidate' WHERE id = ?");
    $stmt->execute([$profileId]);

    $_SESSION['role'] = 'candidate';
    header('Location: ../candidate/dashboard.php');
    exit;
} catch (Exception $e) {
    error_log('Switch to candidate error: ' . $e->getMessage());
    header('Location: dashboard.php');
    exit;
}
