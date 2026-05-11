<?php
require_once '../includes/config.php';

/** @var PDO $pdo */
if (!$pdo) {
    die('Database connection failed. Please check your configuration.');
}

requireRole('admin');
$role = 'admin';
$activePage = 'election';
$pageTitle = 'Election Management';

// Schema detection for resilience against different column names
$stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = 'elections'");
$stmt->execute();
$eCols = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
$col_desc = in_array('description', $eCols) ? 'description' : null;
$col_type = in_array('election_type', $eCols) ? 'election_type' : (in_array('type', $eCols) ? 'type' : null);
$col_scope = in_array('scope', $eCols) ? 'scope' : (in_array('assignment', $eCols) ? 'assignment' : null);
$col_start = in_array('starts_at', $eCols) ? 'starts_at' : (in_array('start_date', $eCols) ? 'start_date' : 'created_at');
$col_end = in_array('ends_at', $eCols) ? 'ends_at' : (in_array('end_date', $eCols) ? 'end_date' : 'created_at');
$col_voters = in_array('total_eligible_voters', $eCols) ? 'total_eligible_voters' : null;
$col_is_open = in_array('is_open', $eCols) ? 'is_open' : null;

// Handle Create Election
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_election') {
    try {
        $title = sanitize($_POST['title']);
        $type = sanitize($_POST['type']);
        $assignment = sanitize($_POST['assignment']);
        $description = "{$type} Level - {$assignment}";
        $starts_at = $_POST['starts_at'];
        $ends_at = $_POST['ends_at'];
        $total_voters = (int)($_POST['total_voters'] ?? 0);
        
        // Simple status logic
        $status = 'scheduled';
        $now = new DateTime();
        $start = new DateTime($starts_at);
        $end = new DateTime($ends_at);
        
        if ($now >= $start && $now <= $end) {
            $status = 'active';
        } elseif ($now > $end) {
            $status = 'completed';
        }

        // Build dynamic query based on existing columns
        $fields = ['title', 'status', $col_start, $col_end];
        $params = [$title, $status, $starts_at, $ends_at];
        
        if ($col_desc) { $fields[] = $col_desc; $params[] = $description; }
        if ($col_type) { $fields[] = $col_type; $params[] = $type; }
        if ($col_scope) { $fields[] = $col_scope; $params[] = $assignment; }
        if ($col_voters) { $fields[] = $col_voters; $params[] = $total_voters; }
        if ($col_is_open) { $fields[] = $col_is_open; $params[] = ($status === 'active' ? 1 : 0); }

        $placeholders = array_fill(0, count($fields), '?');
        $stmt = $pdo->prepare("
            INSERT INTO elections (" . implode(', ', $fields) . ") 
            VALUES (" . implode(', ', $placeholders) . ")
        ");
        $stmt->execute($params);

        header("Location: election.php?success=1");
        exit;
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Update Election
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_election') {
    try {
        $id = $_POST['election_id'];
        $title = sanitize($_POST['title']);
        $type = sanitize($_POST['type']);
        $assignment = sanitize($_POST['assignment']);
        $description = "{$type} Level - {$assignment}";
        $starts_at = $_POST['starts_at'];
        $ends_at = $_POST['ends_at'];
        $total_voters = (int)($_POST['total_voters'] ?? 0);
        
        $status = 'scheduled';
        $now = new DateTime();
        $start = new DateTime($starts_at);
        $end = new DateTime($ends_at);
        
        if ($now >= $start && $now <= $end) $status = 'active';
        elseif ($now > $end) $status = 'completed';

        // Build dynamic update
        $updates = ["title = ?", "status = ?", "{$col_start} = ?", "{$col_end} = ?"];
        $params = [$title, $status, $starts_at, $ends_at];

        if ($col_desc) { $updates[] = "{$col_desc} = ?"; $params[] = $description; }
        if ($col_type) { $updates[] = "{$col_type} = ?"; $params[] = $type; }
        if ($col_scope) { $updates[] = "{$col_scope} = ?"; $params[] = $assignment; }
        if ($col_voters) { $updates[] = "{$col_voters} = ?"; $params[] = $total_voters; }
        if ($col_is_open) { $updates[] = "{$col_is_open} = ?"; $params[] = ($status === 'active' ? 1 : 0); }
        
        $params[] = $id;
        $stmt = $pdo->prepare("
            UPDATE elections 
            SET " . implode(', ', $updates) . " 
            WHERE id = ?
        ");
        $stmt->execute($params);

        header("Location: election.php?success=updated");
        exit;
    } catch (Exception $e) { $error = "Error: " . $e->getMessage(); }
}

// Handle Delete Election
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_election') {
    try {
        $id = $_POST['election_id'];
        $stmt = $pdo->prepare("DELETE FROM elections WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: election.php?success=deleted");
        exit;
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch Elections
$elections = [];
try {
    $stmt = $pdo->query("
        SELECT e.*, COUNT(c.id) as candidate_count 
        FROM elections e 
        LEFT JOIN candidates c ON e.id = c.election_id 
        GROUP BY e.id 
        ORDER BY e.created_at DESC
    ");
    $elections = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Fetch elections error: " . $e->getMessage());
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
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-black text-navy">Elections</h1>
                    <p class="text-slate-500 text-sm font-medium mt-1">Initialize and manage university-wide or department-level polls.</p>
                </div>
                <button onclick="showCreateModal()" class="bg-gold text-navy px-6 py-3 rounded-xl font-bold hover:scale-105 transition shadow-lg shadow-gold/20">
                    <i class="fa-solid fa-plus mr-2"></i>ADD ELECTION
                </button>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl font-bold text-sm">
                    <i class="fa-solid fa-circle-check mr-2"></i> 
                    <?php 
                        if($_GET['success'] == 'updated') echo "Election updated successfully!";
                        elseif($_GET['success'] == 'deleted') echo "Election deleted successfully!";
                        else echo "Election created successfully!";
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl font-bold text-sm">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Active Elections Grid -->
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6">Running / Scheduled</h3>
            <div class="grid md:grid-cols-2 gap-8">
                <?php if (empty($elections)): ?>
                    <div class="col-span-2 py-20 text-center bg-white rounded-[3rem] border border-dashed border-slate-200">
                        <p class="text-slate-400 font-bold">No elections found. Click "Add Election" to create one.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($elections as $election): ?>
                        <?php 
                            $status = strtolower($election['status']);
                            $statusClass = match($status) {
                                'active' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                'completed' => 'text-slate-600 bg-slate-50 border-slate-100',
                                default => 'text-amber-600 bg-amber-50 border-amber-100',
                            };

                            $desc = $col_desc ? ($election[$col_desc] ?? '') : '';
                            $startVal = $election[$col_start] ?? '';
                            $endVal = $election[$col_end] ?? '';
                            $scopeVal = $col_scope ? ($election[$col_scope] ?? '') : '';

                            // Parse description to extract type and assignment for editing
                            $descParts = explode(' Level - ', (string)$desc);
                            $eType = $col_type ? ($election[$col_type] ?? 'University') : ($descParts[0] ?? 'University');
                            $eAssignment = $col_scope ? ($election[$col_scope] ?? 'All Students') : ($descParts[1] ?? 'All Students');
                        ?>
                        <div data-admin-search-item class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-8">
                                <span class="text-[10px] font-black px-4 py-2 rounded-full uppercase border <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($election['status']); ?>
                                </span>
                            </div>
                            
                            <div class="w-16 h-16 bg-blue-50 text-royal rounded-3xl flex items-center justify-center text-2xl mb-6">
                                <i class="fa-solid fa-landmark-flag"></i>
                            </div>

                            <h4 class="text-2xl font-black text-navy mb-2 uppercase"><?php echo htmlspecialchars($election['title']); ?></h4>
                            <p class="text-slate-400 text-sm font-medium mb-8"><?php echo htmlspecialchars((string)($desc ?: $scopeVal)); ?></p>

                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <div class="p-4 bg-slate-50 rounded-2xl">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Start Date</p>
                                    <p class="text-xs font-bold text-navy"><?php echo $startVal ? date('M d, h:i A', strtotime($startVal)) : 'TBA'; ?></p>
                                </div>
                                <div class="p-4 bg-slate-50 rounded-2xl">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">End Date</p>
                                    <p class="text-xs font-bold text-navy"><?php echo $endVal ? date('M d, h:i A', strtotime($endVal)) : 'TBA'; ?></p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between pt-6 border-t border-slate-50 gap-4">
                                <div class="flex gap-2">
                                    <button onclick='showEditModal(<?php echo json_encode(["id"=>$election["id"], "title"=>$election["title"], "type"=>$eType, "assignment"=>$eAssignment, "start"=>$startVal, "end"=>$endVal, "voters"=>$election["total_eligible_voters"] ?? 0]); ?>)' class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-royal transition flex items-center justify-center">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button onclick="confirmDelete(<?php echo $election['id']; ?>)" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 transition flex items-center justify-center">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase"><?php echo number_format($election['total_eligible_voters'] ?? 0); ?> Voters</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Create Election Modal -->
    <div id="createModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden bg-navy/60 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-xl rounded-[3rem] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
            <div class="p-8 border-b flex justify-between items-center bg-slate-50">
                <h3 id="modalHeader" class="text-xl font-black text-navy">Initialize New Election</h3>
                <button onclick="closeCreateModal()" class="w-10 h-10 rounded-full hover:bg-slate-200 transition"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="electionForm" class="p-8 space-y-6" method="POST">
                <input type="hidden" name="action" id="formAction" value="create_election">
                <input type="hidden" name="election_id" id="electionIdField">
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">Election Title</label>
                    <input type="text" name="title" required placeholder="e.g. Codebyters Program Election 2026" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">Type</label>
                        <select name="type" id="typeSelect" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                            <option>University</option>
                            <option>Faculty</option>
                            <option>Program</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">Assignment</label>
                        <select name="assignment" id="assignmentSelect" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                            <option value="All Students">All Students</option>
                            <option>FACET</option>
                            <option>FALS</option>
                            <option>FTED</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">Start Time</label>
                        <input type="datetime-local" name="starts_at" id="startField" required class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">End Time</label>
                        <input type="datetime-local" name="ends_at" id="endField" required class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">Total Eligible Voters</label>
                    <input type="number" name="total_voters" id="votersField" placeholder="0" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                </div>

                <div class="pt-4 flex gap-4">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 py-4 text-slate-400 font-black rounded-2xl hover:text-navy transition">CANCEL</button>
                    <button type="submit" id="submitBtn" class="flex-1 py-4 bg-navy text-white font-black rounded-2xl shadow-xl shadow-navy/20 hover:bg-royal transition">CREATE ELECTION</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete_election">
        <input type="hidden" name="election_id" id="deleteIdField">
    </form>

    <script>
        function showCreateModal() { 
            document.getElementById('modalHeader').textContent = "Initialize New Election";
            document.getElementById('submitBtn').textContent = "CREATE ELECTION";
            document.getElementById('formAction').value = "create_election";
            document.getElementById('electionForm').reset();
            document.getElementById('createModal').classList.remove('hidden'); 
        }

        function showEditModal(data) {
            document.getElementById('modalHeader').textContent = "Edit Election Details";
            document.getElementById('submitBtn').textContent = "UPDATE ELECTION";
            document.getElementById('formAction').value = "update_election";
            
            document.getElementById('electionIdField').value = data.id;
            document.querySelector('input[name="title"]').value = data.title;
            document.getElementById('typeSelect').value = data.type;
            document.getElementById('assignmentSelect').value = data.assignment;
            document.getElementById('votersField').value = data.voters;
            
            // Format dates for datetime-local input
            const formatDate = (dateStr) => {
                const d = new Date(dateStr);
                return d.toISOString().slice(0, 16);
            };
            
            document.getElementById('startField').value = formatDate(data.start);
            document.getElementById('endField').value = formatDate(data.end);
            
            document.getElementById('createModal').classList.remove('hidden');
        }

        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this election? All associated candidates and data will be removed. This action cannot be undone.')) {
                document.getElementById('deleteIdField').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function closeCreateModal() { document.getElementById('createModal').classList.add('hidden'); }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>