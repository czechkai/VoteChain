<?php
require_once '../includes/config.php';
requireRole('admin');
$role = 'admin';
$activePage = 'announcements';
$pageTitle = 'Announcements';

/** @var PDO $pdo */
if (!$pdo) {
    die('Database connection failed. Please check your configuration.');
}

// Improved schema detection for resilience - adding schema filters for Supabase/PostgreSQL stability
$stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = 'announcements' AND (table_schema = 'public' OR table_schema = CURRENT_SCHEMA())");
$stmt->execute();
$aCols = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));

$col_content = in_array('body', $aCols) ? 'body' : (in_array('content', $aCols) ? 'content' : (in_array('description', $aCols) ? 'description' : 'body'));
$col_publish = in_array('publish_date', $aCols) ? 'publish_date' : (in_array('published_at', $aCols) ? 'published_at' : (in_array('created_at', $aCols) ? 'created_at' : null));
$col_author = in_array('author', $aCols) ? 'author' : null;
$col_scope = in_array('scope', $aCols) ? 'scope' : null;

// Handle Create Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_announcement') {
    try {
        $title = sanitize($_POST['title']);
        $content = $_POST['content'] ?? $_POST['body'] ?? ''; // Handle both common names
        $status = sanitize($_POST['status']);
        $publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d');

        // Build dynamic query based on existing columns
        $fields = ['title', 'status', $col_content];
        $params = [$title, $status, $content];
        
        if ($col_publish) { $fields[] = $col_publish; $params[] = $publish_date; }
        if ($col_author) { $fields[] = $col_author; $params[] = $_SESSION['first_name'] ?? 'Admin'; }
        if ($col_scope) { $fields[] = $col_scope; $params[] = 'general'; }

        $placeholders = array_fill(0, count($fields), '?');
        $stmt = $pdo->prepare("
            INSERT INTO announcements (" . implode(', ', $fields) . ") 
            VALUES (" . implode(', ', $placeholders) . ")
        ");
        $stmt->execute($params);

        header("Location: announcements.php?success=1");
        exit;
    } catch (Exception $e) {
        $error = "Failed to create announcement: " . $e->getMessage();
    }
}

// Handle Update Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_announcement') {
    try {
        $id = $_POST['announcement_id'];
        $title = sanitize($_POST['title']);
        $content = $_POST['content'] ?? $_POST['body'] ?? '';
        $status = sanitize($_POST['status']);
        $publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d');

        $updates = ["title = ?", "status = ?", "$col_content = ?"];
        $params = [$title, $status, $content];
        if ($col_publish) { $updates[] = "$col_publish = ?"; $params[] = $publish_date; }
        
        $params[] = $id;
        $stmt = $pdo->prepare("UPDATE announcements SET " . implode(', ', $updates) . " WHERE id = ?");
        $stmt->execute($params);

        header("Location: announcements.php?success=updated");
        exit;
    } catch (Exception $e) {
        $error = "Failed to update announcement: " . $e->getMessage();
    }
}

// Handle Delete Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_announcement') {
    try {
        $id = $_POST['announcement_id'];
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: announcements.php?success=deleted");
        exit;
    } catch (Exception $e) {
        $error = "Failed to delete announcement: " . $e->getMessage();
    }
}

// Fetch Announcements
$announcements = [];
try {
    $orderCol = in_array('created_at', $aCols) ? 'created_at' : (in_array('id', $aCols) ? 'id' : null);
    $orderBy = $orderCol ? "ORDER BY $orderCol DESC" : "";
    
    $stmt = $pdo->query("SELECT * FROM announcements $orderBy");
    $announcements = $stmt->fetchAll();
} catch (Exception $e) {
    // Table might not exist yet; the UI will show an empty state
    error_log("Error fetching announcements: " . $e->getMessage());
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

        <main class="p-8 flex-1">
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl font-bold text-sm">
                    <i class="fa-solid fa-circle-check mr-2"></i> 
                    <?php 
                        if($_GET['success'] === 'updated') echo "Announcement updated successfully!";
                        elseif($_GET['success'] === 'deleted') echo "Announcement removed successfully!";
                        else echo "Announcement published successfully!";
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl font-bold text-sm">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-black text-navy">Announcements</h1>
                <button onclick="openCreateModal()" class="bg-gold text-navy px-6 py-3 rounded-xl font-bold hover:scale-105 transition">
                    <i class="fa-solid fa-plus mr-2"></i>New Announcement
                </button>
            </div>

            <!-- Announcements Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                <?php if (empty($announcements)): ?>
                    <div class="col-span-full py-20 text-center bg-white rounded-[3rem] border border-dashed border-slate-200">
                        <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No announcements found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $item): ?>
                        <?php 
                            $status = strtolower($item['status']);
                            $statusClass = match($status) {
                                'active' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                                'scheduled' => 'text-amber-600 bg-amber-50 border-amber-200',
                                'archived' => 'text-slate-600 bg-slate-50 border-slate-200',
                                default => 'text-slate-600 bg-slate-50 border-slate-200'
                            };
                        ?>
                        <div data-admin-search-item class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-md transition-all flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-[10px] font-black px-3 py-1 rounded-full uppercase border <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($item['status']); ?>
                                        </span>
                                        <span class="text-[10px] text-slate-500"><?php echo date('M d, Y', strtotime($item['created_at'])); ?></span>
                                    </div>
                                    <h3 class="text-lg font-bold text-navy"><?php echo htmlspecialchars($item['title']); ?></h3>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick='openEditModal(<?php echo json_encode(["id"=>$item["id"], "title"=>$item["title"], "content"=>$item[$col_content] ?? "", "status"=>$item["status"], "date"=>$item[$col_publish] ?? ""]); ?>)' class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-royal transition flex items-center justify-center">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button onclick="confirmDelete('<?php echo $item['id']; ?>')" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 transition flex items-center justify-center">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            <?php 
                                $annBody = $col_content ? ($item[$col_content] ?? '') : '';
                                $createdCol = in_array('created_at', $aCols) ? 'created_at' : $col_publish;
                                $annDate = $createdCol && isset($item[$createdCol]) ? $item[$createdCol] : date('Y-m-d');
                            ?>
                            <p class="text-slate-600 text-sm mb-4 line-clamp-3 flex-1"><?php echo htmlspecialchars((string)$annBody); ?></p>
                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2 text-slate-500 text-xs">
                                    <i class="fa-solid fa-eye"></i>
                                    <span><?php echo number_format((int)($item['views'] ?? 0)); ?> views</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center items-center gap-2 mt-12">
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition disabled:opacity-50" disabled>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="w-10 h-10 rounded-lg bg-navy text-white font-bold">1</button>
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">2</button>
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">3</button>
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </main>
    </div>

    <!-- Create/Edit Announcement Modal -->
    <div id="createModal" class="fixed inset-0 z-[60] hidden bg-navy/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-[2rem] overflow-hidden animate-in zoom-in-95 duration-200">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 id="modalTitle" class="text-2xl font-black text-navy">Create Announcement</h3>
                <button onclick="closeCreateModal()" class="w-10 h-10 rounded-full hover:bg-slate-200 transition text-navy">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="announcementForm" class="p-8 space-y-6" method="POST">
                <input type="hidden" name="action" id="formAction" value="create_announcement">
                <input type="hidden" name="announcement_id" id="announcementId">
                
                <div>
                    <label class="block text-sm font-bold text-navy mb-2">Title</label>
                    <input type="text" name="title" id="inputTitle" required placeholder="Announcement title..." class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-gold focus:bg-slate-50 transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-navy mb-2">Content</label>
                    <textarea name="content" id="inputContent" required rows="6" placeholder="Announcement content..." class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-gold focus:bg-slate-50 transition resize-none"></textarea>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Status</label>
                        <select name="status" id="inputStatus" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-gold focus:bg-slate-50 transition">
                            <option value="Draft">Draft</option>
                            <option value="Active" selected>Active</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Publish Date</label>
                        <input type="date" name="publish_date" id="inputDate" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-gold focus:bg-slate-50 transition">
                    </div>
                </div>

                <div class="flex gap-4 justify-end pt-6 border-t border-slate-100">
                    <button type="button" onclick="closeCreateModal()" class="px-6 py-3 bg-slate-100 text-navy rounded-xl font-bold hover:bg-slate-200 transition">Cancel</button>
                    <button type="submit" id="submitBtn" class="px-6 py-3 bg-navy text-white rounded-xl font-bold hover:bg-royal transition">Publish Announcement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete_announcement">
        <input type="hidden" name="announcement_id" id="deleteId">
    </form>

    <script>
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = "Create Announcement";
            document.getElementById('submitBtn').textContent = "Publish Announcement";
            document.getElementById('formAction').value = "create_announcement";
            document.getElementById('announcementForm').reset();
            document.getElementById('createModal').classList.remove('hidden');
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').textContent = "Edit Announcement";
            document.getElementById('submitBtn').textContent = "Update Announcement";
            document.getElementById('formAction').value = "update_announcement";
            
            document.getElementById('announcementId').value = data.id;
            document.getElementById('inputTitle').value = data.title;
            document.getElementById('inputContent').value = data.content;
            document.getElementById('inputStatus').value = data.status;
            document.getElementById('inputDate').value = data.date ? data.date.split(' ')[0] : '';
            
            document.getElementById('createModal').classList.remove('hidden');
        }

        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
