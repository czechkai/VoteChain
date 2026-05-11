<?php
require_once '../includes/config.php';

/** @var PDO $pdo */
if (!$pdo) {
    die('Database connection failed. Please check your configuration.');
}

requireRole('admin');
$role = 'admin';
$activePage = 'dashboard';
$pageTitle = 'Admin Dashboard';

$dashboardStats = [
    'total_candidates' => 0,
    'filed_applications' => 0,
    'pending_review' => 0,
    'approved' => 0,
];

$dashboardCandidates = [];
$dashboardCandidatesByElection = [];
$dashboardCandidateDocuments = [];

function dashboardTableExists($pdo, $tableName) {
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = CURRENT_SCHEMA() AND table_name = ? LIMIT 1");
        $stmt->execute([$tableName]);
        return $stmt->fetchColumn() !== false;
    } catch (Throwable $e) {
        error_log('Dashboard table check error: ' . $e->getMessage());
        return false;
    }
}

function dashboardTableColumns($pdo, $tableName) {
    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = ?");
        $stmt->execute([$tableName]);
        return array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
    } catch (Throwable $e) {
        error_log('Dashboard column check error: ' . $e->getMessage());
        return [];
    }
}

$candidateTableExists = dashboardTableExists($pdo, 'candidates');
$candidateTableColumns = dashboardTableColumns($pdo, 'candidates');
$candidateStatusColumn = in_array('status', $candidateTableColumns, true)
    ? 'status'
    : (in_array('filing_status', $candidateTableColumns, true) ? 'filing_status' : null);
$candidateIdColumn = in_array('profile_id', $candidateTableColumns, true)
    ? 'profile_id'
    : (in_array('user_id', $candidateTableColumns, true) ? 'user_id' : null);
$profilesTableExists = dashboardTableExists($pdo, 'profiles');
$usersTableExists = dashboardTableExists($pdo, 'users');
$positionsTableExists = dashboardTableExists($pdo, 'positions');
$electionsTableExists = dashboardTableExists($pdo, 'elections');
$candidateDocumentsTableExists = dashboardTableExists($pdo, 'candidate_documents') || dashboardTableExists($pdo, 'candidacy_filings');

if ($pdo) {
    try {
        // Total candidates
        $dashboardStats['total_candidates'] = (int) $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
        // All candidates are considered "filed" regardless of status
        $dashboardStats['filed_applications'] = $dashboardStats['total_candidates'];
        // Count pending: NULL or explicit 'pending' status
        if ($candidateStatusColumn) {
            $dashboardStats['pending_review'] = (int) $pdo->query("SELECT COUNT(*) FROM candidates WHERE {$candidateStatusColumn} IS NULL OR LOWER(COALESCE({$candidateStatusColumn}, '')) = 'pending'")->fetchColumn();
            // Count approved: only explicit 'approved' status
            $dashboardStats['approved'] = (int) $pdo->query("SELECT COUNT(*) FROM candidates WHERE LOWER(COALESCE({$candidateStatusColumn}, '')) = 'approved'")->fetchColumn();
        }

        if ($candidateTableExists) {
            $selectParts = [
                'c.id',
                'c.created_at',
            ];

            if ($candidateStatusColumn) {
                $selectParts[] = 'c.' . $candidateStatusColumn . ' AS status';
            } else {
                $selectParts[] = "'pending' AS status";
            }

            if (in_array('image_url', $candidateTableColumns, true)) {
                $selectParts[] = 'c.image_url';
            } elseif (in_array('profile_photo', $candidateTableColumns, true)) {
                $selectParts[] = 'c.profile_photo AS image_url';
            } else {
                $selectParts[] = "'' AS image_url";
            }

            if (in_array('election_id', $candidateTableColumns, true)) {
                $selectParts[] = 'c.election_id';
            }

            if (in_array('position_id', $candidateTableColumns, true)) {
                $selectParts[] = 'c.position_id';
            }

            $joinSql = '';

            if ($electionsTableExists && in_array('election_id', $candidateTableColumns, true)) {
                $electionColumns = dashboardTableColumns($pdo, 'elections');
                $electionTitleColumn = in_array('title', $electionColumns, true)
                    ? 'title'
                    : (in_array('name', $electionColumns, true) ? 'name' : null);

                if ($electionTitleColumn) {
                    $selectParts[] = 'e.' . $electionTitleColumn . ' AS election_title';
                    $joinSql .= ' LEFT JOIN elections e ON e.id = c.election_id';
                }
            }
            if ($candidateIdColumn === 'profile_id' && $profilesTableExists) {
                $selectParts[] = 'p.first_name';
                $selectParts[] = 'p.last_name';
                $selectParts[] = 'p.program_code';
                $selectParts[] = 'p.faculty_code';
                $selectParts[] = 'p.year_level';
                $joinSql .= ' LEFT JOIN profiles p ON p.id = c.profile_id';
            } elseif ($candidateIdColumn === 'user_id' && $usersTableExists) {
                $selectParts[] = 'u.first_name';
                $selectParts[] = 'u.last_name';
                $selectParts[] = 'u.student_id';
                $selectParts[] = 'u.year_level';
                $joinSql .= ' LEFT JOIN users u ON u.id = c.user_id';
            }

            if ($positionsTableExists && in_array('position_id', $candidateTableColumns, true)) {
                $selectParts[] = 'pos.name AS position_name';
                $joinSql .= ' LEFT JOIN positions pos ON pos.id = c.position_id';
            }

            $candidateStmt = $pdo->query('SELECT ' . implode(', ', $selectParts) . ' FROM candidates c' . $joinSql . ' ORDER BY c.created_at DESC');
            $dashboardCandidates = $candidateStmt->fetchAll();

            foreach ($dashboardCandidates as $dashboardCandidate) {
                $electionKey = trim((string) ($dashboardCandidate['election_title'] ?? 'Unassigned Election'));
                if ($electionKey === '') {
                    $electionKey = 'Unassigned Election';
                }

                $positionKey = trim((string) ($dashboardCandidate['position_name'] ?? 'Unassigned Position'));
                if ($positionKey === '') {
                    $positionKey = 'Unassigned Position';
                }

                if (!isset($dashboardCandidatesByElection[$electionKey])) {
                    $dashboardCandidatesByElection[$electionKey] = [];
                }

                if (!isset($dashboardCandidatesByElection[$electionKey][$positionKey])) {
                    $dashboardCandidatesByElection[$electionKey][$positionKey] = [];
                }

                $dashboardCandidatesByElection[$electionKey][$positionKey][] = $dashboardCandidate;
            }

            if ($candidateDocumentsTableExists && !empty($dashboardCandidates)) {
                $candidateIds = array_values(array_filter(array_column($dashboardCandidates, 'id')));
                if (!empty($candidateIds)) {
                    $placeholders = implode(',', array_fill(0, count($candidateIds), '?'));
                    $documentTable = dashboardTableExists($pdo, 'candidate_documents') ? 'candidate_documents' : 'candidacy_filings';
                    $documentTableColumns = dashboardTableColumns($pdo, $documentTable);
                    $documentNameColumn = $documentTable === 'candidate_documents' ? 'document_name' : 'document_type';
                    $documentOrderColumn = in_array('uploaded_at', $documentTableColumns, true)
                        ? 'uploaded_at'
                        : (in_array('created_at', $documentTableColumns, true) ? 'created_at' : 'candidate_id');

                    $documentStmt = $pdo->prepare("SELECT candidate_id, {$documentNameColumn} AS document_name, document_url FROM {$documentTable} WHERE candidate_id IN ({$placeholders}) ORDER BY {$documentOrderColumn} ASC");
                    $documentStmt->execute($candidateIds);

                    while ($documentRow = $documentStmt->fetch(PDO::FETCH_ASSOC)) {
                        $dashboardCandidateDocuments[$documentRow['candidate_id']][] = $documentRow;
                    }
                }
            }
        }
    } catch (Throwable $e) {
        error_log('Dashboard stats query error: ' . $e->getMessage());
    }
}

if (isset($_GET['live_stats']) && $pdo) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dashboardStats, JSON_UNESCAPED_SLASHES);
    exit;
}
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
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <?php include '../includes/sidebar.php'; ?>

    <div class="lg:ml-72 flex flex-col min-w-0 min-h-screen">
        <?php include '../includes/header.php'; ?>

        <main class="p-8 flex-1">
            <!-- Stats Grid -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-bold">Total Candidates</p>
                            <p id="statTotalCandidates" class="text-4xl font-black text-navy mt-2"><?php echo $dashboardStats['total_candidates']; ?></p>
                        </div>
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-2xl text-blue-500">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-bold">Filed Applications</p>
                            <p id="statFiledApplications" class="text-4xl font-black text-navy mt-2"><?php echo $dashboardStats['filed_applications']; ?></p>
                        </div>
                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-2xl text-emerald-500">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-bold">Pending Review</p>
                            <p id="statPendingReview" class="text-4xl font-black text-navy mt-2"><?php echo $dashboardStats['pending_review']; ?></p>
                        </div>
                        <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-2xl text-amber-500">
                            <i class="fa-solid fa-hourglass-end"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-bold">Approved</p>
                            <p id="statApproved" class="text-4xl font-black text-navy mt-2"><?php echo $dashboardStats['approved']; ?></p>
                        </div>
                        <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center text-2xl text-green-600">
                            <i class="fa-solid fa-thumbs-up"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidate Filings by Election and Position -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-navy">Candidate Filing Status</h3>
                        <p class="text-sm text-slate-500 mt-1">Grouped by election first, then by position.</p>
                    </div>
                    <input id="dashboardCandidateSearch" type="text" placeholder="Search candidates or positions..." class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm outline-none focus:border-gold md:w-80">
                </div>

                <div class="p-8 space-y-8">
                    <?php if (!empty($dashboardCandidatesByElection)): ?>
                        <?php foreach ($dashboardCandidatesByElection as $electionTitle => $positionsByElection): ?>
                            <section class="candidate-election-group rounded-[2.25rem] border border-slate-100 bg-white p-5 md:p-6 shadow-sm" data-election="<?php echo htmlspecialchars(strtolower($electionTitle)); ?>">
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-6 border-b border-slate-100 pb-4">
                                    <div>
                                        <h4 class="text-2xl font-black text-navy"><?php echo htmlspecialchars($electionTitle); ?></h4>
                                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Election filing group</p>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full bg-slate-50 text-slate-500 border border-slate-200">
                                        <?php echo count($positionsByElection); ?> position<?php echo count($positionsByElection) === 1 ? '' : 's'; ?>
                                    </span>
                                </div>

                                <div class="space-y-6">
                                    <?php foreach ($positionsByElection as $positionName => $positionCandidates): ?>
                                        <section class="candidate-position-group rounded-[1.75rem] border border-slate-100 bg-slate-50/40 p-4 md:p-5" data-position="<?php echo htmlspecialchars(strtolower($positionName)); ?>" data-election="<?php echo htmlspecialchars(strtolower($electionTitle)); ?>">
                                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                                <div>
                                                    <h5 class="text-xl font-black text-navy"><?php echo htmlspecialchars($positionName); ?></h5>
                                                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400"><?php echo count($positionCandidates); ?> candidate<?php echo count($positionCandidates) === 1 ? '' : 's'; ?> filed</p>
                                                </div>
                                                <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full bg-white text-slate-500 border border-slate-200">Position Group</span>
                                            </div>

                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                                <?php foreach ($positionCandidates as $candidate): ?>
                                                    <?php
                                                        $firstName = trim((string) ($candidate['first_name'] ?? ''));
                                                        $lastName = trim((string) ($candidate['last_name'] ?? ''));
                                                        $fullName = trim($firstName . ' ' . $lastName) ?: 'Unknown Candidate';
                                                        $initials = getCandidateInitials($firstName, $lastName, 'UC');
                                                        $programCode = strtoupper((string) ($candidate['program_code'] ?? ''));
                                                        $yearLevel = (string) ($candidate['year_level'] ?? '');
                                                        $status = strtolower((string) ($candidate['status'] ?? 'pending'));
                                                        $candidateId = (string) ($candidate['id'] ?? '');
                                                        $documentCount = isset($dashboardCandidateDocuments[$candidateId]) ? count($dashboardCandidateDocuments[$candidateId]) : 0;
                                                        $documentCount = min($documentCount, 5);
                                                        $candidateImageUrl = trim((string) ($candidate['image_url'] ?? ''));

                                                        if ($status === 'approved') {
                                                            $statusLabel = 'Approved';
                                                            $statusClass = 'text-emerald-600 bg-emerald-50 border-emerald-100';
                                                            $progressWidth = '100%';
                                                            $progressLabel = '5/5';
                                                        } elseif ($status === 'rejected') {
                                                            $statusLabel = 'Rejected';
                                                            $statusClass = 'text-rose-600 bg-rose-50 border-rose-100';
                                                            $progressWidth = '0%';
                                                            $progressLabel = '0/5';
                                                        } else {
                                                            $statusLabel = 'Pending';
                                                            $statusClass = 'text-amber-600 bg-amber-50 border-amber-100';
                                                            $progressPercent = (int) round(($documentCount / 5) * 100);
                                                            $progressWidth = max(0, min(100, $progressPercent)) . '%';
                                                            $progressLabel = $documentCount . '/5';
                                                        }
                                                    ?>
                                                    <div data-admin-search-item class="bg-white rounded-[1.75rem] border border-slate-100 p-5 shadow-sm hover:border-slate-200 transition candidate-card" data-status="<?php echo htmlspecialchars($status); ?>" data-position="<?php echo htmlspecialchars(strtolower($positionName)); ?>" data-election="<?php echo htmlspecialchars(strtolower($electionTitle)); ?>">
                                                        <div class="flex items-start justify-between gap-4 mb-4">
                                                            <div class="flex items-center gap-3 min-w-0">
                                                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white font-bold overflow-hidden flex items-center justify-center flex-shrink-0">
                                                                    <?php if ($candidateImageUrl !== ''): ?>
                                                                        <img src="<?php echo htmlspecialchars('../' . ltrim($candidateImageUrl, '/')); ?>" alt="<?php echo htmlspecialchars($fullName); ?>" class="w-full h-full object-cover">
                                                                    <?php else: ?>
                                                                        <?php echo htmlspecialchars($initials); ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="min-w-0">
                                                                    <p class="font-bold text-navy truncate"><?php echo htmlspecialchars($fullName); ?></p>
                                                                    <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars(trim($programCode . ($yearLevel !== '' ? ' - ' . $yearLevel : ''))); ?></p>
                                                                </div>
                                                            </div>
                                                            <span class="text-[10px] font-black px-3 py-1.5 rounded-lg uppercase border <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                                        </div>

                                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm mb-4">
                                                            <div class="rounded-xl bg-slate-50 p-3">
                                                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Election</p>
                                                                <p class="font-bold text-slate-700"><?php echo htmlspecialchars($electionTitle); ?></p>
                                                            </div>
                                                            <div class="rounded-xl bg-slate-50 p-3">
                                                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Documents</p>
                                                                <p class="font-bold text-slate-700"><?php echo htmlspecialchars((string) $documentCount); ?> / 5</p>
                                                            </div>
                                                            <div class="rounded-xl bg-slate-50 p-3">
                                                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Progress</p>
                                                                <p class="font-bold text-slate-700"><?php echo htmlspecialchars($progressLabel); ?></p>
                                                            </div>
                                                        </div>

                                                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                                            <div class="bg-gradient-to-r from-royal to-gold h-full" style="width: <?php echo htmlspecialchars($progressWidth); ?>"></div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </section>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12 text-slate-500">No candidates found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('dashboardCandidateSearch');
            const searchRows = document.querySelectorAll('[data-admin-search-item]');
            const positionGroups = document.querySelectorAll('.candidate-position-group');
            const electionGroups = document.querySelectorAll('.candidate-election-group');

            if (!searchInput || !searchRows.length) {
                return;
            }

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim().toLowerCase();

                searchRows.forEach((row) => {
                    const rowText = row.textContent.toLowerCase();
                    row.style.display = !query || rowText.includes(query) ? '' : 'none';
                });

                positionGroups.forEach((group) => {
                    const visibleCards = group.querySelectorAll('[data-admin-search-item]:not([style*="display: none"])');
                    group.style.display = visibleCards.length > 0 ? '' : 'none';
                });

                electionGroups.forEach((group) => {
                    const visiblePositionGroups = group.querySelectorAll('.candidate-position-group:not([style*="display: none"])');
                    group.style.display = visiblePositionGroups.length > 0 ? '' : 'none';
                });
            });

            const statMap = {
                total_candidates: document.getElementById('statTotalCandidates'),
                filed_applications: document.getElementById('statFiledApplications'),
                pending_review: document.getElementById('statPendingReview'),
                approved: document.getElementById('statApproved'),
            };

            const refreshLiveStats = async () => {
                try {
                    const response = await fetch('dashboard.php?live_stats=1', { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) {
                        return;
                    }

                    const stats = await response.json();
                    Object.entries(statMap).forEach(([key, element]) => {
                        if (element && Object.prototype.hasOwnProperty.call(stats, key)) {
                            element.textContent = String(stats[key]);
                        }
                    });
                } catch (error) {
                    // Ignore transient polling failures.
                }
            };

            refreshLiveStats();
            setInterval(refreshLiveStats, 15000);
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
