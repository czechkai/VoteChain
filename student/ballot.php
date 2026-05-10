<?php
require_once '../includes/config.php';
requireRole('student');
$role = 'student';
$activePage = 'vote';

$voterProfileId = $_SESSION['profile_id'] ?? null;

$electionId = $_GET['election_id'] ?? '';
$alertMessage = '';
$alertType = '';

if (!$electionId) {
    header('Location: vote.php');
    exit;
}

$election = null;
$positions = [];
$positionsWithCandidates = [];

if ($pdo) {
    $electionStmt = $pdo->prepare("SELECT * FROM elections WHERE id = ? AND status = 'active' LIMIT 1");
    $electionStmt->execute([$electionId]);
    $election = $electionStmt->fetch();

    if ($election) {
        $positionStmt = $pdo->prepare(
            "SELECT DISTINCT pos.id, pos.name, pos.order_index
             FROM positions pos
             JOIN candidates c ON c.position_id = pos.id
             WHERE c.election_id = ? AND c.status = 'approved'
             ORDER BY pos.order_index"
        );
        $positionStmt->execute([$electionId]);
        $positions = $positionStmt->fetchAll();

        foreach ($positions as $position) {
            $positionsWithCandidates[] = [
                'position' => $position,
                'candidates' => getCandidates($pdo, $electionId, $position['id'])
            ];
        }
    }
}

if (isset($_GET['success'])) {
    $alertType = 'success';
    $alertMessage = 'Your vote has been recorded successfully.';
} elseif (isset($_GET['error'])) {
    $alertType = 'error';
    $alertMessage = 'Voting failed. Please review your selections and try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballot | USC General Elections</title>
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
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .selection-ring { border: 3px solid transparent; transition: all 0.2s; }
        .candidate-card.selected { border-color: #1E3A8A; background-color: #f0f4ff; }
        .candidate-card.selected .check-icon { display: flex; }
        .focused-container { max-width: 900px; margin: 0 auto; }
    </style>
</head>
<body class="pb-32">

    <!-- Stepper Header -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="vote.php" class="text-slate-400 hover:text-navy transition-colors">
                    <i class="fa-solid fa-chevron-left text-lg"></i>
                </a>
                <h1 class="font-black text-navy text-xl uppercase tracking-tight">USC <span class="text-royal">Ballot</span></h1>
            </div>
            <div class="hidden md:flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-bold">1</span>
                    <span class="text-sm font-bold text-navy">Select</span>
                </div>
                <div class="w-12 h-px bg-slate-200"></div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xs font-bold">2</span>
                    <span class="text-sm font-bold text-slate-400">Review</span>
                </div>
                <div class="w-12 h-px bg-slate-200"></div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xs font-bold">3</span>
                    <span class="text-sm font-bold text-slate-400">Submit</span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Time Remaining</p>
                <p id="timer" class="text-royal font-black text-lg">44:02</p>
            </div>
        </div>
    </nav>

    <div class="focused-container px-4 py-12">
        <?php if ($alertMessage): ?>
            <div class="mb-10 px-4 py-3 rounded-2xl text-sm font-bold <?php echo $alertType === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100'; ?>">
                <?php echo htmlspecialchars($alertMessage); ?>
            </div>
        <?php endif; ?>
        
        <!-- Guidelines -->
        <section class="bg-amber-50 border border-amber-100 p-6 rounded-3xl mb-12 flex gap-4">
            <i class="fa-solid fa-circle-info text-amber-500 text-xl mt-1"></i>
            <div>
                <h4 class="font-bold text-amber-900 mb-1">Voting Guidelines</h4>
                <ul class="text-sm text-amber-800/80 space-y-1 list-disc ml-4">
                    <li>Select exactly one (1) candidate for the Presidential position.</li>
                    <li>Verify your selection before clicking the review button.</li>
                    <li>Your vote is final once submitted to the blockchain.</li>
                </ul>
            </div>
        </section>

        <form method="POST" action="submit_vote.php">
            <input type="hidden" name="election_id" value="<?php echo htmlspecialchars($electionId); ?>">

            <?php if (!$election): ?>
                <div class="bg-white p-6 rounded-3xl border border-slate-100">
                    <p class="text-sm text-slate-500 font-medium">Election not found or not active.</p>
                </div>
            <?php elseif (!$positionsWithCandidates): ?>
                <div class="bg-white p-6 rounded-3xl border border-slate-100">
                    <p class="text-sm text-slate-500 font-medium">No approved candidates available yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($positionsWithCandidates as $group): ?>
                    <?php
                    $position = $group['position'];
                    $candidates = $group['candidates'];
                    $alreadyVoted = $voterProfileId ? hasUserVoted($pdo, $voterProfileId, $electionId, $position['id']) : false;
                    ?>
                    <div class="mb-16">
                        <div class="flex items-end justify-between mb-8 border-b-2 border-slate-100 pb-4">
                            <div>
                                <h2 class="text-2xl font-black text-navy"><?php echo htmlspecialchars($position['name']); ?></h2>
                                <p class="text-slate-400 text-sm font-bold uppercase tracking-widest"><?php echo htmlspecialchars($election['title'] ?? $election['name'] ?? 'Election'); ?></p>
                            </div>
                            <span class="text-xs font-bold text-royal bg-royal/10 px-3 py-1 rounded-full italic">Required</span>
                        </div>

                        <?php if (!$candidates): ?>
                            <p class="text-sm text-slate-500">No candidates for this position.</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <?php foreach ($candidates as $candidate): ?>
                                    <label class="candidate-card p-6 rounded-[2.5rem] bg-white border-2 border-slate-100 cursor-pointer hover:border-royal/30 transition-all relative group">
                                        <input type="radio" name="votes[<?php echo htmlspecialchars($position['id']); ?>]" value="<?php echo htmlspecialchars($candidate['id']); ?>" class="hidden" <?php echo $alreadyVoted ? 'disabled' : ''; ?>>
                                        <div class="check-icon absolute top-6 right-6 w-8 h-8 bg-royal text-white rounded-full hidden items-center justify-center shadow-lg">
                                            <i class="fa-solid fa-check"></i>
                                        </div>
                                        <div class="flex items-center gap-5">
                                            <div class="w-20 h-20 rounded-3xl bg-slate-100 overflow-hidden border-2 border-white shadow-sm"></div>
                                            <div>
                                                <h3 class="text-lg font-extrabold text-navy group-hover:text-royal transition-colors">
                                                    <?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?>
                                                </h3>
                                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Candidate</p>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($alreadyVoted): ?>
                                <p class="text-xs font-bold text-emerald-600 mt-4">You already voted for this position.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($election && $positionsWithCandidates): ?>
                <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 p-6 z-40">
                    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-center sm:text-left">
                            <p class="text-xs font-bold text-slate-400 uppercase">Selections</p>
                            <p class="text-navy font-extrabold">Complete all positions before submitting.</p>
                        </div>
                        <div class="flex gap-4 w-full sm:w-auto">
                            <button type="submit" class="flex-1 sm:flex-none px-12 py-4 bg-navy text-white rounded-2xl font-bold hover:bg-royal transition-all shadow-xl shadow-navy/20">
                                Submit Vote
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>

    </div>

    <script>
        document.querySelectorAll('.candidate-card').forEach(card => {
            const input = card.querySelector('input[type="radio"]');
            if (!input) return;
            card.addEventListener('click', () => {
                if (input.disabled) return;
                const groupName = input.name;
                document.querySelectorAll(`input[name="${groupName}"]`).forEach(i => {
                    const parent = i.closest('.candidate-card');
                    parent?.classList.remove('selected', 'border-royal');
                    parent?.querySelector('.check-icon')?.classList.add('hidden');
                });
                input.checked = true;
                card.classList.add('selected', 'border-royal');
                card.querySelector('.check-icon')?.classList.remove('hidden');
            });
        });
    </script>
</body>
</html>