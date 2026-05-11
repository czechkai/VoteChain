<?php
require_once '../includes/config.php';
requireRole('admin');
$role = 'admin';
$activePage = 'candidate';
$pageTitle = 'Candidate Applications';

function adminCandidateTableExists($pdo, $tableName) {
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = CURRENT_SCHEMA() AND table_name = ? LIMIT 1");
        $stmt->execute([$tableName]);
        return $stmt->fetchColumn() !== false;
    } catch (Throwable $e) {
        error_log('Admin candidate table check error: ' . $e->getMessage());
        return false;
    }
}

function adminCandidateTableColumns($pdo, $tableName) {
    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = ?");
        $stmt->execute([$tableName]);
        return array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
    } catch (Throwable $e) {
        error_log('Admin candidate column check error: ' . $e->getMessage());
        return [];
    }
}

$candidateTableExists = adminCandidateTableExists($pdo, 'candidates');
$candidateTableColumns = adminCandidateTableColumns($pdo, 'candidates');
$candidateStatusColumn = in_array('status', $candidateTableColumns, true)
    ? 'status'
    : (in_array('filing_status', $candidateTableColumns, true) ? 'filing_status' : null);
$candidateIdColumn = in_array('profile_id', $candidateTableColumns, true)
    ? 'profile_id'
    : (in_array('user_id', $candidateTableColumns, true) ? 'user_id' : null);
$candidateUpdatedAtColumn = in_array('updated_at', $candidateTableColumns, true) ? 'updated_at' : null;
$profilesTableExists = adminCandidateTableExists($pdo, 'profiles');
$usersTableExists = adminCandidateTableExists($pdo, 'users');
$positionsTableExists = adminCandidateTableExists($pdo, 'positions');
$candidateDocumentsTableExists = adminCandidateTableExists($pdo, 'candidate_documents') || adminCandidateTableExists($pdo, 'candidacy_filings');

$applicationNotice = '';
$applicationNoticeType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_action'], $_POST['candidate_id'])) {
    if (!$pdo || !$candidateTableExists || !$candidateStatusColumn) {
        $applicationNotice = 'Unable to update the application status right now.';
        $applicationNoticeType = 'error';
    } else {
        $candidateId = trim((string) $_POST['candidate_id']);
        $candidateAction = strtolower(trim((string) $_POST['candidate_action']));
        $nextStatus = $candidateAction === 'approve' ? 'approved' : ($candidateAction === 'reject' ? 'rejected' : null);

        if (!$candidateId || !$nextStatus) {
            $applicationNotice = 'Please choose a valid application action.';
            $applicationNoticeType = 'error';
        } else {
            try {
                $setParts = ["{$candidateStatusColumn} = ?"];
                if ($candidateUpdatedAtColumn) {
                    $setParts[] = "{$candidateUpdatedAtColumn} = NOW()";
                }

                $updateStmt = $pdo->prepare('UPDATE candidates SET ' . implode(', ', $setParts) . ' WHERE id = ?');
                $updateStmt->execute(array_merge([$nextStatus], [$candidateId]));
                header('Location: candidate.php?updated=' . urlencode($nextStatus));
                exit;
            } catch (Throwable $e) {
                error_log('Admin candidate status update error: ' . $e->getMessage());
                $applicationNotice = 'Failed to update the application.';
                $applicationNoticeType = 'error';
            }
        }
    }
}

$candidateApplications = [];
$candidateDocuments = [];
$positionOptions = [];
$candidateStats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
];

if ($pdo && $candidateTableExists) {
    try {
        $selectParts = [
            'c.id',
            'c.created_at',
        ];

        if ($candidateStatusColumn) {
            $selectParts[] = 'c.' . $candidateStatusColumn . ' AS status';
        } else {
            $selectParts[] = "'pending' AS status";
        }

        // Add image URL if column exists
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

        if ($candidateIdColumn === 'profile_id' && $profilesTableExists) {
            $selectParts[] = 'p.first_name';
            $selectParts[] = 'p.last_name';
            $selectParts[] = 'p.program_code';
            $selectParts[] = 'p.year_level';
            $selectParts[] = 'p.student_id';
        } elseif ($candidateIdColumn === 'user_id' && $usersTableExists) {
            $selectParts[] = 'u.first_name';
            $selectParts[] = 'u.last_name';
            $selectParts[] = 'u.student_id';
            $selectParts[] = 'u.year_level';
        }

        if ($positionsTableExists && in_array('position_id', $candidateTableColumns, true)) {
            $selectParts[] = 'pos.name AS position_name';
        }

        $joinSql = '';
        if ($candidateIdColumn === 'profile_id' && $profilesTableExists) {
            $joinSql .= ' LEFT JOIN profiles p ON p.id = c.profile_id';
        } elseif ($candidateIdColumn === 'user_id' && $usersTableExists) {
            $joinSql .= ' LEFT JOIN users u ON u.id = c.user_id';
        }

        if ($positionsTableExists && in_array('position_id', $candidateTableColumns, true)) {
            $joinSql .= ' LEFT JOIN positions pos ON pos.id = c.position_id';
        }

        $candidateStmt = $pdo->query('SELECT ' . implode(', ', $selectParts) . ' FROM candidates c' . $joinSql . ' ORDER BY c.created_at DESC');
        $candidateApplications = $candidateStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $candidateStats['total'] = count($candidateApplications);
        foreach ($candidateApplications as $candidateRow) {
            $currentStatus = strtolower((string) ($candidateRow['status'] ?? 'pending'));
            if (isset($candidateStats[$currentStatus])) {
                $candidateStats[$currentStatus]++;
            }
        }

        if ($candidateDocumentsTableExists && !empty($candidateApplications)) {
            $candidateIds = array_values(array_filter(array_column($candidateApplications, 'id')));
            if (!empty($candidateIds)) {
                $placeholders = implode(',', array_fill(0, count($candidateIds), '?'));
                $documentTable = adminCandidateTableExists($pdo, 'candidate_documents') ? 'candidate_documents' : 'candidacy_filings';
                $documentNameColumn = $documentTable === 'candidate_documents' ? 'document_name' : 'document_type';
                $documentTableColumns = adminCandidateTableColumns($pdo, $documentTable);
                $documentOrderColumn = in_array('uploaded_at', $documentTableColumns, true)
                    ? 'uploaded_at'
                    : (in_array('created_at', $documentTableColumns, true) ? 'created_at' : 'candidate_id');
                $documentStmt = $pdo->prepare("SELECT candidate_id, {$documentNameColumn} AS document_name, document_url FROM {$documentTable} WHERE candidate_id IN ({$placeholders}) ORDER BY {$documentOrderColumn} ASC");
                $documentStmt->execute($candidateIds);

                while ($documentRow = $documentStmt->fetch(PDO::FETCH_ASSOC)) {
                    $candidateDocuments[$documentRow['candidate_id']][] = $documentRow;
                }
            }
        }

        if ($positionsTableExists) {
            $positionStmt = $pdo->query('SELECT id, name FROM positions ORDER BY name ASC');
            $positionOptions = $positionStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (Throwable $e) {
        error_log('Admin candidate list error: ' . $e->getMessage());
    }
}

$candidateApplicationsForJs = array_map(static function ($candidate) use ($candidateDocuments) {
    $candidateId = (string) ($candidate['id'] ?? '');
    $documents = [];

    if (!empty($candidateDocuments[$candidateId])) {
        foreach ($candidateDocuments[$candidateId] as $document) {
            $documents[] = [
                'name' => (string) ($document['document_name'] ?? 'Document'),
                'url' => (string) ($document['document_url'] ?? ''),
            ];
        }
    }

    $firstName = trim((string) ($candidate['first_name'] ?? ''));
    $lastName = trim((string) ($candidate['last_name'] ?? ''));
    $fullName = trim($firstName . ' ' . $lastName) ?: 'Unknown Candidate';
    $yearLevel = trim((string) ($candidate['year_level'] ?? ''));
    $studentId = trim((string) ($candidate['student_id'] ?? ''));
    $programCode = trim((string) ($candidate['program_code'] ?? ''));
    $identity = $programCode !== '' ? $programCode : $studentId;

    return [
        'id' => $candidateId,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'fullName' => $fullName,
        'positionName' => (string) ($candidate['position_name'] ?? 'Unassigned Position'),
        'status' => strtolower((string) ($candidate['status'] ?? 'pending')),
        'submittedAt' => !empty($candidate['created_at']) ? date('M d, Y h:i A', strtotime((string) $candidate['created_at'])) : 'Not available',
        'identity' => trim($identity . ($yearLevel !== '' ? ' - ' . $yearLevel : '')),
        'imageUrl' => (string) ($candidate['image_url'] ?? ''),
        'documents' => $documents,
    ];
}, $candidateApplications);

$statusFilterOptions = ['pending', 'approved', 'rejected'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        <main class="p-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-navy">Candidate Applications</h1>
                    <p class="text-slate-500 text-sm font-medium mt-1">Review submitted filings and update the live application queue.</p>
                </div>
                <div class="flex items-center gap-3 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-amber-700">
                    <i class="fa-solid fa-signal"></i>
                    <span class="text-xs font-black uppercase tracking-widest">Live Pending</span>
                    <span id="liveCandidatePendingCount" class="text-lg font-black"><?php echo (int) $candidateStats['pending']; ?></span>
                </div>
            </div>

            <?php if (isset($_GET['updated'])): ?>
                <div class="mb-6 rounded-2xl border px-4 py-3 text-sm font-bold <?php echo $_GET['updated'] === 'approved' ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-rose-100 bg-rose-50 text-rose-700'; ?>">
                    Candidate application status updated to <?php echo htmlspecialchars(ucfirst((string) $_GET['updated'])); ?>.
                </div>
            <?php elseif ($applicationNotice): ?>
                <div class="mb-6 rounded-2xl border px-4 py-3 text-sm font-bold <?php echo $applicationNoticeType === 'success' ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-rose-100 bg-rose-50 text-rose-700'; ?>">
                    <?php echo htmlspecialchars($applicationNotice); ?>
                </div>
            <?php endif; ?>

            <!-- Filter Bar -->
            <div class="bg-white p-4 rounded-[2rem] shadow-sm border border-slate-100 mb-8 flex flex-wrap gap-4 items-center">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input id="candidateSearchInput" type="text" placeholder="Search by name or position..." class="w-full pl-10 pr-4 py-3 bg-slate-50 rounded-2xl border-none text-sm outline-none">
                </div>
                <select id="candidateStatusFilter" class="bg-slate-50 text-sm font-bold border-none rounded-2xl px-6 py-3 outline-none">
                    <option value="">All Status</option>
                    <?php foreach ($statusFilterOptions as $statusOption): ?>
                        <option value="<?php echo htmlspecialchars($statusOption); ?>"><?php echo htmlspecialchars(ucfirst($statusOption)); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="candidatePositionFilter" class="bg-slate-50 text-sm font-bold border-none rounded-2xl px-6 py-3 outline-none">
                    <option value="">All Positions</option>
                    <?php foreach ($positionOptions as $positionOption): ?>
                        <option value="<?php echo htmlspecialchars(strtolower((string) ($positionOption['name'] ?? ''))); ?>"><?php echo htmlspecialchars((string) ($positionOption['name'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Table-Card Hybrid -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Candidate Info</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Position</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Documents</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (!empty($candidateApplicationsForJs)): ?>
                            <?php foreach ($candidateApplicationsForJs as $index => $candidate): ?>
                                <?php
                                    $status = (string) ($candidate['status'] ?? 'pending');
                                    if ($status === 'approved') {
                                        $statusLabel = 'Approved';
                                        $statusClass = 'text-emerald-600 bg-emerald-50 border-emerald-100';
                                    } elseif ($status === 'rejected') {
                                        $statusLabel = 'Rejected';
                                        $statusClass = 'text-rose-600 bg-rose-50 border-rose-100';
                                    } else {
                                        $statusLabel = 'Pending';
                                        $statusClass = 'text-amber-600 bg-amber-50 border-amber-100';
                                    }

                                    $documentCount = count($candidate['documents']);
                                    $identity = trim((string) ($candidate['identity'] ?? ''));
                                    $identityLabel = $identity !== '' ? $identity : 'No program information';
                                ?>
                                <tr data-admin-search-item class="hover:bg-slate-50/50 transition" data-status="<?php echo htmlspecialchars($status); ?>" data-position="<?php echo htmlspecialchars(strtolower((string) ($candidate['positionName'] ?? ''))); ?>">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-navy text-white flex items-center justify-center font-black overflow-hidden flex-shrink-0">
                                                <?php if (!empty($candidate['imageUrl'])): ?>
                                                    <img src="<?php echo htmlspecialchars((string) ('../' . $candidate['imageUrl'] ?? '')); ?>" alt="<?php echo htmlspecialchars((string) ($candidate['fullName'] ?? '')); ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars(getCandidateInitials($candidate['firstName'] ?? '', $candidate['lastName'] ?? '', 'U')); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-navy"><?php echo htmlspecialchars((string) ($candidate['fullName'] ?? 'Unknown Candidate')); ?></p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase"><?php echo htmlspecialchars($identityLabel); ?></p>
                                                <p class="text-[10px] font-bold text-slate-300 uppercase mt-1">Submitted <?php echo htmlspecialchars((string) ($candidate['submittedAt'] ?? '')); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-xs font-black text-royal bg-blue-50 px-3 py-1.5 rounded-lg uppercase"><?php echo htmlspecialchars((string) ($candidate['positionName'] ?? 'Unassigned Position')); ?></span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-xs font-black text-slate-600 bg-slate-100 px-3 py-1.5 rounded-full uppercase border border-slate-200"><?php echo htmlspecialchars((string) $documentCount); ?> file<?php echo $documentCount === 1 ? '' : 's'; ?> submitted</span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-[10px] font-black px-3 py-1.5 rounded-full uppercase border <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <button type="button" onclick="openApplicationModal(<?php echo (int) $index; ?>)" class="w-10 h-10 rounded-xl bg-slate-100 text-navy hover:bg-navy hover:text-white transition">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr data-admin-search-item class="hover:bg-slate-50/50 transition">
                                <td colspan="5" class="px-8 py-10 text-center text-slate-500">No candidate applications submitted yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden bg-navy/60 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-2xl rounded-[3rem] overflow-hidden animate-in zoom-in-95 duration-200">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-black text-navy">Review Application</h3>
                <button onclick="closeDetailsModal()" class="w-10 h-10 rounded-full hover:bg-slate-200 transition"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-8 space-y-8 max-h-[70vh] overflow-y-auto">
                <div class="flex gap-6 items-center">
                    <div class="w-24 h-24 rounded-[2rem] bg-gradient-to-br from-blue-500 to-navy text-white border-4 border-white shadow-xl overflow-hidden flex items-center justify-center font-black text-2xl" id="modalAvatar">CA</div>
                    <div>
                        <h4 class="text-2xl font-black text-navy" id="modalCandidateName">Candidate Name</h4>
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest" id="modalCandidatePosition">Candidate Position</p>
                        <p class="text-xs font-bold text-slate-300 uppercase mt-1" id="modalCandidateSubmitted">Submitted Date</p>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 space-y-3">
                    <h5 class="text-xs font-black text-royal uppercase tracking-widest mb-2">Submitted Documents</h5>
                    <div id="modalDocuments" class="grid grid-cols-1 gap-3"></div>
                </div>

                <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100">
                    <h5 class="text-xs font-black text-royal uppercase tracking-widest mb-2">Admin Notes</h5>
                    <textarea id="adminNotes" class="w-full bg-white border-none rounded-2xl p-4 text-sm outline-none focus:ring-2 focus:ring-royal" placeholder="Add comments for rejection or internal notes..."></textarea>
                </div>
            </div>
            <div class="p-8 bg-slate-50 grid grid-cols-2 gap-4">
                <form method="POST" class="contents">
                    <input type="hidden" name="candidate_id" id="modalCandidateId">
                    <input type="hidden" name="candidate_action" value="reject">
                    <button type="submit" class="py-4 bg-red-50 text-red-500 font-black rounded-2xl border border-red-100 hover:bg-red-100 transition">REJECT CANDIDATE</button>
                </form>
                <form method="POST" class="contents">
                    <input type="hidden" name="candidate_id" id="modalApproveCandidateId">
                    <input type="hidden" name="candidate_action" value="approve">
                    <button type="submit" class="py-4 bg-navy text-white font-black rounded-2xl shadow-xl shadow-navy/20 hover:bg-royal transition">APPROVE CANDIDATE</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const candidateApplications = <?php echo json_encode($candidateApplicationsForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('candidateSearchInput');
            const statusFilter = document.getElementById('candidateStatusFilter');
            const positionFilter = document.getElementById('candidatePositionFilter');
            const rows = document.querySelectorAll('tr[data-admin-search-item]');

            if (!searchInput || !statusFilter || !positionFilter || !rows.length) {
                return;
            }

            const applyFilters = () => {
                const searchTerm = searchInput.value.trim().toLowerCase();
                const selectedStatus = statusFilter.value;
                const selectedPosition = positionFilter.value;

                rows.forEach((row) => {
                    const rowText = row.textContent.toLowerCase();
                    const rowStatus = row.dataset.status || row.querySelector('td:nth-child(4) span')?.textContent.toLowerCase() || '';
                    const rowPosition = row.dataset.position || row.querySelector('td:nth-child(2) span')?.textContent.toLowerCase() || '';

                    const matchesSearch = !searchTerm || rowText.includes(searchTerm);
                    const matchesStatus = !selectedStatus || rowStatus.includes(selectedStatus);
                    const matchesPosition = !selectedPosition || rowPosition.includes(selectedPosition);

                    row.style.display = matchesSearch && matchesStatus && matchesPosition ? '' : 'none';
                });
            };

            searchInput.addEventListener('input', applyFilters);
            statusFilter.addEventListener('change', applyFilters);
            positionFilter.addEventListener('change', applyFilters);
        });

        function openApplicationModal(index) {
            const candidate = candidateApplications[index];
            if (!candidate) {
                return;
            }

            document.getElementById('modalCandidateName').textContent = candidate.fullName || 'Candidate Name';
            document.getElementById('modalCandidatePosition').textContent = candidate.positionName ? `Candidate for ${candidate.positionName}` : 'Candidate Application';
            document.getElementById('modalCandidateSubmitted').textContent = candidate.submittedAt ? `Submitted ${candidate.submittedAt}` : 'Submitted Date';
            document.getElementById('modalCandidateId').value = candidate.id || '';
            document.getElementById('modalApproveCandidateId').value = candidate.id || '';

            // Display profile image if available, otherwise show initials
            const avatarElement = document.getElementById('modalAvatar');
            const imageUrl = (candidate.imageUrl || '').trim();
            
            if (imageUrl && imageUrl.length > 0) {
                avatarElement.innerHTML = `<img src="../${imageUrl}" alt="${candidate.fullName}" class="w-full h-full object-cover">`;
                avatarElement.style.backgroundColor = '';
                avatarElement.style.backgroundImage = '';
            } else {
                const avatarLabel = (candidate.fullName || 'CA')
                    .split(' ')
                    .filter(Boolean)
                    .slice(0, 2)
                    .map((part) => part.charAt(0).toUpperCase())
                    .join('') || 'CA';
                avatarElement.textContent = avatarLabel;
            }

            const documentsContainer = document.getElementById('modalDocuments');
            documentsContainer.innerHTML = '';

            if (candidate.documents && candidate.documents.length) {
                candidate.documents.forEach((documentItem) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'p-4 bg-white rounded-2xl border border-slate-100 flex justify-between items-center gap-4';

                    const title = document.createElement('div');
                    const name = document.createElement('span');
                    name.className = 'block text-sm font-bold text-navy';
                    name.textContent = documentItem.name || 'Document';
                    title.appendChild(name);

                    const link = document.createElement('a');
                    link.className = 'text-royal font-black text-[10px] hover:underline shrink-0';
                    link.textContent = 'VIEW FILE';
                    link.href = documentItem.url ? ('download.php?file=' + encodeURIComponent(documentItem.url)) : '#';
                    link.target = documentItem.url ? '_blank' : '_self';
                    link.rel = 'noreferrer';

                    wrapper.appendChild(title);
                    wrapper.appendChild(link);
                    documentsContainer.appendChild(wrapper);
                });
            } else {
                const emptyState = document.createElement('div');
                emptyState.className = 'p-4 bg-white rounded-2xl border border-slate-100 text-sm text-slate-500';
                emptyState.textContent = 'No uploaded documents were found for this application.';
                documentsContainer.appendChild(emptyState);
            }

            document.getElementById('detailsModal').classList.remove('hidden');
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }

        async function refreshLiveCandidateCount() {
            try {
                const response = await fetch('dashboard.php?live_stats=1', { headers: { 'Accept': 'application/json' } });
                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                const pendingCount = Number.parseInt(data.pending_review ?? data.pending ?? 0, 10);
                const countEl = document.getElementById('liveCandidatePendingCount');
                if (countEl && Number.isFinite(pendingCount)) {
                    countEl.textContent = String(pendingCount);
                }
            } catch (error) {
                // Ignore transient polling failures.
            }
        }

        refreshLiveCandidateCount();
        setInterval(refreshLiveCandidateCount, 15000);
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>