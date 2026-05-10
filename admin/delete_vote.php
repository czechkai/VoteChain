<?php
require_once __DIR__ . '/../includes/config.php';
requireRole('admin');
header('Content-Type: application/json');

$vote_id = $_POST['vote_id'] ?? null;
if (!$vote_id) {
    echo json_encode(['success' => false, 'message' => 'vote_id required']);
    exit;
}

try {
    // fetch vote for logging
    $stmt = $pdo->prepare('SELECT * FROM votes WHERE id = ?');
    $stmt->execute([$vote_id]);
    $vote = $stmt->fetch();

    if (!$vote) {
        echo json_encode(['success' => false, 'message' => 'Vote not found']);
        exit;
    }

    // delete the vote
    $del = $pdo->prepare('DELETE FROM votes WHERE id = ?');
    $del->execute([$vote_id]);

    // log action
    $logfile = __DIR__ . '/../logs/admin_actions.log';
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . ($_SESSION['profile_id'] ?? 'unknown') . " - DELETE vote_id={$vote_id} tx=" . ($vote['tx_hash'] ?? '') . "\n";
    file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);

    echo json_encode(['success' => true, 'message' => 'Vote deleted']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
