<?php
require_once __DIR__ . '/../includes/config.php';
requireRole('admin');

$database = $pdo;
if (!$database instanceof PDO) {
    die('Database connection failed');
}

$activePage = 'admin_results';

function adminResultsTableColumns($pdo, $tableName) {
    try {
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = ?");
        $stmt->execute([$tableName]);
        return array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
    } catch (Throwable $e) {
        error_log('Admin results column check error: ' . $e->getMessage());
        return [];
    }
}

function adminResultsFirstAvailableColumn(array $columns, array $preferredColumns, $fallback = null) {
    foreach ($preferredColumns as $column) {
        if (in_array($column, $columns, true)) {
            return $column;
        }
    }

    return $fallback;
}

$electionsTableColumns = adminResultsTableColumns($database, 'elections');
$votesTableColumns = adminResultsTableColumns($database, 'votes');
$electionOrderColumn = adminResultsFirstAvailableColumn($electionsTableColumns, ['start_date', 'starts_at', 'created_at'], 'created_at');
$col_end = adminResultsFirstAvailableColumn($electionsTableColumns, ['end_date', 'ends_at'], 'created_at');
$voteStatusColumn = in_array('vote_status', $votesTableColumns, true) ? 'vote_status' : null;

$elections = [];
$selectedElection = null;
$election_id = $_GET['election_id'] ?? null;
$electionTitle = 'Election Results';
$totalEligibleVoters = 0;
$isLive = false;
$deadlineTs = 0;
$totalVotes = 0;
$voteStatusCounts = [
    'valid' => 0,
    'spoiled' => 0,
    'invalid' => 0,
];
$turnoutPercent = 0;
$validVotePercent = 0;
$spoiledVotePercent = 0;
$invalidVotePercent = 0;
$results = [];
$resultsByPosition = [];
$ledger = [];

// Fetch elections
try {
    $stmt = $database->prepare('SELECT * FROM elections ORDER BY ' . $electionOrderColumn . ' DESC');
    $stmt->execute();
    $elections = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $elections = [];
}

// Determine election to show ledger and results for (query param or most recent)
if (!$election_id) {
    try {
        $stmt = $database->prepare("SELECT id FROM elections WHERE status IN ('active','completed') ORDER BY {$electionOrderColumn} DESC LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $election_id = $row['id'] ?? null;
    } catch (Exception $e) {
        $election_id = null;
    }
}

if ($election_id) {
    try {
        $stmt = $database->prepare('SELECT * FROM elections WHERE id = ? LIMIT 1');
        $stmt->execute([$election_id]);
        $selectedElection = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        $selectedElection = null;
    }
}

if ($selectedElection) {
    $electionTitle = trim((string) ($selectedElection['title'] ?? $selectedElection['name'] ?? 'Election Results')) ?: 'Election Results';
    $totalEligibleVoters = (int) ($selectedElection['total_eligible_voters'] ?? 0);
    $status = strtolower($selectedElection['status'] ?? '');
    $isLive = ($status === 'active');
    $deadlineTs = isset($selectedElection[$col_end]) ? strtotime($selectedElection[$col_end]) : 0;
}

if ($election_id) {
    $results = getElectionResults($database, $election_id);
    if (!empty($results)) {
        foreach ($results as $resultRow) {
            $positionTitle = (string) ($resultRow['position_title'] ?? 'Unknown Position');
            if (!isset($resultsByPosition[$positionTitle])) {
                $resultsByPosition[$positionTitle] = [];
            }
            $resultsByPosition[$positionTitle][] = $resultRow;
            $totalVotes += (int) ($resultRow['vote_count'] ?? 0);
        }
    }

    try {
        if ($voteStatusColumn) {
            $stmt = $database->prepare("SELECT LOWER(COALESCE({$voteStatusColumn}, 'valid')) AS vote_status, COUNT(*) AS total FROM votes WHERE election_id = ? GROUP BY LOWER(COALESCE({$voteStatusColumn}, 'valid'))");
            $stmt->execute([$election_id]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $status = (string) ($row['vote_status'] ?? 'valid');
                $voteStatusCounts[$status] = (int) ($row['total'] ?? 0);
            }
        } else {
            $voteStatusCounts['valid'] = $totalVotes;
        }
    } catch (Exception $e) {
        $voteStatusCounts['valid'] = $totalVotes;
    }

    $turnoutPercent = $totalEligibleVoters > 0 ? round(($totalVotes / $totalEligibleVoters) * 100, 1) : 0;
    $validVotePercent = $totalVotes > 0 ? round(($voteStatusCounts['valid'] / $totalVotes) * 100, 1) : 0;
    $spoiledVotePercent = $totalVotes > 0 ? round(($voteStatusCounts['spoiled'] / $totalVotes) * 100, 1) : 0;
    $invalidVotePercent = $totalVotes > 0 ? round(($voteStatusCounts['invalid'] / $totalVotes) * 100, 1) : 0;

    try {
        $stmt = $database->prepare(
            "SELECT v.id, v.election_id, v.voter_profile_id, v.position_id, v.candidate_id, v.tx_hash, v.prev_hash, v.created_at,
                    p.first_name AS candidate_first, p.last_name AS candidate_last, pos.name AS position_title
             FROM votes v
             JOIN candidates c ON v.candidate_id = c.id
             JOIN profiles p ON c.profile_id = p.id
             JOIN positions pos ON v.position_id = pos.id
             WHERE v.election_id = ?
             ORDER BY v.created_at ASC, v.id ASC"
        );
        $stmt->execute([$election_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $expectedPrev = 'GENESIS';
        foreach ($rows as $r) {
            $payload = implode('|', [
                $election_id,
                $r['voter_profile_id'],
                $r['position_id'],
                $r['candidate_id'],
                $expectedPrev
            ]);
            $expectedHash = hash('sha256', $payload);
            $tampered = ($r['prev_hash'] !== $expectedPrev) || ($r['tx_hash'] !== $expectedHash);
            $r['tampered'] = $tampered;
            $ledger[] = $r;
            $expectedPrev = $r['tx_hash'];
        }
    } catch (Exception $e) {
        $ledger = [];
    }
}

// Fetch elections
?>
<?php
require_once '../includes/config.php';
requireRole('admin');

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=election_results_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [$electionTitle . ' Summary']);
    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Election Title', $electionTitle]);
    fputcsv($output, ['Voter Turnout', number_format($turnoutPercent, 1) . '%']);
    fputcsv($output, ['Total Votes', number_format($totalVotes)]);
    fputcsv($output, ['Valid Votes', number_format($voteStatusCounts['valid'])]);
    fputcsv($output, ['Spoiled Votes', number_format($voteStatusCounts['spoiled'])]);
    fputcsv($output, ['Invalid Votes', number_format($voteStatusCounts['invalid'])]);
    fputcsv($output, []);
    
    fputcsv($output, ['Position', 'Candidate', 'Votes', 'Percentage']);
    foreach ($resultsByPosition as $positionTitle => $candidates) {
        $positionVotes = array_sum(array_map(static function ($candidateRow) {
            return (int) ($candidateRow['vote_count'] ?? 0);
        }, $candidates));

        foreach ($candidates as $candidate) {
            $candidateFirstName = trim((string) ($candidate['first_name'] ?? ''));
            $candidateLastName = trim((string) ($candidate['last_name'] ?? ''));
            $candidateFullName = trim($candidateFirstName . ' ' . $candidateLastName) ?: 'Unknown Candidate';
            $candidateVotes = (int) ($candidate['vote_count'] ?? 0);
            $candidatePercent = $positionVotes > 0 ? round(($candidateVotes / $positionVotes) * 100, 1) : 0;
            fputcsv($output, [$positionTitle, $candidateFullName, $candidateVotes, $candidatePercent . '%']);
        }
    }
    
    fclose($output);
    exit;
}

$role = 'admin';
$activePage = 'results';
$pageTitle = 'Live Results';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: { 
                    colors: { navy: '#0A1F44', royal: '#1E3A8A', gold: '#FFC107' } 
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }

        @media print {
            @page { margin: 0; }
            body { background: white !important; padding: 2cm; }
            aside, header, footer, .export-buttons { display: none !important; }
            .lg\:ml-72 { margin-left: 0 !important; }
            main { padding: 0 !important; }
            
            /* Professional Report Styling */
            .print-only { display: block !important; }
            .no-print { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            
            /* Card adjustments for print */
            .bg-white { border: 1px solid #e2e8f0 !important; box-shadow: none !important; }
            [data-admin-search-item] { break-inside: avoid; margin-bottom: 2rem; }
            .bg-gradient-to-r { border: 1px solid #e2e8f0 !important; }
        }

        /* Hide print elements on web view */
        .print-only { display: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <?php include '../includes/sidebar.php'; ?>

    <div class="lg:ml-72 flex flex-col min-w-0 min-h-screen">
        <?php include '../includes/header.php'; ?>

        <main class="p-8 flex-1">
            <!-- Print Header (Professional Branding) -->
            <div class="print-only text-center mb-10 pb-6 border-b-2 border-navy">
                <h1 class="text-2xl font-black text-navy uppercase tracking-tighter">Davao Oriental State University</h1>
                <p class="text-sm font-bold text-slate-600 uppercase tracking-widest">Commission on Elections (COMELEC)</p>
                <div class="mt-6">
                    <h2 class="text-xl font-extrabold text-navy uppercase">Official Election Results Summary</h2>
                    <p class="text-xs text-slate-500 mt-1 italic">Generated on <?php echo date('F j, Y, g:i a'); ?></p>
                </div>
            </div>

            <!-- Live Status -->
            <div class="mb-8 p-6 bg-gradient-to-r <?php echo $isLive ? 'from-emerald-50 to-teal-50 border-emerald-200' : 'from-slate-50 to-slate-100 border-slate-200'; ?> rounded-[2rem] border flex items-center justify-between no-print">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 <?php echo $isLive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'; ?> rounded-full flex items-center justify-center text-white">
                        <i class="fa-solid <?php echo $isLive ? 'fa-circle-dot' : 'fa-check-double'; ?>"></i>
                    </div>
                    <div>
                        <p class="font-black <?php echo $isLive ? 'text-emerald-700' : 'text-slate-700'; ?> text-lg"><?php echo $isLive ? 'ELECTION LIVE' : 'ELECTION COMPLETED'; ?></p>
                        <p class="text-sm <?php echo $isLive ? 'text-emerald-600' : 'text-slate-500'; ?>"><?php echo $isLive ? 'Votes are being counted in real-time' : 'Final results are now available'; ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <?php if ($isLive && $deadlineTs > time()): ?>
                        <p id="liveCountdown" class="text-3xl font-black text-emerald-700" data-expire="<?php echo $deadlineTs; ?>">--:--:--</p>
                        <p class="text-sm text-emerald-600">Time remaining</p>
                    <?php else: ?>
                        <p class="text-3xl font-black text-slate-700">FINISHED</p>
                        <p class="text-sm text-slate-500">Voting closed</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Results Overview -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Voter Turnout -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xl font-black text-navy mb-6">Voter Turnout</h3>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-5xl font-black text-navy"><?php echo number_format($turnoutPercent, 1); ?>%</p>
                            <p class="text-sm text-slate-600 mt-2">
                                <?php echo number_format($totalVotes); ?> votes cast
                                <?php if ($totalEligibleVoters > 0): ?>
                                    out of <?php echo number_format($totalEligibleVoters); ?> eligible voters
                                <?php else: ?>
                                    for the current election
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="w-24 h-24">
                                <div class="relative w-full h-full">
                                    <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#FFC107" stroke-width="8" stroke-dasharray="245.04" stroke-dashoffset="<?php echo number_format(245.04 - (245.04 * max(0, min(100, $turnoutPercent)) / 100), 2, '.', ''); ?>" stroke-linecap="round"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="font-black text-sm text-navy"><?php echo number_format($turnoutPercent, 1); ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Votes -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xl font-black text-navy mb-6">Vote Distribution</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-navy">Valid Votes</span>
                                <span class="font-black text-navy"><?php echo number_format($voteStatusCounts['valid']); ?></span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full" style="width: <?php echo number_format($validVotePercent, 1); ?>%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1"><?php echo number_format($validVotePercent, 1); ?>% of total votes</p>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-navy">Spoiled Votes</span>
                                <span class="font-black text-navy"><?php echo number_format($voteStatusCounts['spoiled']); ?></span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-red-500 h-full" style="width: <?php echo number_format($spoiledVotePercent, 1); ?>%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1"><?php echo number_format($spoiledVotePercent, 1); ?>% of total votes</p>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-navy">Invalid Votes</span>
                                <span class="font-black text-navy"><?php echo number_format($voteStatusCounts['invalid']); ?></span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-yellow-500 h-full" style="width: <?php echo number_format($invalidVotePercent, 1); ?>%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1"><?php echo number_format($invalidVotePercent, 1); ?>% of total votes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results by Position -->
            <div class="space-y-8">
                <?php if (empty($resultsByPosition)): ?>
                    <div data-admin-search-item class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                        <p class="text-slate-400 text-center py-8">No results available yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($resultsByPosition as $positionTitle => $candidates): ?>
                        <?php $positionVotes = array_sum(array_map(static function ($candidateRow) { return (int) ($candidateRow['vote_count'] ?? 0); }, $candidates)); ?>
                        <div data-admin-search-item class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                            <h3 class="text-xl font-black text-navy mb-6"><?php echo htmlspecialchars($positionTitle); ?> Results</h3>
                            <div class="space-y-6">
                                <?php foreach ($candidates as $candidate): ?>
                                    <?php
                                        $candidateVotes = (int) ($candidate['vote_count'] ?? 0);
                                        $candidatePercent = $positionVotes > 0 ? ($candidateVotes / $positionVotes * 100) : 0;
                                        $candidateFirstName = trim((string) ($candidate['first_name'] ?? ''));
                                        $candidateLastName = trim((string) ($candidate['last_name'] ?? ''));
                                        $candidateFullName = trim($candidateFirstName . ' ' . $candidateLastName) ?: 'Unknown Candidate';
                                        $candidateInitials = getCandidateInitials($candidateFirstName, $candidateLastName, 'C');
                                        $candidateImageUrl = trim((string) ($candidate['image_url'] ?? ''));
                                    ?>
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-4 flex-1 min-w-0">
                                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white font-bold overflow-hidden flex items-center justify-center flex-shrink-0">
                                                <?php if ($candidateImageUrl !== ''): ?>
                                                    <img src="<?php echo htmlspecialchars('../' . ltrim($candidateImageUrl, '/')); ?>" alt="<?php echo htmlspecialchars($candidateFullName); ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($candidateInitials); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-navy truncate"><?php echo htmlspecialchars($candidateFullName); ?></p>
                                                <p class="text-xs text-slate-500 uppercase">Candidate</p>
                                            </div>
                                        </div>
                                        <div class="flex-1 px-3">
                                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                                <div class="bg-gradient-to-r from-royal to-gold h-full" style="width: <?php echo number_format($candidatePercent, 1); ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="text-right ml-2 min-w-[88px]">
                                            <p class="font-black text-navy text-lg"><?php echo number_format($candidateVotes); ?></p>
                                            <p class="text-xs text-slate-500"><?php echo number_format($candidatePercent, 1); ?>%</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Certification Section (Print Only) -->
            <div class="print-only mt-16">
                <p class="text-sm text-slate-600 mb-12">I hereby certify that the above results are true and accurate representations of the votes cast in the electronic ledger as of the date specified above.</p>
                <div class="grid grid-cols-2 gap-20">
                    <div class="text-center">
                        <div class="border-b border-navy mb-2 w-full h-10"></div>
                        <p class="text-xs font-black text-navy uppercase">COMELEC Commissioner</p>
                    </div>
                    <div class="text-center">
                        <div class="border-b border-navy mb-2 w-full h-10"></div>
                        <p class="text-xs font-black text-navy uppercase">University Registrar</p>
                    </div>
                </div>
            </div>

            <!-- Admin Ledger -->
            <div class="glass-card p-8 mt-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-black text-navy">Blockchain Ledger (Admin)</h3>
                    <div class="flex items-center gap-3">
                        <button id="verifyChainBtn" class="text-xs font-bold text-royal flex items-center gap-2 px-3 py-2 rounded-md bg-blue-50 hover:bg-blue-100">
                            <i class="fa-solid fa-shield-check"></i> Verify Chain
                        </button>
                        <button id="restoreChainBtn" class="text-xs font-bold text-white flex items-center gap-2 px-3 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700">
                            <i class="fa-solid fa-rotate-right"></i> Restore Chain
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase">#</th>
                                <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase">Prev Hash</th>
                                <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase">Tx Hash</th>
                                <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase">Position</th>
                                <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase">Candidate</th>
                                <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase">Timestamp</th>
                                <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ledger)): ?>
                                <tr><td colspan="7" class="py-4 text-center text-slate-400">No ledger entries for this election.</td></tr>
                            <?php else: ?>
                                <?php foreach ($ledger as $i => $row): ?>
                                    <?php
                                        $voteData = json_encode([
                                            'index' => $i + 1,
                                            'id' => $row['id'],
                                            'election_id' => $row['election_id'],
                                            'voter_profile_id' => $row['voter_profile_id'],
                                            'position_id' => $row['position_id'],
                                            'candidate_id' => $row['candidate_id'],
                                            'tx_hash' => $row['tx_hash'],
                                            'prev_hash' => $row['prev_hash']
                                        ]);
                                    ?>
                                    <tr class="border-b vote-row <?php echo $row['tampered'] ? 'bg-red-50' : 'bg-green-50'; ?>" data-vote='<?php echo htmlspecialchars($voteData); ?>'>
                                        <td class="py-3"><?php echo $i+1; ?></td>
                                        <td class="py-3 font-mono text-xs" title="<?php echo $row['prev_hash']; ?>"><?php echo substr($row['prev_hash'],0,8) . '...' . substr($row['prev_hash'],-8); ?></td>
                                        <td class="py-3 font-mono text-xs" title="<?php echo $row['tx_hash']; ?>"><?php echo substr($row['tx_hash'],0,8) . '...' . substr($row['tx_hash'],-8); ?></td>
                                        <td class="py-3"><?php echo htmlspecialchars($row['position_title']); ?></td>
                                        <td class="py-3"><?php echo htmlspecialchars($row['candidate_first'] . ' ' . $row['candidate_last']); ?></td>
                                        <td class="py-3 text-xs"><?php echo $row['created_at']; ?></td>
                                        <td class="py-3 text-xs font-bold"><span class="status-badge <?php echo $row['tampered'] ? 'text-red-600' : 'text-green-600'; ?>"><?php echo $row['tampered'] ? '⚠ Tampered' : '✓ Valid'; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Verification Modal -->
            <div id="verificationModal" class="fixed inset-0 bg-slate-900/15 backdrop-blur-sm hidden flex items-center justify-center z-50">
                <div class="glass-card bg-white/95 p-8 w-full max-w-md rounded-3xl shadow-2xl border border-slate-100">
                    <div class="flex items-center justify-center mb-4">
                        <div id="modalIcon" class="text-5xl"></div>
                    </div>
                    <h2 id="modalTitle" class="text-2xl font-black text-navy text-center mb-4">Blockchain Verification</h2>
                    <p id="modalMessage" class="text-center text-slate-600 mb-6"></p>
                    <div id="modalDetails" class="bg-slate-50 rounded-2xl p-4 mb-6 hidden">
                        <p id="detailsText" class="text-xs text-slate-600 font-mono"></p>
                    </div>
                    <button id="closeModalBtn" class="w-full bg-navy text-white font-bold py-3 rounded-2xl hover:bg-royal transition-all">Close</button>
                </div>
            </div>

            <!-- Restore Modal -->
            <div id="restoreModal" class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm hidden flex items-center justify-center z-50 no-print">
                <div class="glass-card bg-white/95 p-8 w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 id="restoreModalTitle" class="text-2xl font-black text-navy">Restore Chain</h2>
                            <p id="restoreModalMessage" class="text-sm text-slate-600 mt-1">This will recalculate the selected election chain from the stored vote records.</p>
                        </div>
                        <button id="closeRestoreModalBtn" class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition" aria-label="Close restore dialog">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-5">
                        <p class="text-sm font-semibold text-amber-800">Warning: restoring will rewrite the chain hashes for this election.</p>
                    </div>

                    <div id="restoreForm">
                        <div class="mb-5">
                            <label for="restoreConfirmInput" class="block text-sm font-bold text-slate-700 mb-2">Type RESTORE to continue</label>
                            <input id="restoreConfirmInput" type="text" autocomplete="off" placeholder="RESTORE" class="w-full px-4 py-3 rounded-2xl border border-slate-200 outline-none focus:border-royal focus:ring-2 focus:ring-blue-100">
                        </div>

                        <div id="restoreStatus" class="hidden mb-5 rounded-2xl px-4 py-3 text-sm font-semibold"></div>

                        <div class="flex gap-3">
                            <button id="cancelRestoreBtn" class="flex-1 px-4 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition">Cancel</button>
                            <button id="confirmRestoreBtn" class="flex-1 px-4 py-3 rounded-2xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition">Restore Chain</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Results -->
            <div class="flex gap-4 justify-center mt-12 export-buttons">
                <button onclick="window.print()" class="px-8 py-3 bg-slate-100 text-navy rounded-xl font-bold hover:bg-slate-200 transition">
                    <i class="fa-solid fa-download mr-2"></i>Download PDF
                </button>
                <a href="?export=csv" class="px-8 py-3 bg-navy text-white rounded-xl font-bold hover:bg-royal transition inline-flex items-center">
                    <i class="fa-solid fa-table mr-2"></i>Export CSV
                </a>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        function verifyVoteHash(voteData, expectedPrevHash) {
            const payload = [
                voteData.election_id,
                voteData.voter_profile_id,
                voteData.position_id,
                voteData.candidate_id,
                expectedPrevHash
            ].join('|');

            return crypto.subtle.digest('SHA-256', new TextEncoder().encode(payload)).then(hashBuffer => {
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                return {
                    calculated: hashHex,
                    stored: voteData.tx_hash,
                    isValid: hashHex === voteData.tx_hash,
                    expectedPrev: expectedPrevHash,
                    storedPrev: voteData.prev_hash
                };
            });
        }

        async function verifyAllVotes() {
            const rows = document.querySelectorAll('.vote-row');
            let expectedPrevHash = 'GENESIS';
            let hasErrors = false;
            let brokenVoteIndex = null;
            let brokenVoteId = null;

            for (const row of rows) {
                const voteData = JSON.parse(row.getAttribute('data-vote'));
                const statusBadge = row.querySelector('.status-badge');

                try {
                    const result = await verifyVoteHash(voteData, expectedPrevHash);

                    if (!result.isValid || result.storedPrev !== expectedPrevHash) {
                        row.classList.add('bg-red-50', 'border-2', 'border-red-300');
                        row.classList.remove('bg-green-50');
                        statusBadge.className = 'status-badge text-red-600';
                        statusBadge.textContent = '⚠ Tampered';
                        hasErrors = true;

                        if (brokenVoteIndex === null) {
                            brokenVoteIndex = voteData.index;
                            brokenVoteId = voteData.id;
                        }
                    } else {
                        row.classList.add('bg-green-50');
                        row.classList.remove('bg-red-50', 'border-2', 'border-red-300');
                        statusBadge.className = 'status-badge text-green-600';
                        statusBadge.textContent = '✓ Valid';
                    }

                    expectedPrevHash = result.stored;
                } catch (error) {
                    statusBadge.className = 'status-badge text-yellow-600';
                    statusBadge.textContent = 'Error';
                }
            }

            return {
                isValid: !hasErrors,
                brokenVoteIndex,
                brokenVoteId
            };
        }

        function showVerificationModal(result, customMessage = null) {
            const modal = document.getElementById('verificationModal');
            const icon = document.getElementById('modalIcon');
            const title = document.getElementById('modalTitle');
            const message = document.getElementById('modalMessage');
            const details = document.getElementById('modalDetails');
            const detailsText = document.getElementById('detailsText');

            const isValid = typeof result === 'object' ? result?.isValid : result;
            const brokenVoteIndex = typeof result === 'object' ? result?.brokenVoteIndex : null;
            const brokenVoteId = typeof result === 'object' ? result?.brokenVoteId : null;

            if (isValid === true) {
                icon.textContent = '✓';
                icon.className = 'text-5xl text-green-500';
                title.textContent = 'Blockchain Valid';
                title.className = 'text-2xl font-black text-green-600 text-center mb-4';
                message.textContent = 'All vote hashes are intact. Every vote in this election is counted.';
                details.classList.add('hidden');
            } else if (isValid === false) {
                icon.textContent = '⚠';
                icon.className = 'text-5xl text-red-500';
                title.textContent = 'Tampering Detected';
                title.className = 'text-2xl font-black text-red-600 text-center mb-4';
                message.textContent = brokenVoteIndex
                    ? 'Chain broken at vote #' + brokenVoteIndex + '. Votes after this point are not counted.'
                    : 'Blockchain verification found tampering! Votes after the first broken link are not counted.';
                details.classList.remove('hidden');
                detailsText.textContent = brokenVoteIndex
                    ? 'Vote #' + brokenVoteIndex + (brokenVoteId ? ' (ID ' + brokenVoteId + ')' : '') + ' is the first invalid link. Only votes before it remain valid.'
                    : 'Red rows in the ledger indicate votes with hash mismatches. Only votes before the first red row remain valid.';
            } else {
                icon.textContent = '✕';
                icon.className = 'text-5xl text-orange-500';
                title.textContent = 'Verification Error';
                title.className = 'text-2xl font-black text-orange-600 text-center mb-4';
                message.textContent = customMessage || 'An error occurred during verification.';
                details.classList.add('hidden');
            }

            modal.classList.remove('hidden');
        }

        document.getElementById('verifyChainBtn')?.addEventListener('click', async function() {
            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';

            try {
                const verification = await verifyAllVotes();
                showVerificationModal(verification);
            } catch (error) {
                showVerificationModal(false, 'Error verifying blockchain: ' + error.message);
            } finally {
                this.disabled = false;
                this.innerHTML = originalHTML;
            }
        });

        document.getElementById('closeModalBtn')?.addEventListener('click', function() {
            document.getElementById('verificationModal')?.classList.add('hidden');
        });

        document.getElementById('verificationModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>
    <script>
        (function(){
            const restoreBtn = document.getElementById('restoreChainBtn');
            const restoreModal = document.getElementById('restoreModal');
            const restoreConfirmInput = document.getElementById('restoreConfirmInput');
            const restoreStatus = document.getElementById('restoreStatus');
            const restoreForm = document.getElementById('restoreForm');
            const closeRestoreModalBtn = document.getElementById('closeRestoreModalBtn');
            const cancelRestoreBtn = document.getElementById('cancelRestoreBtn');
            const confirmRestoreBtn = document.getElementById('confirmRestoreBtn');
            const electionId = <?php echo json_encode($election_id); ?>;

            const openRestoreModal = () => {
                if (!restoreModal) {
                    return;
                }

                restoreModal.classList.remove('hidden');

                if (restoreConfirmInput) {
                    restoreConfirmInput.value = '';
                    setTimeout(() => restoreConfirmInput.focus(), 0);
                }

                if (restoreStatus) {
                    restoreStatus.className = 'hidden mb-5 rounded-2xl px-4 py-3 text-sm font-semibold';
                    restoreStatus.textContent = '';
                }

                if (restoreForm) {
                    restoreForm.classList.remove('hidden');
                }
            };

            const closeRestoreModal = () => {
                restoreModal?.classList.add('hidden');
            };

            const showRestoreStatus = (kind, message) => {
                if (!restoreStatus) {
                    return;
                }

                restoreStatus.className = kind === 'success'
                    ? 'mb-5 rounded-2xl px-4 py-3 text-sm font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'mb-5 rounded-2xl px-4 py-3 text-sm font-semibold border bg-rose-50 text-rose-700 border-rose-200';
                restoreStatus.textContent = message;
                restoreStatus.classList.remove('hidden');
            };

            if (restoreBtn) {
                restoreBtn.addEventListener('click', openRestoreModal);
            }

            closeRestoreModalBtn?.addEventListener('click', closeRestoreModal);
            cancelRestoreBtn?.addEventListener('click', closeRestoreModal);

            restoreModal?.addEventListener('click', (event) => {
                if (event.target === restoreModal) {
                    closeRestoreModal();
                }
            });

            restoreConfirmInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    confirmRestoreBtn?.click();
                }
            });

            confirmRestoreBtn?.addEventListener('click', async () => {
                if (!electionId) {
                    showRestoreStatus('error', 'No election is loaded for restore.');
                    return;
                }

                if (!restoreConfirmInput || restoreConfirmInput.value.trim() !== 'RESTORE') {
                    showRestoreStatus('error', 'Type RESTORE in the field to continue.');
                    restoreConfirmInput?.focus();
                    return;
                }

                restoreBtn.disabled = true;
                restoreBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Restoring...';
                confirmRestoreBtn.disabled = true;
                cancelRestoreBtn.disabled = true;
                showRestoreStatus('success', 'Restoring the chain now. Please wait.');

                try {
                    const res = await fetch('../includes/restore_chain.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ election_id: electionId, confirm: 'RESTORE' })
                    });
                    const rawResponse = await res.text();
                    let data = {};

                    try {
                        data = rawResponse ? JSON.parse(rawResponse) : {};
                    } catch (parseError) {
                        throw new Error(rawResponse || parseError.message);
                    }

                    if (!res.ok && !data.message) {
                        throw new Error('Restore request failed with HTTP ' + res.status);
                    }

                    if (data.success) {
                        showRestoreStatus('success', 'Restore complete. ' + (data.repaired_count ?? 0) + ' vote(s) were recalculated.');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showRestoreStatus('error', 'Restore failed: ' + (data.message || data.error || JSON.stringify(data)));
                    }
                } catch (err) {
                    showRestoreStatus('error', 'Restore error: ' + err.message);
                } finally {
                    restoreBtn.disabled = false;
                    restoreBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Restore Chain';
                    confirmRestoreBtn.disabled = false;
                    cancelRestoreBtn.disabled = false;
                }
            });
        })();
    </script>
</body>
</html>
