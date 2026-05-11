<?php
require_once '../includes/config.php';
requireRole('student');
$role = 'student';
$activePage = 'vote';

$allElections = [];
$startColumn = 'starts_at';
$endColumn = 'ends_at';

if ($pdo) {
    try {
        $colStmt = $pdo->prepare(
            "SELECT column_name
             FROM information_schema.columns
             WHERE table_schema = CURRENT_SCHEMA() AND table_name = 'elections'"
        );
        $colStmt->execute();
        $electionColumns = array_map('strtolower', $colStmt->fetchAll(PDO::FETCH_COLUMN));

        $startColumn = in_array('starts_at', $electionColumns, true) ? 'starts_at' : (in_array('start_date', $electionColumns, true) ? 'start_date' : 'created_at');
        $endColumn = in_array('ends_at', $electionColumns, true) ? 'ends_at' : (in_array('end_date', $electionColumns, true) ? 'end_date' : 'created_at');

        $stmt = $pdo->query("SELECT * FROM elections ORDER BY {$startColumn} DESC, created_at DESC");
        $allElections = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log('Error fetching elections for student vote page: ' . $e->getMessage());
        $allElections = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Now | VoteChain DOrSU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0A1F44',
                        royal: '#1E3A8A',
                        gold: '#FFC107',
                        slate: { 850: '#1e293b', 950: '#0f172a' }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .sidebar-gradient { background: linear-gradient(180deg, #0A1F44 0%, #1E3A8A 100%); }
        .nav-item-active { background: rgba(255, 255, 255, 0.1); border-left: 4px solid #FFC107; color: white !important; }
        .election-card { background: white; border-radius: 2rem; border: 1px solid #e2e8f0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .election-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05); border-color: #1E3A8A; }
        .status-badge { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 4px 12px; border-radius: 9999px; }
    </style>
</head>
<body class="flex min-h-screen">
    <?php $role = 'student'; $activePage = 'vote'; include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-72 p-4 md:p-8">
        <header class="mb-10">
            <h1 class="text-3xl font-extrabold text-navy">Election Center</h1>
            <p class="text-slate-500 font-medium mt-1">View all elections. Voting opens only during the scheduled date range.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (!$allElections): ?>
                <div class="election-card p-8">
                    <h3 class="text-lg font-extrabold text-navy mb-2">No Elections Available</h3>
                    <p class="text-slate-500 text-sm">Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($allElections as $election): ?>
                    <?php
                    $title = $election['title'] ?? $election['name'] ?? 'Election';
                    $scope = $election['scope'] ?? $election['assignment'] ?? $election['type'] ?? 'Election';
                    $status = strtolower((string)($election['status'] ?? 'scheduled'));

                    $startRaw = $election[$startColumn] ?? null;
                    $endRaw = $election[$endColumn] ?? null;
                    $startDate = $startRaw ? new DateTime($startRaw) : null;
                    $endDate = $endRaw ? new DateTime($endRaw) : null;

                    $today = new DateTime('today');
                    $isVotingDay = false;
                    if ($startDate && $endDate) {
                        $startDay = new DateTime($startDate->format('Y-m-d'));
                        $endDay = new DateTime($endDate->format('Y-m-d'));
                        $isVotingDay = ($today >= $startDay && $today <= $endDay);
                    }

                    $statusLabel = $isVotingDay ? 'Voting Open' : 'Coming Soon';
                    $statusBadgeClass = $isVotingDay
                        ? 'bg-green-100 text-green-600'
                        : 'bg-amber-100 text-amber-700';
                    ?>
                    <div class="election-card flex flex-col">
                        <div class="p-8 flex-1">
                            <div class="flex justify-between items-start mb-6">
                                <div class="w-14 h-14 bg-navy text-white rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-navy/20">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <span class="status-badge <?php echo $statusBadgeClass; ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                            </div>

                            <h3 class="text-xl font-extrabold text-navy mb-2"><?php echo htmlspecialchars($title); ?></h3>
                            <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mb-6">
                                <?php echo htmlspecialchars($scope); ?>
                            </p>
                            <div class="space-y-4 mb-8">
                                <div class="text-xs font-semibold text-slate-500">
                                    <?php if ($startDate && $endDate): ?>
                                        <?php echo htmlspecialchars($startDate->format('M d, Y')); ?> - <?php echo htmlspecialchars($endDate->format('M d, Y')); ?>
                                    <?php else: ?>
                                        Schedule: To be announced
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-3 text-slate-600">
                                    <i class="fa-solid fa-shield-halved text-royal w-5"></i>
                                    <span class="text-sm font-semibold">Blockchain Verified</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 pt-0">
                            <?php if ($isVotingDay): ?>
                                <a href="ballot.php?election_id=<?php echo urlencode($election['id']); ?>" class="w-full py-4 bg-navy text-white rounded-2xl font-bold flex items-center justify-center gap-3 hover:bg-royal transition-all shadow-xl shadow-navy/10 group">
                                    Proceed to Ballot
                                    <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            <?php else: ?>
                                <button type="button" disabled class="w-full py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold flex items-center justify-center gap-3 cursor-not-allowed">
                                    Coming Soon
                                    <i class="fa-solid fa-hourglass-half"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Security Notice -->
        <div class="mt-12 bg-white p-8 rounded-[2rem] border border-slate-100 flex items-center gap-6">
            <div class="hidden sm:flex w-16 h-16 bg-gold/10 text-gold rounded-full items-center justify-center text-2xl flex-shrink-0">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
            <div>
                <h4 class="text-navy font-extrabold">Voter Security Protocol</h4>
                <p class="text-slate-500 text-sm max-w-2xl">Your vote is encrypted and recorded on a private blockchain. Once submitted, it cannot be altered or deleted. Ensure you are alone while casting your vote to maintain ballot secrecy.</p>
            </div>
        </div>
    </main>

    <!-- Mobile Navigation (Same as Newsfeed) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 flex justify-around items-center z-50">
        <a href="dashboard.php" class="text-slate-400"><i class="fa-solid fa-chart-pie text-xl"></i></a>
        <a href="newsfeed.php" class="text-slate-400"><i class="fa-solid fa-newspaper text-xl"></i></a>
        <div class="w-12 h-12 bg-navy rounded-full -mt-10 flex items-center justify-center text-white shadow-xl border-4 border-white">
            <i class="fa-solid fa-box-archive"></i>
        </div>
        <a href="results.php" class="text-slate-400"><i class="fa-solid fa-square-poll-vertical text-xl"></i></a>
        <a href="profile.php" class="text-slate-400"><i class="fa-solid fa-user-gear text-xl"></i></a>
    </div>

</body>
</html>