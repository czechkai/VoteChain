<?php
/**
 * Candidate Filing Handler
 * Stores candidate record and documents
 */

require_once '../includes/config.php';
requireCandidateFilingAccess($pdo);

function filingTableExists($pdo, $tableName) {
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = CURRENT_SCHEMA() AND table_name = ? LIMIT 1");
        $stmt->execute([$tableName]);
        return $stmt->fetchColumn() !== false;
    } catch (Throwable $e) {
        error_log('Candidate filing table check error: ' . $e->getMessage());
        return false;
    }
}

function filingTableColumns($pdo, $tableName) {
    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = ?");
        $stmt->execute([$tableName]);
        return array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
    } catch (Throwable $e) {
        error_log('Candidate filing column check error: ' . $e->getMessage());
        return [];
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: filing.php');
    exit;
}

if (!$pdo) {
    error_log('Candidate filing error: Database connection failed');
    header('Location: filing.php?error=db');
    exit;
}

$profileId = $_SESSION['profile_id'] ?? null;
$electionId = $_POST['election_id'] ?? '';
$positionId = $_POST['position_id'] ?? '';

if (!$profileId || !$electionId || !$positionId) {
    error_log('Candidate filing error: Missing required fields - profileId: ' . ($profileId ? 'OK' : 'MISSING') . ', electionId: ' . ($electionId ? 'OK' : 'MISSING') . ', positionId: ' . ($positionId ? 'OK' : 'MISSING'));
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

$missingDocs = [];
foreach ($requiredDocs as $field => $label) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        $missingDocs[] = $field;
    }
}

if (!empty($missingDocs)) {
    error_log('Candidate filing error: Missing required documents - ' . implode(', ', $missingDocs));
    header('Location: filing.php?error=docs');
    exit;
}

try {
    $pdo->beginTransaction();

    $candidateTableColumns = filingTableColumns($pdo, 'candidates');
    $candidateIdColumn = in_array('profile_id', $candidateTableColumns, true)
        ? 'profile_id'
        : (in_array('user_id', $candidateTableColumns, true) ? 'user_id' : null);
    $candidateStatusColumn = in_array('status', $candidateTableColumns, true)
        ? 'status'
        : (in_array('filing_status', $candidateTableColumns, true) ? 'filing_status' : null);

    if (!$candidateIdColumn || !$candidateStatusColumn) {
        throw new RuntimeException('Unsupported candidate table schema.');
    }

    $checkStmt = $pdo->prepare(
        "SELECT id FROM candidates WHERE {$candidateIdColumn} = ? AND election_id = ? AND position_id = ?"
    );
    $checkStmt->execute([$profileId, $electionId, $positionId]);
    if ($checkStmt->fetch()) {
        $pdo->rollBack();
        header('Location: filing.php?error=duplicate');
        exit;
    }

    $candidateInsertColumns = [$candidateIdColumn, 'election_id', 'position_id', $candidateStatusColumn];
    $candidateInsertPlaceholders = ['?', '?', '?', '?'];
    $candidateStmt = $pdo->prepare(
        'INSERT INTO candidates (' . implode(', ', $candidateInsertColumns) . ')
         VALUES (' . implode(', ', $candidateInsertPlaceholders) . ')
         RETURNING id'
    );
    $candidateStmt->execute([$profileId, $electionId, $positionId, 'pending']);
    $candidateId = $candidateStmt->fetchColumn();

    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $photoFile = $_FILES['profile_photo'];
        $photoExt = strtolower(pathinfo($photoFile['name'], PATHINFO_EXTENSION));
        $allowedPhotos = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($photoExt, $allowedPhotos, true)) {
            $photoDir = __DIR__ . '/../uploads/candidate_images';
            if (!is_dir($photoDir)) {
                mkdir($photoDir, 0775, true);
            }

            $photoName = 'candidate-' . $candidateId . '-' . uniqid('', true) . '.' . $photoExt;
            $photoPath = $photoDir . '/' . $photoName;

            if (move_uploaded_file($photoFile['tmp_name'], $photoPath)) {
                $photoUrl = 'uploads/candidate_images/' . $photoName;

                // Try to update candidates table with image URL if column exists
                $candidateColumns = filingTableColumns($pdo, 'candidates');
                if (in_array('image_url', $candidateColumns, true)) {
                    $updatePhotoStmt = $pdo->prepare('UPDATE candidates SET image_url = ? WHERE id = ?');
                    $updatePhotoStmt->execute([$photoUrl, $candidateId]);
                } elseif (in_array('profile_photo', $candidateColumns, true)) {
                    $updatePhotoStmt = $pdo->prepare('UPDATE candidates SET profile_photo = ? WHERE id = ?');
                    $updatePhotoStmt->execute([$photoUrl, $candidateId]);
                }
            }
        }
    }

    $baseDir = __DIR__ . '/../uploads/candidate_documents/' . $candidateId;
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0775, true);
    }

    $documentTable = filingTableExists($pdo, 'candidate_documents') ? 'candidate_documents' : 'candidacy_filings';
    $documentColumns = filingTableColumns($pdo, $documentTable);
    $documentUseStatus = in_array('status', $documentColumns, true);
    $documentNameColumn = $documentTable === 'candidate_documents' ? 'document_name' : 'document_type';

    if (!in_array('document_url', $documentColumns, true)) {
        throw new RuntimeException('Document table is missing the document_url column.');
    }

    $documentInsertColumns = ['candidate_id', $documentNameColumn, 'document_url'];
    $documentInsertPlaceholders = ['?', '?', '?'];
    if ($documentUseStatus) {
        $documentInsertColumns[] = 'status';
        $documentInsertPlaceholders[] = '?';
    }

    $docStmt = $pdo->prepare(
        'INSERT INTO ' . $documentTable . ' (' . implode(', ', $documentInsertColumns) . ')
         VALUES (' . implode(', ', $documentInsertPlaceholders) . ')'
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
        $documentValues = [$candidateId, $label, $publicPath];
        if ($documentUseStatus) {
            $documentValues[] = 'pending';
        }

        $docStmt->execute($documentValues);
    }

    $pdo->commit();
    if (($_SESSION['role'] ?? 'student') !== 'candidate') {
        $_SESSION['candidate_application_mode'] = 1;
    }
    header('Location: filing.php?success=1');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $errorMsg = $e->getMessage();
    error_log('Candidate filing error: ' . $errorMsg);
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Store error in session for debugging
    $_SESSION['filing_error_details'] = $errorMsg;
    
    header('Location: filing.php?error=server');
    exit;
}
