<?php
require_once 'config.php';

if (!isLoggedIn() || (($_SESSION['role'] ?? null) !== 'admin')) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$election_id = $data['election_id'] ?? null;
$confirm = strtoupper(trim((string) ($data['confirm'] ?? '')));

if (!$election_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Election ID required']);
    exit;
}

if ($confirm !== 'RESTORE') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Confirmation keyword required']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT id, voter_profile_id, position_id, candidate_id, tx_hash, prev_hash
         FROM votes
         WHERE election_id = ?
         ORDER BY created_at ASC, id ASC
         FOR UPDATE"
    );
    $stmt->execute([$election_id]);
    $votes = $stmt->fetchAll();

    $expectedPrev = 'GENESIS';
    $repairedCount = 0;

    $updateStmt = $pdo->prepare("UPDATE votes SET prev_hash = ?, tx_hash = ? WHERE id = ?");

    foreach ($votes as $vote) {
        $payload = implode('|', [
            $election_id,
            $vote['voter_profile_id'],
            $vote['position_id'],
            $vote['candidate_id'],
            $expectedPrev
        ]);

        $expectedHash = hash('sha256', $payload);

        if ($vote['prev_hash'] !== $expectedPrev || $vote['tx_hash'] !== $expectedHash) {
            $repairedCount++;
        }

        $updateStmt->execute([$expectedPrev, $expectedHash, $vote['id']]);
        $expectedPrev = $expectedHash;
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $repairedCount > 0 ? 'Chain restored successfully.' : 'Chain already matched the restored state.',
        'repaired_count' => $repairedCount,
        'total_votes' => count($votes)
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Restore failed',
        'error' => $e->getMessage()
    ]);
}
?>