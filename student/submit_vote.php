<?php
require_once '../includes/config.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vote.php');
    exit;
}

if (!$pdo) {
    header('Location: ballot.php?error=1');
    exit;
}

$electionId = $_POST['election_id'] ?? '';
$votes = $_POST['votes'] ?? [];
$voterProfileId = $_SESSION['profile_id'] ?? null;

if (!$electionId || !$voterProfileId || !is_array($votes)) {
    header('Location: ballot.php?error=1');
    exit;
}

try {
    $electionStmt = $pdo->prepare("SELECT id FROM elections WHERE id = ? AND status = 'active' LIMIT 1");
    $electionStmt->execute([$electionId]);
    if (!$electionStmt->fetch()) {
        header('Location: ballot.php?error=1');
        exit;
    }

    $positionStmt = $pdo->prepare(
        "SELECT DISTINCT pos.id
         FROM positions pos
         JOIN candidates c ON c.position_id = pos.id
         WHERE c.election_id = ? AND c.status = 'approved'"
    );
    $positionStmt->execute([$electionId]);
    $requiredPositions = $positionStmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($requiredPositions as $positionId) {
        if (!isset($votes[$positionId]) || !$votes[$positionId]) {
            header('Location: ballot.php?election_id=' . urlencode($electionId) . '&error=1');
            exit;
        }
    }

    $candidateCheck = $pdo->prepare(
        "SELECT id FROM candidates WHERE id = ? AND election_id = ? AND position_id = ? AND status = 'approved'"
    );

    $pdo->beginTransaction();

    foreach ($votes as $positionId => $candidateId) {
        if (hasUserVoted($pdo, $voterProfileId, $electionId, $positionId)) {
            $pdo->rollBack();
            header('Location: ballot.php?election_id=' . urlencode($electionId) . '&error=1');
            exit;
        }

        $candidateCheck->execute([$candidateId, $electionId, $positionId]);
        if (!$candidateCheck->fetch()) {
            $pdo->rollBack();
            header('Location: ballot.php?election_id=' . urlencode($electionId) . '&error=1');
            exit;
        }

        $record = recordVote($pdo, $voterProfileId, $electionId, $positionId, $candidateId, null);
        if (!$record['success']) {
            $pdo->rollBack();
            header('Location: ballot.php?election_id=' . urlencode($electionId) . '&error=1');
            exit;
        }
    }

    $pdo->commit();
    header('Location: ballot.php?election_id=' . urlencode($electionId) . '&success=1');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Submit vote error: ' . $e->getMessage());
    header('Location: ballot.php?election_id=' . urlencode($electionId) . '&error=1');
    exit;
}
