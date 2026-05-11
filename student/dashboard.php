<?php
require_once '../includes/config.php';

/** @var PDO $pdo */
if (!$pdo) {
    die('Database connection failed. Please check your configuration.');
}

requireRole('student');
$role = 'student';
$activePage = 'dashboard';

$voterProfileId = $_SESSION['profile_id'] ?? null;
$activeElections = [];
$electionVoteStatus = [];
$stats = [
    'candidates' => 0,
    'total_votes' => 0,
    'time_remaining' => 'No Active Election',
    'deadline_ts' => 0
];
$studentProfile = null;
$upcomingEvents = [];

if ($pdo && $voterProfileId) {
    try {
        // Fetch Student Profile Details
        $stmt = $pdo->prepare("SELECT * FROM profiles WHERE id = ?");
        $stmt->execute([$voterProfileId]);
        $studentProfile = $stmt->fetch();

        // Schema detection for election columns
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = 'elections'");
        $stmt->execute();
        $eCols = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
        $col_start = in_array('starts_at', $eCols) ? 'starts_at' : (in_array('start_date', $eCols) ? 'start_date' : 'created_at');
        $col_end = in_array('ends_at', $eCols) ? 'ends_at' : (in_array('end_date', $eCols) ? 'end_date' : 'created_at');

        // Fetch elections that are currently active based on TIME
        $stmt = $pdo->prepare("SELECT * FROM elections WHERE (status = 'active') OR (NOW() BETWEEN $col_start AND $col_end) ORDER BY $col_start DESC");
        $stmt->execute();
        $activeElections = $stmt->fetchAll();

        if ($activeElections) {
            $electionIds = array_column($activeElections, 'id');
            $placeholders = implode(',', array_fill(0, count($electionIds), '?'));

            // Check Vote Status
            $voteCheckStmt = $pdo->prepare("SELECT election_id FROM votes WHERE voter_profile_id = ? AND election_id IN ($placeholders)");
            $voteCheckStmt->execute(array_merge([$voterProfileId], $electionIds));
            $votedIds = $voteCheckStmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($activeElections as $e) {
                $electionVoteStatus[$e['id']] = in_array($e['id'], $votedIds);
            }

            // Count Candidates in Active Elections
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidates WHERE election_id IN ($placeholders) AND status = 'approved'");
            $stmt->execute($electionIds);
            $stats['candidates'] = $stmt->fetchColumn();

            // Time Remaining (from the nearest deadline based on the calendar end date)
            $stmt = $pdo->prepare("SELECT $col_end FROM elections WHERE id IN ($placeholders) AND NOW() < $col_end ORDER BY $col_end ASC LIMIT 1");
            $stmt->execute($electionIds);
            $deadline = $stmt->fetchColumn();
            if ($deadline) {
                $stats['deadline_ts'] = strtotime($deadline);
            }
        }

        // Total Votes Cast (System-wide)
        $stats['total_votes'] = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();

        // Upcoming Schedule (Next 2 events)
        $stmt = $pdo->query("SELECT title, starts_at as event_date, 'Start' as type FROM elections WHERE starts_at > NOW() UNION SELECT title, ends_at, 'End' FROM elections WHERE ends_at > NOW() ORDER BY event_date ASC LIMIT 2");
        $upcomingEvents = $stmt->fetchAll();

    } catch (Exception $e) {
        error_log("Dashboard Sync Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | VoteChain DOrSU</title>
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
                        slate: {
                            850: '#1e293b',
                            950: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .sidebar-gradient { background: linear-gradient(180deg, #0A1F44 0%, #1E3A8A 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(226, 232, 240, 0.8); }
        .nav-item-active { background: rgba(255, 255, 255, 0.1); border-left: 4px solid #FFC107; color: white !important; }
        .custom-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="flex min-h-screen">
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-72 p-4 md:p-8">
        <!-- Top Bar -->
        <header class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-navy">Student Dashboard</h1>
                <p class="text-slate-500 font-medium text-sm">Welcome back, <span class="text-royal font-bold"><?php echo htmlspecialchars(($_SESSION['first_name'] ?? 'Student') . ' ' . ($_SESSION['last_name'] ?? '')); ?></span></p>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <button class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-400 hover:text-navy custom-shadow transition-all">
                        <i class="fa-solid fa-bell"></i>
                    </button>
                </div>
                <div class="flex items-center gap-3 bg-white p-2 pr-4 rounded-2xl custom-shadow">
                    <div class="w-10 h-10 bg-navy rounded-xl overflow-hidden flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($_SESSION['first_name'] ?? 'S', 0, 1) . substr($_SESSION['last_name'] ?? '', 0, 1)); ?>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-xs font-extrabold text-navy"><?php echo htmlspecialchars($_SESSION['student_id'] ?? 'ID-PENDING'); ?></p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            <?php echo htmlspecialchars(($studentProfile['year_level'] ?? 'N/A') . ' • ' . ($studentProfile['program_code'] ?? 'GENERAL')); ?>
                        </p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-royal/10 text-royal rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-green-500 bg-green-50 px-2 py-1 rounded-lg uppercase">Active</span>
                </div>
                <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Active Elections</h3>
                <p class="text-2xl font-extrabold text-navy"><?php echo count($activeElections); ?></p>
            </div>

            <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gold/10 text-gold rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg uppercase">Total</span>
                </div>
                <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Candidates</h3>
                <p class="text-2xl font-extrabold text-navy"><?php echo number_format($stats['candidates']); ?></p>
            </div>

            <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-500/10 text-green-500 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-bolt text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-blue-500 bg-blue-50 px-2 py-1 rounded-lg uppercase">Real-time</span>
                </div>
                <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Total Votes Cast</h3>
                <p class="text-2xl font-extrabold text-navy"><?php echo number_format($stats['total_votes']); ?></p>
            </div>

            <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-navy/10 text-navy rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-clock text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-amber-500 bg-amber-50 px-2 py-1 rounded-lg uppercase">Deadline</span>
                </div>
                <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Time Remaining</h3>
                <p id="countdownTimer" class="text-2xl font-extrabold text-navy" data-expire="<?php echo $stats['deadline_ts']; ?>">--:--:--</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Side -->
            <div class="lg:col-span-8 space-y-8">
                <!-- Participation Chart -->
                <div class="glass-card p-8 rounded-[2rem] custom-shadow">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-extrabold text-navy">Voting Participation</h3>
                            <p class="text-sm text-slate-400 font-medium">Turnout rate across programs</p>
                        </div>
                        <select class="bg-slate-50 border-none text-xs font-bold text-navy px-4 py-2 rounded-xl outline-none">
                            <option>University Wide</option>
                            <option>FACET Only</option>
                        </select>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="participationChart"></canvas>
                    </div>
                </div>

                <!-- Election Overview List -->
                <div class="space-y-4">
                    <h3 class="text-lg font-extrabold text-navy ml-2">Available Elections</h3>

                    <?php if (!$activeElections): ?>
                        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 custom-shadow">
                            <p class="text-sm text-slate-400 font-bold text-center py-4 uppercase tracking-widest">No active elections found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activeElections as $election): ?>
                            <?php
                            $title = $election['title'] ?? $election['name'] ?? 'Election';
                            $subtitle = $election['description'] ?? $election['scope'] ?? 'Active voting period';
                            $hasVoted = $electionVoteStatus[$election['id']] ?? false;
                            ?>
                            <div class="group bg-white p-6 rounded-[2rem] border border-slate-100 custom-shadow hover:border-royal/30 transition-all flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div class="flex items-center gap-5">
                                    <div class="w-16 h-16 bg-navy rounded-2xl flex items-center justify-center text-white text-2xl">
                                        <i class="fa-solid fa-building-columns"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-extrabold text-navy"><?php echo htmlspecialchars($title); ?></h4>
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">
                                            <?php echo htmlspecialchars($subtitle); ?>
                                        </p>
                                        <div class="flex gap-2 mt-2">
                                            <span class="text-[10px] font-bold bg-green-100 text-green-600 px-2 py-0.5 rounded-full">Ongoing</span>
                                            <?php if ($hasVoted): ?>
                                                <span class="text-[10px] font-bold bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">Voted</span>
                                            <?php else: ?>
                                                <span class="text-[10px] font-bold bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full">Not Voted</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($hasVoted): ?>
                                    <button disabled class="px-8 py-3 bg-slate-100 text-slate-400 rounded-xl font-bold cursor-not-allowed">
                                        Voted
                                    </button>
                                <?php else: ?>
                                    <a href="ballot.php?election_id=<?php echo urlencode($election['id']); ?>" class="px-8 py-3 bg-navy text-white rounded-xl font-bold hover:bg-royal transition-all shadow-lg shadow-navy/20">
                                        Vote Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Side -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Schedule / Calendar Preview -->
                <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                    <h3 class="text-lg font-extrabold text-navy mb-6">Upcoming Schedule</h3>
                    <div class="space-y-6">
                        <?php if (empty($upcomingEvents)): ?>
                            <p class="text-xs text-slate-400 font-medium text-center">No upcoming events scheduled.</p>
                        <?php else: ?>
                            <?php foreach ($upcomingEvents as $event): ?>
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center justify-center min-w-[50px] h-14 bg-navy text-white rounded-xl">
                                        <span class="text-[10px] font-bold uppercase"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                                        <span class="text-lg font-black leading-none"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-navy text-sm truncate"><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase"><?php echo $event['type']; ?> • <?php echo date('h:i A', strtotime($event['event_date'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <a href="calendar.php" class="block text-center w-full mt-8 py-3 text-xs font-bold text-navy border-2 border-slate-100 rounded-xl hover:bg-slate-50 transition-all">
                        View All Events
                    </a>
                </div>

                <!-- Timeline / Recent Activity -->
                <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                    <h3 class="text-lg font-extrabold text-navy mb-6">Activity Log</h3>
                    <div class="relative space-y-6 ml-2">
                        <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-slate-100"></div>
                        
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1.5 w-6 h-6 bg-white border-4 border-royal rounded-full"></div>
                            <p class="text-xs font-bold text-navy">Registration Complete</p>
                            <p class="text-[10px] text-slate-400">Welcome to the VoteChain platform.</p>
                        </div>

                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1.5 w-6 h-6 bg-white border-4 border-gold rounded-full shadow-[0_0_10px_rgba(255,193,7,0.3)]"></div>
                            <p class="text-xs font-bold text-navy">Eligibility Check</p>
                            <p class="text-[10px] text-slate-400">Account verified with registrar data.</p>
                            <p class="text-[9px] text-royal font-bold uppercase mt-1">Yesterday</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize Charts
        window.onload = function() {
            const ctx = document.getElementById('participationChart').getContext('2d');

            // Countdown Timer Logic
            const timerEl = document.getElementById('countdownTimer');
            const expireTs = parseInt(timerEl.dataset.expire) * 1000;

            if (expireTs > 0) {
                setInterval(() => {
                    const now = new Date().getTime();
                    const diff = expireTs - now;
                    if (diff <= 0) {
                        timerEl.textContent = "ENDED";
                        return;
                    }
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    timerEl.textContent = 
                        String(hours).padStart(2, '0') + ":" + 
                        String(minutes).padStart(2, '0') + ":" + 
                        String(seconds).padStart(2, '0');
                }, 1000);
            } else {
                timerEl.textContent = "00:00:00";
            }
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
                    datasets: [{
                        label: 'Voter Turnout',
                        data: [120, 350, 680, 890, 1100, 1204],
                        borderColor: '#1E3A8A',
                        borderWidth: 4,
                        pointBackgroundColor: '#FFC107',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        tension: 0.4,
                        fill: true,
                        backgroundColor: (context) => {
                            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, 'rgba(30, 58, 138, 0.1)');
                            gradient.addColorStop(1, 'rgba(30, 58, 138, 0)');
                            return gradient;
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { display: true, color: '#f1f5f9' },
                            ticks: { font: { size: 10, weight: '600' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10, weight: '600' } }
                        }
                    }
                }
            });
        };
    </script>
</body>
</html>