<?php
/**
 * VoteChain - Blockchain Tampering Test
 * This script allows you to simulate vote tampering for testing the blockchain verification
 */

require_once 'includes/config.php';

// Check if tampering action requested
$action = $_GET['action'] ?? null;
$message = '';
$success = false;

if ($action === 'tamper' && $pdo) {
    try {
        // Find the most recent vote
        $stmt = $pdo->prepare("
            SELECT id, tx_hash FROM votes 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $vote = $stmt->fetch();
        
        if ($vote) {
            // Corrupt the hash (add some characters to make it invalid)
            $corruptedHash = 'corrupted_' . substr($vote['tx_hash'], 10);
            
            $updateStmt = $pdo->prepare("UPDATE votes SET tx_hash = ? WHERE id = ?");
            $updateStmt->execute([$corruptedHash, $vote['id']]);
            
            $message = "✓ Vote #" . $vote['id'] . " tampered! Hash changed from:<br>
                       <code style='font-size:11px; background:#f0f0f0; padding:8px; display:block; margin:10px 0;'>" . substr($vote['tx_hash'], 0, 20) . "...</code>
                       to:<br>
                       <code style='font-size:11px; background:#f0f0f0; padding:8px; display:block; margin:10px 0;'>" . $corruptedHash . "</code>";
            $success = true;
        } else {
            $message = "✗ No votes found in database";
        }
    } catch (Exception $e) {
        $message = "✗ Error: " . $e->getMessage();
    }
} elseif ($action === 'restore' && $pdo) {
    try {
        // Delete all tampered votes (those with 'corrupted_' prefix)
        $stmt = $pdo->prepare("DELETE FROM votes WHERE tx_hash LIKE ?");
        $stmt->execute(['corrupted_%']);
        
        $deletedCount = $stmt->rowCount();
        if ($deletedCount > 0) {
            $message = "✓ Restored! Deleted $deletedCount tampered vote(s). You can now re-vote if needed.";
            $success = true;
        } else {
            $message = "ℹ No tampered votes found. Everything is already clean.";
            $success = true;
        }
    } catch (Exception $e) {
        $message = "✗ Error: " . $e->getMessage();
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoteChain - Tampering Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-3xl shadow-lg p-8">
            <div class="flex items-center gap-3 mb-8">
                <i class="fa-solid fa-flask-vial text-royal text-3xl"></i>
                <h1 class="text-3xl font-black text-navy">Blockchain Tampering Test</h1>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8">
                <p class="text-sm text-blue-900">
                    <strong>How to test the blockchain verification:</strong><br>
                    1. First, cast some votes from the student ballot<br>
                    2. Click "Tamper Vote" below to corrupt the most recent vote's hash<br>
                    3. Go to Results page and see it highlighted in RED<br>
                    4. Click "Verify Chain" button to see the tampering detected
                </p>
            </div>

            <?php if ($message): ?>
                <div class="bg-<?php echo $success ? 'green' : 'red'; ?>-50 border-l-4 border-<?php echo $success ? 'green' : 'red'; ?>-500 p-4 mb-8">
                    <p class="text-<?php echo $success ? 'green' : 'red'; ?>-900">
                        <?php echo $message; ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="?action=tamper" class="flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-xl transition-all">
                    <i class="fa-solid fa-flask-vial"></i>
                    Tamper Vote
                </a>

                <a href="?action=restore" class="flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl transition-all">
                    <i class="fa-solid fa-wrench"></i>
                    Restore Votes
                </a>

                <a href="/votechain/student/results.php" class="flex items-center justify-center gap-2 bg-royal hover:bg-navy text-white font-bold py-3 rounded-xl transition-all">
                    <i class="fa-solid fa-shield-check"></i>
                    Go to Results
                </a>
            </div>

            <hr class="my-8">

            <div class="bg-slate-50 p-6 rounded-xl">
                <h3 class="font-bold text-navy mb-4">
                    <i class="fa-solid fa-database"></i> Recent Votes in Database
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="pb-2 font-bold text-slate-600">ID</th>
                                <th class="pb-2 font-bold text-slate-600">Election</th>
                                <th class="pb-2 font-bold text-slate-600">Position</th>
                                <th class="pb-2 font-bold text-slate-600">Tx Hash</th>
                                <th class="pb-2 font-bold text-slate-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($pdo) {
                                try {
                                    $stmt = $pdo->prepare("
                                        SELECT 
                                            v.id,
                                            v.tx_hash,
                                            e.name as election_name,
                                            pos.name as position_name,
                                            v.created_at
                                        FROM votes v
                                        JOIN elections e ON v.election_id = e.id
                                        JOIN positions pos ON v.position_id = pos.id
                                        ORDER BY v.created_at DESC
                                        LIMIT 10
                                    ");
                                    $stmt->execute();
                                    $votes = $stmt->fetchAll();
                                    
                                    if (empty($votes)) {
                                        echo '<tr><td colspan="5" class="py-4 text-center text-slate-400">No votes found. Cast some votes first!</td></tr>';
                                    } else {
                                        foreach ($votes as $index => $vote) {
                                            $hashShort = substr($vote['tx_hash'], 0, 8) . '...' . substr($vote['tx_hash'], -8);
                                            $isTampered = strpos($vote['tx_hash'], 'corrupted_') === 0;
                                            ?>
                                            <tr class="border-b border-slate-100 hover:bg-slate-100 transition <?php echo $isTampered ? 'bg-red-50' : ''; ?>">
                                                <td class="py-3">#<?php echo $vote['id']; ?></td>
                                                <td class="py-3 text-xs"><?php echo htmlspecialchars($vote['election_name']); ?></td>
                                                <td class="py-3 text-xs"><?php echo htmlspecialchars($vote['position_name']); ?></td>
                                                <td class="py-3 font-mono text-xs" title="<?php echo $vote['tx_hash']; ?>">
                                                    <?php echo $hashShort; ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php if ($isTampered): ?>
                                                        <span class="inline-block px-2 py-1 bg-red-100 text-red-600 rounded text-xs font-bold">⚠ TAMPERED</span>
                                                    <?php else: ?>
                                                        <span class="inline-block px-2 py-1 bg-green-100 text-green-600 rounded text-xs font-bold">✓ VALID</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php 
                                        }
                                    }
                                } catch (Exception $e) {
                                    echo '<tr><td colspan="5" class="py-4 text-center text-red-500">Error: ' . $e->getMessage() . '</td></tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" class="py-4 text-center text-red-500">Database connection error</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <p class="text-xs text-amber-900">
                    <strong>⚠ Note:</strong> This test page is for development only. 
                    After testing, you can manually restore votes by clearing tampered entries or re-voting.
                </p>
            </div>

            <div class="mt-6 text-center">
                <a href="/votechain/student/dashboard.php" class="text-royal hover:text-navy underline font-semibold">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
