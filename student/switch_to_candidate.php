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
    if (hasApprovedCandidateAccess($pdo, $profileId)) {
        $_SESSION['role'] = 'candidate';
        $_SESSION['candidate_application_mode'] = 0;
        header('Location: ../candidate/dashboard.php');
        exit;
    }

    // Student can start/continue candidacy application, but role remains student until approved.
    $_SESSION['candidate_application_mode'] = 1;
    header('Location: ../candidate/filing.php?application=1');
    exit;
} catch (Exception $e) {
    error_log('Switch to candidate error: ' . $e->getMessage());
    header('Location: dashboard.php');
    exit;
}
