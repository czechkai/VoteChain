<?php
require_once __DIR__ . '/../includes/config.php';
requireRole('admin');

$activePage = 'admin_results';

// Fetch elections
try {
    $stmt = $pdo->prepare("SELECT * FROM elections ORDER BY starts_at DESC");
    $stmt->execute();
    $elections = $stmt->fetchAll();
} catch (Exception $e) {
    $elections = [];
}

// Determine election to show ledger for (query param or most recent)
$election_id = $_GET['election_id'] ?? null;
if (!$election_id) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM elections WHERE status IN ('active','completed') ORDER BY starts_at DESC LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $election_id = $row['id'] ?? null;
    } catch (Exception $e) {
        $election_id = null;
    }
}

// Fetch ledger votes for selected election and verify per-vote integrity
$ledger = [];
if ($election_id) {
    try {
        $stmt = $pdo->prepare(
            "SELECT v.id, v.election_id, v.voter_profile_id, v.position_id, v.candidate_id, v.tx_hash, v.prev_hash, v.created_at,
                    p.first_name AS candidate_first, p.last_name AS candidate_last, pos.name AS position_title
             FROM votes v
             JOIN candidates c ON v.candidate_id = c.id
             JOIN profiles p ON c.profile_id = p.id
             JOIN positions pos ON v.position_id = pos.id
             WHERE v.election_id = ?
             ORDER BY v.created_at ASC"
        );
        $stmt->execute([$election_id]);
        $rows = $stmt->fetchAll();

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

?>
<?php
require_once '../includes/config.php';
requireRole('admin');

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=election_results_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['Election Results Summary']);
    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Voter Turnout', '68.4%']);
    fputcsv($output, ['Valid Votes', '2847']);
    fputcsv($output, ['Spoiled Votes', '32']);
    fputcsv($output, ['Invalid Votes', '18']);
    fputcsv($output, []);
    
    fputcsv($output, ['Position', 'Candidate', 'Course/Year', 'Votes', 'Percentage']);
    fputcsv($output, ['USC President', 'James Blanco', 'BS IT - 4A', '1194', '42.0%']);
    fputcsv($output, ['USC President', 'Sarah Rodriguez', 'BSN - 4B', '1078', '38.0%']);
    fputcsv($output, ['USC President', 'Maria Cruz', 'BSED - 3C', '575', '20.0%']);
    fputcsv($output, ['Vice President', 'John Park', 'BSCS - 3A', '1563', '55.0%']);
    fputcsv($output, ['Vice President', 'Angela Lopez', 'BSBA - 4B', '1284', '45.0%']);
    
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
            <div class="mb-8 p-6 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-[2rem] border border-emerald-200 flex items-center justify-between no-print">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center text-white animate-pulse">
                        <i class="fa-solid fa-circle-dot"></i>
                    </div>
                    <div>
                        <p class="font-black text-emerald-700 text-lg">ELECTION LIVE</p>
                        <p class="text-sm text-emerald-600">Votes are being counted in real-time</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-black text-emerald-700">12:34:56</p>
                    <p class="text-sm text-emerald-600">Time remaining</p>
                </div>
            </div>

            <!-- Results Overview -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Voter Turnout -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xl font-black text-navy mb-6">Voter Turnout</h3>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-5xl font-black text-navy">68.4%</p>
                            <p class="text-sm text-slate-600 mt-2">2,847 votes cast out of 4,162 registered voters</p>
                        </div>
                        <div class="text-right">
                            <div class="w-24 h-24">
                                <div class="relative w-full h-full">
                                    <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#FFC107" stroke-width="8" stroke-dasharray="245.04" stroke-dashoffset="79.27" stroke-linecap="round"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="font-black text-sm text-navy">68.4%</span>
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
                                <span class="font-black text-navy">2,847</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full w-[98%]"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-navy">Spoiled Votes</span>
                                <span class="font-black text-navy">32</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-red-500 h-full w-[1%]"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-navy">Invalid Votes</span>
                                <span class="font-black text-navy">18</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-yellow-500 h-full w-[0.6%]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results by Position -->
            <div class="space-y-8">
                <!-- USC President -->
                <div data-admin-search-item class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xl font-black text-navy mb-6">USC President Results</h3>
                    <div class="space-y-6">
                        <!-- Candidate 1 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">JB</div>
                                <div>
                                    <p class="font-bold text-navy">James Blanco</p>
                                    <p class="text-xs text-slate-500">BS IT - 4A</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-full" style="width: 42%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">1,194</p>
                                <p class="text-xs text-slate-500">42.0%</p>
                            </div>
                        </div>

                        <!-- Candidate 2 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-bold">SR</div>
                                <div>
                                    <p class="font-bold text-navy">Sarah Rodriguez</p>
                                    <p class="text-xs text-slate-500">BSN - 4B</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-purple-400 to-purple-600 h-full" style="width: 38%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">1,078</p>
                                <p class="text-xs text-slate-500">38.0%</p>
                            </div>
                        </div>

                        <!-- Candidate 3 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold">MC</div>
                                <div>
                                    <p class="font-bold text-navy">Maria Cruz</p>
                                    <p class="text-xs text-slate-500">BSED - 3C</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-full" style="width: 20%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">575</p>
                                <p class="text-xs text-slate-500">20.0%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vice President -->
                <div data-admin-search-item class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xl font-black text-navy mb-6">Vice President Results</h3>
                    <div class="space-y-6">
                        <!-- Candidate 1 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white font-bold">JP</div>
                                <div>
                                    <p class="font-bold text-navy">John Park</p>
                                    <p class="text-xs text-slate-500">BSCS - 3A</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-teal-400 to-teal-600 h-full" style="width: 55%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">1,563</p>
                                <p class="text-xs text-slate-500">55.0%</p>
                            </div>
                        </div>

                        <!-- Candidate 2 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold">AL</div>
                                <div>
                                    <p class="font-bold text-navy">Angela Lopez</p>
                                    <p class="text-xs text-slate-500">BSBA - 4B</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-orange-400 to-orange-600 h-full" style="width: 45%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">1,284</p>
                                <p class="text-xs text-slate-500">45.0%</p>
                            </div>
                        </div>
                    </div>
                </div>
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

            return !hasErrors;
        }

        function showVerificationModal(isValid, customMessage = null) {
            const modal = document.getElementById('verificationModal');
            const icon = document.getElementById('modalIcon');
            const title = document.getElementById('modalTitle');
            const message = document.getElementById('modalMessage');
            const details = document.getElementById('modalDetails');
            const detailsText = document.getElementById('detailsText');

            if (isValid === true) {
                icon.textContent = '✓';
                icon.className = 'text-5xl text-green-500';
                title.textContent = 'Blockchain Valid';
                title.className = 'text-2xl font-black text-green-600 text-center mb-4';
                message.textContent = 'All vote hashes are intact and the blockchain chain is valid.';
                details.classList.add('hidden');
            } else if (isValid === false) {
                icon.textContent = '⚠';
                icon.className = 'text-5xl text-red-500';
                title.textContent = 'Tampering Detected';
                title.className = 'text-2xl font-black text-red-600 text-center mb-4';
                message.textContent = 'Blockchain verification found tampering! Some votes have been modified or the chain is broken.';
                details.classList.remove('hidden');
                detailsText.textContent = 'Red rows in the ledger indicate votes with hash mismatches.';
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
                const isValid = await verifyAllVotes();
                showVerificationModal(isValid);
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
</body>
</html>
