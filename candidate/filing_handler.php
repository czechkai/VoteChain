<?php
/**
 * Candidate Filing Handler
 * Stores candidate record and documents
 */

require_once '../includes/config.php';
requireRole('candidate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: filing.php');
    exit;
}

if (!$pdo) {
    header('Location: filing.php?error=db');
    exit;
}

$profileId = $_SESSION['profile_id'] ?? null;
$electionId = $_POST['election_id'] ?? '';
$positionId = $_POST['position_id'] ?? '';

if (!$profileId || !$electionId || !$positionId) {
    header('Location: filing.php?error=missing');
    exit;
}

$requiredDocs = [
    'cert_candidacy' => 'Certificate of Candidacy',
    'cert_registration' => 'Certificate of Registration',
    'report_grades' => 'Report of Grades',
    'good_moral' => 'Good Moral Character',
    'recommendation' => 'Recommendation Letter'
];

foreach ($requiredDocs as $field => $label) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        header('Location: filing.php?error=docs');
        exit;
    }
}

try {
    $pdo->beginTransaction();

    $checkStmt = $pdo->prepare(
        "SELECT id FROM candidates WHERE profile_id = ? AND election_id = ? AND position_id = ?"
    );
    $checkStmt->execute([$profileId, $electionId, $positionId]);
    if ($checkStmt->fetch()) {
        $pdo->rollBack();
        header('Location: filing.php?error=duplicate');
        exit;
    }

    $candidateStmt = $pdo->prepare(
        "INSERT INTO candidates (profile_id, election_id, position_id, status)
         VALUES (?, ?, ?, 'pending')
         RETURNING id"
    );
    $candidateStmt->execute([$profileId, $electionId, $positionId]);
    $candidateId = $candidateStmt->fetchColumn();

    $baseDir = __DIR__ . '/../uploads/candidate_documents/' . $candidateId;
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0775, true);
    }

    $docStmt = $pdo->prepare(
        "INSERT INTO candidate_documents (candidate_id, document_name, document_url, status)
         VALUES (?, ?, ?, 'pending')"
    );

    foreach ($requiredDocs as $field => $label) {
        $file = $_FILES[$field];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed, true)) {
            $pdo->rollBack();
            header('Location: filing.php?error=type');
            exit;
        }

        $safeName = $field . '-' . uniqid('', true) . '.' . $ext;
        $targetPath = $baseDir . '/' . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $pdo->rollBack();
            header('Location: filing.php?error=upload');
            exit;
        }

        $publicPath = 'uploads/candidate_documents/' . $candidateId . '/' . $safeName;
        $docStmt->execute([$candidateId, $label, $publicPath]);
    }

    $pdo->commit();
    header('Location: filing.php?success=1');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Candidate filing error: ' . $e->getMessage());
    header('Location: filing.php?error=server');
    exit;
}
