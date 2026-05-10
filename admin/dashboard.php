<?php
require_once '../includes/config.php';
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

if ($pdo) {
    try {
        // Total candidates
        $dashboardStats['total_candidates'] = (int) $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
        // All candidates are considered "filed" regardless of status
        $dashboardStats['filed_applications'] = $dashboardStats['total_candidates'];
        // Count pending: NULL or explicit 'pending' status
        $dashboardStats['pending_review'] = (int) $pdo->query("SELECT COUNT(*) FROM candidates WHERE status IS NULL OR LOWER(COALESCE(status, '')) = 'pending'")->fetchColumn();
        // Count approved: only explicit 'approved' status
        $dashboardStats['approved'] = (int) $pdo->query("SELECT COUNT(*) FROM candidates WHERE LOWER(COALESCE(status, '')) = 'approved'")->fetchColumn();

        $candidateStmt = $pdo->query(
            "SELECT
                c.id,
                c.status,
                c.created_at,
                c.election_id,
                c.position_id,
                p.first_name,
                p.last_name,
                p.program_code,
                p.faculty_code,
                p.year_level,
                pos.name AS position_name
             FROM candidates c
             LEFT JOIN profiles p ON p.id = c.profile_id
             LEFT JOIN positions pos ON pos.id = c.position_id
             ORDER BY c.created_at DESC"
        );
        $dashboardCandidates = $candidateStmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Dashboard stats query error: ' . $e->getMessage());
    }
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
                            <p class="text-4xl font-black text-navy mt-2"><?php echo $dashboardStats['total_candidates']; ?></p>
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
                            <p class="text-4xl font-black text-navy mt-2"><?php echo $dashboardStats['filed_applications']; ?></p>
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
                            <p class="text-4xl font-black text-navy mt-2"><?php echo $dashboardStats['pending_review']; ?></p>
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
                            <p class="text-4xl font-black text-navy mt-2"><?php echo $dashboardStats['approved']; ?></p>
                        </div>
                        <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center text-2xl text-green-600">
                            <i class="fa-solid fa-thumbs-up"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidate Filings Table -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-2xl font-black text-navy">Candidate Filing Status</h3>
                    <input id="dashboardCandidateSearch" type="text" placeholder="Search candidates..." class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm outline-none focus:border-gold">
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Candidate</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Position</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Documents</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Progress</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-slate-600 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dashboardCandidates)): ?>
                                <?php foreach ($dashboardCandidates as $candidate): ?>
                                    <?php
                                        $firstName = trim((string) ($candidate['first_name'] ?? ''));
                                        $lastName = trim((string) ($candidate['last_name'] ?? ''));
                                        $fullName = trim($firstName . ' ' . $lastName) ?: 'Unknown Candidate';
                                        $initials = strtoupper(substr($firstName ?: 'U', 0, 1) . substr($lastName ?: 'C', 0, 1));
                                        $programCode = strtoupper((string) ($candidate['program_code'] ?? ''));
                                        $yearLevel = (string) ($candidate['year_level'] ?? '');
                                        $positionName = (string) ($candidate['position_name'] ?? 'Unassigned');
                                        $status = strtolower((string) ($candidate['status'] ?? 'pending'));

                                        if ($status === 'approved') {
                                            $statusLabel = 'Approved';
                                            $statusClass = 'text-emerald-600 bg-emerald-50 border-emerald-100';
                                            $progressWidth = '100%';
                                            $progressLabel = '5/5';
                                            $documentStates = ['emerald', 'emerald', 'emerald', 'emerald', 'emerald'];
                                        } elseif ($status === 'rejected') {
                                            $statusLabel = 'Rejected';
                                            $statusClass = 'text-rose-600 bg-rose-50 border-rose-100';
                                            $progressWidth = '0%';
                                            $progressLabel = '0/5';
                                            $documentStates = ['slate', 'slate', 'slate', 'slate', 'slate'];
                                        } else {
                                            $statusLabel = 'Pending';
                                            $statusClass = 'text-amber-600 bg-amber-50 border-amber-100';
                                            $progressWidth = '80%';
                                            $progressLabel = '4/5';
                                            $documentStates = ['emerald', 'emerald', 'emerald', 'emerald', 'slate'];
                                        }
                                    ?>
                                    <tr data-admin-search-item class="border-b border-slate-100 hover:bg-slate-50 transition">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold"><?php echo htmlspecialchars($initials); ?></div>
                                                <div>
                                                    <p class="font-bold text-navy"><?php echo htmlspecialchars($fullName); ?></p>
                                                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars(trim($programCode . ($yearLevel !== '' ? ' - ' . $yearLevel : ''))); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="text-sm font-bold text-slate-600"><?php echo htmlspecialchars($positionName); ?></span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex gap-1">
                                                <?php foreach ($documentStates as $documentState): ?>
                                                    <?php if ($documentState === 'emerald'): ?>
                                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                                    <?php else: ?>
                                                        <div class="w-6 h-6 bg-slate-300 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-times"></i></div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                                <div class="bg-gradient-to-r from-royal to-gold h-full" style="width: <?php echo htmlspecialchars($progressWidth); ?>"></div>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($progressLabel); ?></p>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="text-xs font-black px-3 py-1.5 rounded-lg uppercase border <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <button class="w-10 h-10 rounded-xl bg-slate-50 text-navy hover:bg-blue-50 hover:text-blue-600 transition">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="border-b border-slate-100">
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-500">No candidates found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('dashboardCandidateSearch');
            const searchRows = document.querySelectorAll('tr[data-admin-search-item]');

            if (!searchInput || !searchRows.length) {
                return;
            }

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim().toLowerCase();

                searchRows.forEach((row) => {
                    const rowText = row.textContent.toLowerCase();
                    row.style.display = !query || rowText.includes(query) ? '' : 'none';
                });
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
