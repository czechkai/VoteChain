<?php
require_once __DIR__ . '/../includes/config.php';
requireRole('admin');

$election_id = $_GET['election_id'] ?? null;

// Fetch votes (optionally for an election)
try {
    if ($election_id) {
        $stmt = $pdo->prepare(
            "SELECT v.*, c.id as candidate_id, p.first_name, p.last_name, pos.name as position_title, e.name as election_name
             FROM votes v
             JOIN candidates c ON v.candidate_id = c.id
             JOIN profiles p ON c.profile_id = p.id
             JOIN positions pos ON v.position_id = pos.id
             JOIN elections e ON v.election_id = e.id
             WHERE v.election_id = ?
             ORDER BY v.created_at DESC"
        );
        $stmt->execute([$election_id]);
    } else {
        $stmt = $pdo->prepare(
            "SELECT v.*, c.id as candidate_id, p.first_name, p.last_name, pos.name as position_title, e.name as election_name
             FROM votes v
             JOIN candidates c ON v.candidate_id = c.id
             JOIN profiles p ON c.profile_id = p.id
             JOIN positions pos ON v.position_id = pos.id
             JOIN elections e ON v.election_id = e.id
             ORDER BY v.created_at DESC LIMIT 200"
        );
        $stmt->execute();
    }
    $votes = $stmt->fetchAll();
} catch (Exception $e) {
    $votes = [];
}

function logAdminAction($msg) {
    $logfile = __DIR__ . '/../logs/admin_actions.log';
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . ($_SESSION['profile_id'] ?? 'unknown') . ' - ' . $msg . "\n";
    file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Votes | VoteChain</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
    <h1 class="text-2xl font-bold mb-4">Admin - Vote Management</h1>

    <div class="mb-4">
        <a href="results.php" class="px-3 py-2 bg-slate-100 rounded">Back to Results</a>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow p-4">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b">
                    <th class="py-2">ID</th>
                    <th class="py-2">Election</th>
                    <th class="py-2">Position</th>
                    <th class="py-2">Candidate</th>
                    <th class="py-2">Tx Hash</th>
                    <th class="py-2">Prev Hash</th>
                    <th class="py-2">Timestamp</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($votes)): ?>
                    <tr><td colspan="8" class="py-4 text-center text-slate-400">No votes found.</td></tr>
                <?php else: ?>
                    <?php foreach ($votes as $v): ?>
                        <tr class="border-b hover:bg-slate-50">
                            <td class="py-2">#<?php echo $v['id']; ?></td>
                            <td class="py-2 text-xs"><?php echo htmlspecialchars($v['election_name']); ?></td>
                            <td class="py-2 text-xs"><?php echo htmlspecialchars($v['position_title']); ?></td>
                            <td class="py-2 text-xs"><?php echo htmlspecialchars($v['first_name'] . ' ' . $v['last_name']); ?></td>
                            <td class="py-2 font-mono text-xs" title="<?php echo $v['tx_hash']; ?>"><?php echo substr($v['tx_hash'],0,8) . '...' . substr($v['tx_hash'],-8); ?></td>
                            <td class="py-2 font-mono text-xs" title="<?php echo $v['prev_hash']; ?>"><?php echo substr($v['prev_hash'],0,8) . '...' . substr($v['prev_hash'],-8); ?></td>
                            <td class="py-2 text-xs"><?php echo $v['created_at']; ?></td>
                            <td class="py-2">
                                <form method="post" action="flag_vote.php" style="display:inline">
                                    <input type="hidden" name="vote_id" value="<?php echo $v['id']; ?>">
                                    <input type="hidden" name="note" value="Manual flag by admin">
                                    <button class="px-2 py-1 bg-amber-200 rounded text-xs">Flag</button>
                                </form>
                                <form method="post" action="delete_vote.php" style="display:inline" onsubmit="return confirm('Delete this vote? This action cannot be undone.')">
                                    <input type="hidden" name="vote_id" value="<?php echo $v['id']; ?>">
                                    <button class="px-2 py-1 bg-red-500 text-white rounded text-xs ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6 bg-slate-50 p-4 rounded">
        <h3 class="font-bold">Admin Audit Log (recent)</h3>
        <pre class="text-xs mt-3 p-3 bg-white rounded border" style="max-height:200px; overflow:auto"><?php
            $logfile = __DIR__ . '/../logs/admin_actions.log';
            if (file_exists($logfile)) {
                echo htmlspecialchars(implode('', array_slice(explode("\n", file_get_contents($logfile)), -50)));
            } else {
                echo 'No admin actions logged yet.';
            }
        ?></pre>
    </div>

</body>
</html>
