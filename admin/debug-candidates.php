<?php
// Debug script - verify what data admin page is receiving for candidates
require_once '../includes/config.php';

// Allow anyone logged in to view
if (!isset($_SESSION['user_id'])) {
    die('Login required');
}

if (!$pdo) {
    die('Database connection failed');
}

echo '<h2>Admin Debug - Candidate Data Check</h2>';

try {
    // Check the query being used in admin/candidate.php
    $candidateTableColumns = [];
    $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = 'candidates'");
    $stmt->execute();
    $candidateTableColumns = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
    
    echo '<h3>Candidates Table Columns:</h3>';
    echo '<p>' . implode(', ', $candidateTableColumns) . '</p>';
    
    // Check if image_url column exists
    echo '<h3>Image URL Column Check:</h3>';
    if (in_array('image_url', $candidateTableColumns, true)) {
        echo '<p>✅ image_url column EXISTS</p>';
    } else {
        echo '<p>❌ image_url column NOT found</p>';
    }
    
    // Now do a sample query similar to admin/candidate.php
    echo '<h3>Sample Query Results:</h3>';
    $selectParts = ['c.id', 'c.created_at'];
    
    if (in_array('status', $candidateTableColumns, true)) {
        $selectParts[] = 'c.status';
    }
    
    if (in_array('image_url', $candidateTableColumns, true)) {
        $selectParts[] = 'c.image_url';
    }
    
    if (in_array('profile_id', $candidateTableColumns, true)) {
        $selectParts[] = 'c.profile_id';
    } elseif (in_array('user_id', $candidateTableColumns, true)) {
        $selectParts[] = 'c.user_id';
    }
    
    $query = 'SELECT ' . implode(', ', $selectParts) . ' FROM candidates c ORDER BY c.created_at DESC LIMIT 5';
    echo '<p><strong>Query:</strong> <code>' . htmlspecialchars($query) . '</code></p>';
    
    $candidateStmt = $pdo->query($query);
    $candidates = $candidateStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
    echo '<tr style="background: #ddd;"><th>ID</th><th>image_url Value</th><th>Status</th><th>Created</th></tr>';
    foreach ($candidates as $candidate) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars(substr($candidate['id'], 0, 8)) . '...</td>';
        echo '<td><code style="font-size: 11px;">' . htmlspecialchars($candidate['image_url'] ?? 'NULL') . '</code></td>';
        echo '<td>' . htmlspecialchars($candidate['status'] ?? 'NULL') . '</td>';
        echo '<td>' . htmlspecialchars($candidate['created_at']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
} catch (Exception $e) {
    echo '<p style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
