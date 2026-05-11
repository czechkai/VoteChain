<?php
require_once '../includes/config.php';
requireRole('student');
$role = 'student';
$activePage = 'results';

$database = $pdo;
if (!$database instanceof PDO) {
    die('Database connection failed');
}

// Get election ID from query or use most recent
$election_id = $_GET['election_id'] ?? null;

// If no election_id provided, get the most recent active or completed election
if (!$election_id) {
    try {
        $stmt = $database->prepare("
            SELECT id FROM elections 
            WHERE status IN ('active', 'completed')
            ORDER BY starts_at DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        $election_id = $result['id'] ?? null;
    } catch (Exception $e) {
        error_log("Error fetching election: " . $e->getMessage());
    }
}

// Get election details
$election = null;
$results = [];
$totalVotes = 0;
$ledger = [];

if ($election_id) {
    try {
        $stmt = $database->prepare("SELECT * FROM elections WHERE id = ?");
        $stmt->execute([$election_id]);
        $election = $stmt->fetch();
        
        // Get results grouped by position
        $results = getElectionResults($database, $election_id);
        
        // Calculate total votes from the verified result set only.
        $totalVotes = 0;
        foreach ($results as $resultRow) {
            $totalVotes += (int) ($resultRow['vote_count'] ?? 0);
        }
        
        // Get all votes for blockchain ledger with candidate details
        $stmt = $database->prepare("
            SELECT 
                v.id,
                v.election_id,
                v.voter_profile_id,
                v.position_id,
                v.candidate_id,
                v.tx_hash,
                v.prev_hash,
                v.created_at,
                p.first_name as candidate_first,
                p.last_name as candidate_last,
                pos.name as position_title,
                COALESCE(c.image_url, '') as image_url
            FROM votes v
            JOIN candidates c ON v.candidate_id = c.id
            JOIN profiles p ON c.profile_id = p.id
            JOIN positions pos ON v.position_id = pos.id
            WHERE v.election_id = ?
            ORDER BY v.created_at ASC
        ");
        $stmt->execute([$election_id]);
        $rows = $stmt->fetchAll();

        // Match admin behavior: pre-verify chain server-side so status is visible immediately.
        $expectedPrev = 'GENESIS';
        foreach ($rows as $r) {
            $payload = implode('|', [
                $r['election_id'],
                $r['voter_profile_id'],
                $r['position_id'],
                $r['candidate_id'],
                $expectedPrev
            ]);
            $expectedHash = hash('sha256', $payload);
            $r['tampered'] = ($r['prev_hash'] !== $expectedPrev) || ($r['tx_hash'] !== $expectedHash);
            $ledger[] = $r;
            $expectedPrev = $r['tx_hash'];
        }
    } catch (Exception $e) {
        error_log("Error fetching election data: " . $e->getMessage());
    }
}

// Group results by position
$resultsByPosition = [];
if ($results) {
    foreach ($results as $result) {
        $pos = $result['position_title'];
        if (!isset($resultsByPosition[$pos])) {
            $resultsByPosition[$pos] = [];
        }
        $resultsByPosition[$pos][] = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Results | VoteChain DOrSU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0A1F44',
                        royal: '#1E3A8A',
                        gold: '#FFC107',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        .sidebar-gradient { background: linear-gradient(180deg, #0A1F44 0%, #1E3A8A 100%); }
        .nav-item-active { background: rgba(255, 255, 255, 0.1); border-left: 4px solid #FFC107; color: white !important; }
        .glass-card { background: white; border-radius: 2rem; border: 1px solid #e2e8f0; }
        .result-bar { transition: width 1.5s cubic-bezier(0.65, 0, 0.35, 1); }
    </style>
</head>
<body class="flex min-h-screen">
    <?php $role = 'student'; $activePage = 'results'; include '../includes/sidebar.php'; ?>

    <main class="flex-1 lg:ml-72 p-4 md:p-8">
        <!-- Live Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                    <span class="text-xs font-black text-red-500 uppercase tracking-widest">Live Election Results</span>
                </div>
                <h1 class="text-3xl font-extrabold text-navy">
                    <?php echo $election ? htmlspecialchars($election['name']) : 'Election Results'; ?>
                </h1>
                <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-1">
                    Total Votes: <?php echo $totalVotes; ?>
                </p>
            </div>
            <div class="flex gap-4">
                <div class="glass-card px-6 py-3 flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Votes Cast</p>
                        <p class="text-xl font-black text-navy"><?php echo $totalVotes; ?></p>
                    </div>
                    <div class="w-px h-8 bg-slate-100"></div>
                    <i class="fa-solid fa-users text-royal text-xl"></i>
                </div>
                <button class="bg-navy text-white px-6 rounded-2xl font-bold flex items-center gap-2 hover:bg-royal transition-all">
                    <i class="fa-solid fa-download"></i>
                    <span class="hidden sm:inline">Export PDF</span>
                </button>
            </div>
        </header>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            
            <!-- Results by Position -->
            <div class="lg:col-span-8 glass-card p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black text-navy">Election Results</h3>
                    <div class="flex items-center gap-3">
                        <select class="text-xs font-bold bg-slate-50 border-none px-4 py-2 rounded-xl outline-none" id="positionFilter">
                            <option value="">All Positions</option>
                        </select>
                        <input id="ledgerSearch" type="search" placeholder="Search position or candidate" class="text-xs px-3 py-2 rounded-xl border border-slate-100 outline-none" />
                    </div>
                        <?php 
                        if ($results) {
                            $positions = [];
                            foreach ($results as $r) {
                                if (!in_array($r['position_title'], $positions)) {
                                    $positions[] = $r['position_title'];
                                    echo '<option value="' . htmlspecialchars($r['position_title']) . '">' . htmlspecialchars($r['position_title']) . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="space-y-8">
                    <?php 
                    if (empty($results)) {
                        echo '<p class="text-slate-400 text-center py-8">No results available yet.</p>';
                    } else {
                        $currentPosition = '';
                        $positionIndex = 0;
                        
                        foreach ($resultsByPosition as $position => $candidates) {
                            ?>
                            <div class="position-group">
                                <h4 class="text-lg font-bold text-navy mb-4"><?php echo htmlspecialchars($position); ?></h4>
                                <?php 
                                $rank = 1;
                                foreach ($candidates as $candidate) {
                                    $percentage = $totalVotes > 0 ? ($candidate['vote_count'] / $totalVotes * 100) : 0;
                                    $rankBgColor = $rank === 1 ? '#FFC107' : ($rank === 2 ? '#e2e8f0' : '#f1f5f9');
                                    $rankTextColor = $rank === 1 ? '#FFC107' : '#94a3b8';
                                    $barColor = $rank === 1 ? '#1E3A8A' : '#cbd5e1';
                                    ?>
                                    <div class="relative mb-6">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black flex-shrink-0 overflow-hidden bg-gradient-to-br from-blue-400 to-navy text-white" style="background-color: <?php echo $rankBgColor; ?>20; color: <?php echo $rankTextColor; ?>">
                                                    <?php if (!empty($candidate['image_url'])): ?>
                                                                        <img src="<?php echo htmlspecialchars((string) ('../' . ltrim((string) $candidate['image_url'], '/'))); ?>" alt="<?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?>" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        #<?php echo $rank; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <h5 class="font-bold text-navy">
                                                        <?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?>
                                                    </h5>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Candidate</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-black text-navy"><?php echo $candidate['vote_count']; ?></p>
                                                <p class="text-[10px] font-bold uppercase" style="color: <?php echo $rank === 1 ? '#22c55e' : '#94a3b8'; ?>">
                                                    <?php echo round($percentage, 1); ?>%
                                                </p>
                                            </div>
                                        </div>
                                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="result-bar h-full rounded-full" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $barColor; ?>"></div>
                                        </div>
                                    </div>
                                    <?php 
                                    $rank++;
                                }
                                ?>
                            </div>
                            <?php 
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Program Participation Pie -->
            <div class="lg:col-span-4 glass-card p-8 flex flex-col">
                <h3 class="text-xl font-black text-navy mb-8">Participation by Program</h3>
                <div class="flex-1 min-h-[300px] relative">
                    <canvas id="participationPie"></canvas>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-100 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Top Turnout</p>
                        <p class="font-black text-royal">IT Program</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Lowest Turnout</p>
                        <p class="font-black text-amber-500">BS Arch</p>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script>
        window.onload = function() {
            const ctx = document.getElementById('participationPie').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['IT', 'Engineering', 'Nursing', 'Education', 'Business'],
                    datasets: [{
                        data: [300, 250, 180, 220, 150],
                        backgroundColor: ['#1E3A8A', '#0A1F44', '#FFC107', '#6366f1', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: { size: 11, weight: '600' },
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '70%'
                }
            });

            // Position filter
            const positionFilter = document.getElementById('positionFilter');
            if (positionFilter) {
                positionFilter.addEventListener('change', function() {
                    const selectedPosition = this.value;
                    const groups = document.querySelectorAll('.position-group');
                    
                    groups.forEach(group => {
                        if (!selectedPosition || group.querySelector('h4').textContent.trim() === selectedPosition) {
                            group.style.display = 'block';
                        } else {
                            group.style.display = 'none';
                        }
                    });
                });
            }

            // Hash verification function
            function verifyVoteHash(voteData, expectedPrevHash) {
                // Reconstruct the hash from vote data
                const payload = [
                    voteData.election_id,
                    voteData.voter_profile_id,
                    voteData.position_id,
                    voteData.candidate_id,
                    expectedPrevHash
                ].join('|');

                // Use Web Crypto API for SHA-256
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

            // Build a stable in-memory ledger model once to avoid repeated JSON parsing.
            const voteRows = Array.from(document.querySelectorAll('.vote-row')).map((row, index, arr) => {
                const data = JSON.parse(row.getAttribute('data-vote'));
                const expectedPrev = index === 0 ? 'GENESIS' : JSON.parse(arr[index - 1].getAttribute('data-vote')).tx_hash;
                return {
                    row,
                    data,
                    expectedPrev,
                    statusBadge: row.querySelector('.status-badge')
                };
            });

            let lastVerification = null;

            function getLedgerFingerprint() {
                return voteRows.map(item => item.data.tx_hash).join('|');
            }

            function applyVerificationStyle(row, statusBadge, isValid) {
                row.classList.remove('bg-red-50', 'bg-green-50', 'border-2', 'border-red-300');

                if (isValid) {
                    row.classList.add('bg-green-50');
                    statusBadge.className = 'status-badge text-green-600 text-xs font-bold';
                    statusBadge.textContent = '✓ Valid';
                } else {
                    row.classList.add('bg-red-50');
                    statusBadge.className = 'status-badge text-red-600 text-xs font-bold';
                    statusBadge.textContent = '⚠ Tampered';
                }
            }

            // Verify all votes on page load and update display
            async function verifyAllVotes() {
                if (voteRows.length === 0) {
                    lastVerification = { isValid: true, fingerprint: '' };
                    return true;
                }

                const currentFingerprint = getLedgerFingerprint();
                if (lastVerification && lastVerification.fingerprint === currentFingerprint) {
                    return lastVerification.isValid;
                }

                let hasErrors = false;

                const chunkSize = 80;
                for (let i = 0; i < voteRows.length; i += chunkSize) {
                    const chunk = voteRows.slice(i, i + chunkSize);
                    const chunkResults = await Promise.all(
                        chunk.map(item => verifyVoteHash(item.data, item.expectedPrev))
                    );

                    chunkResults.forEach((result, offset) => {
                        const item = chunk[offset];
                        const isRowValid = result.isValid && result.storedPrev === item.expectedPrev;
                        applyVerificationStyle(item.row, item.statusBadge, isRowValid);
                        if (!isRowValid) {
                            hasErrors = true;
                        }
                    });

                    // Yield to the browser between chunks so the UI stays responsive.
                    await new Promise(resolve => requestAnimationFrame(resolve));
                }

                lastVerification = {
                    isValid: !hasErrors,
                    fingerprint: currentFingerprint
                };

                return !hasErrors;
            }

        };
    </script>
</body>
</html>