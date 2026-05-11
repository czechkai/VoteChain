<?php
// Determine role from context
$role = $role ?? 'student'; // Options: 'student', 'candidate', 'admin'
$activePage = $activePage ?? 'dashboard';

$logoInitials = 'VC';
if ($role === 'admin') {
    $logoInitials = 'AC';
} elseif ($role === 'candidate') {
    $logoInitials = 'CH';
}

// Calculate pending candidate count for admin
$pendingCandidateCount = 0;
if ($role === 'admin' && isset($pdo) && $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = ?");
        $stmt->execute(['candidates']);
        $columns = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
        
        $statusColumn = in_array('status', $columns, true)
            ? 'status'
            : (in_array('filing_status', $columns, true) ? 'filing_status' : null);
        
        if ($statusColumn) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM candidates WHERE {$statusColumn} IS NULL OR LOWER(COALESCE({$statusColumn}, '')) = 'pending'");
            $pendingCandidateCount = (int) $countStmt->fetchColumn();
        }
    } catch (Exception $e) {
        error_log('Sidebar pending count error: ' . $e->getMessage());
    }
}

$activeLinkClass = 'flex items-center gap-3 px-4 py-3.5 rounded-xl bg-white/10 border-l-4 border-gold text-white font-bold transition-all group';
$inactiveLinkClass = 'flex items-center gap-3 px-4 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 font-semibold transition-all group';
?>

<button id="mobileMenuBtn" class="lg:hidden fixed top-4 right-4 z-50 p-2 bg-navy text-white rounded-lg shadow-lg">
    <i class="fa-solid fa-bars"></i>
</button>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-gradient-to-b from-navy to-royal text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xl shadow-navy/30">
    <div class="h-full flex flex-col p-6 overflow-y-auto">
        <!-- Logo -->
        <div class="flex items-center gap-3 mb-12 px-2">
            <div class="w-10 h-10 bg-gold/20 rounded-xl flex items-center justify-center border border-gold/40 shadow-lg">
                <span class="text-gold text-sm font-black tracking-tight"><?php echo $logoInitials; ?></span>
            </div>
            <span class="text-2xl font-extrabold tracking-tight">
                <?php if ($role === 'admin'): ?>
                    ADMIN<span class="text-gold">CORE</span>
                <?php elseif ($role === 'candidate'): ?>
                    CANDIDATE<span class="text-gold">HUB</span>
                <?php else: ?>
                    VOTE<span class="text-gold">CHAIN</span>
                <?php endif; ?>
            </span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-2">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/30 mb-4 px-2">
                <?php if ($role === 'admin'): ?>
                    Management
                <?php else: ?>
                    Main Menu
                <?php endif; ?>
            </p>

            <?php if ($role === 'admin'): ?>
                <!-- Admin Navigation -->
                <a href="/votechain/admin/dashboard.php" class="<?php echo $activePage === 'dashboard' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-gauge-high w-5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="/votechain/admin/candidate.php" class="<?php echo $activePage === 'candidate' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-users-gear w-5"></i>
                    <span>Candidate Apps</span>
                    <span id="adminCandidateAppsBadge" class="ml-auto bg-gold text-navy text-[10px] px-2 py-0.5 rounded-full font-black"><?php echo $pendingCandidateCount; ?></span>
                </a>

                <a href="/votechain/admin/election.php" class="<?php echo $activePage === 'election' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-box-archive w-5"></i>
                    <span>Elections</span>
                </a>

                <a href="/votechain/admin/announcements.php" class="<?php echo $activePage === 'announcements' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-bullhorn w-5"></i>
                    <span>Announcements</span>
                </a>

                <div class="h-px bg-white/10 my-4 mx-4"></div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/30 mb-4 px-4">Reports</p>

                <a href="/votechain/admin/results.php" class="<?php echo $activePage === 'results' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-chart-pie w-5"></i>
                    <span>Live Results</span>
                </a>

                <a href="/votechain/admin/calendar.php" class="<?php echo $activePage === 'calendar' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-calendar-days w-5"></i>
                    <span>Calendar</span>
                </a>

            <?php elseif ($role === 'candidate'): ?>
                <!-- Candidate Navigation -->
                <a href="/votechain/candidate/dashboard.php" class="<?php echo $activePage === 'dashboard' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="/votechain/candidate/campaign.php" class="<?php echo $activePage === 'campaign' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-bullhorn w-5"></i>
                    <span>Manage Campaign</span>
                </a>

                <a href="/votechain/candidate/filing.php" class="<?php echo $activePage === 'filing' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-clipboard-check w-5"></i>
                    <span>Filing Status</span>
                </a>

            <?php else: ?>
                <!-- Student Navigation -->
                <a href="/votechain/student/dashboard.php" class="<?php echo $activePage === 'dashboard' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-chart-pie w-5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="/votechain/student/newsfeed.php" class="<?php echo $activePage === 'newsfeed' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-newspaper w-5"></i>
                    <span>News Feed</span>
                </a>

                <a href="/votechain/student/vote.php" class="<?php echo $activePage === 'vote' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-box-archive w-5"></i>
                    <span>Vote Now</span>
                </a>

                <a href="/votechain/student/results.php" class="<?php echo $activePage === 'results' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-square-poll-vertical w-5"></i>
                    <span>Results</span>
                </a>

                <a href="/votechain/student/calendar.php" class="<?php echo $activePage === 'calendar' ? $activeLinkClass : $inactiveLinkClass; ?>">
                    <i class="fa-solid fa-calendar-days w-5"></i>
                    <span>Calendar</span>
                </a>

            <?php endif; ?>
        </nav>

        <!-- Role Switching / Profile -->
        <div class="border-t border-white/10 pt-6 mt-6">
            <?php if ($role === 'candidate'): ?>
                <a href="/votechain/candidate/switch_to_student.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-white/60 hover:text-gold hover:bg-white/5 font-bold transition-all group mb-4">
                    <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
                    <span class="text-sm">Switch to Student</span>
                </a>
            <?php elseif ($role === 'student'): ?>
                <a href="/votechain/student/switch_to_candidate.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-white/60 hover:text-gold hover:bg-white/5 font-bold transition-all group mb-4">
                    <i class="fa-solid fa-arrow-right-to-bracket w-5"></i>
                    <span class="text-sm">Switch to Candidate</span>
                </a>
            <?php endif; ?>

            <a href="/votechain/auth/logout.php" class="w-full flex items-center gap-3 px-4 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 font-bold transition-all group">
                <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</aside>

<script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const adminCandidateAppsBadge = document.getElementById('adminCandidateAppsBadge');

    mobileMenuBtn?.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
    });

    document.addEventListener('click', (e) => {
        if (!sidebar?.contains(e.target) && !mobileMenuBtn?.contains(e.target)) {
            sidebar?.classList.add('-translate-x-full');
        }
    });

    async function refreshAdminCandidateAppsBadge() {
        if (!adminCandidateAppsBadge) {
            return;
        }

        try {
            const response = await fetch('/votechain/admin/dashboard.php?live_stats=1', {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                return;
            }

            const stats = await response.json();
            const pendingCount = Number.parseInt(stats.pending_review ?? stats.pending ?? 0, 10);

            if (Number.isFinite(pendingCount)) {
                adminCandidateAppsBadge.textContent = String(pendingCount);
            }
        } catch (error) {
            // Ignore transient refresh failures.
        }
    }

    refreshAdminCandidateAppsBadge();
    setInterval(refreshAdminCandidateAppsBadge, 15000);
</script>
