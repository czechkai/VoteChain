<?php
require_once 'config.php';
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$election_id = $data['election_id'] ?? null;

if (!$election_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Election ID required']);
    exit;
}

// Verify vote chain integrity
$result = verifyVoteChain($pdo, $election_id);
echo json_encode($result);
?>
