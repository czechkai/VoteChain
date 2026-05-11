<?php
require_once '../includes/config.php';

/** @var PDO $pdo */
if (!$pdo) {
    die('Database connection failed. Please check your configuration.');
}

requireCandidateFilingAccess($pdo);
$role = (($_SESSION['role'] ?? 'student') === 'candidate') ? 'candidate' : 'student';
$activePage = 'filing';

$positions = [];
$activeElections = [];

if ($pdo) {
    try {
        // Schema detection for resilience
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = 'elections'");
        $stmt->execute();
        $eCols = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
        $col_start = in_array('starts_at', $eCols) ? 'starts_at' : (in_array('start_date', $eCols) ? 'start_date' : 'created_at');
        $col_end = in_array('ends_at', $eCols) ? 'ends_at' : (in_array('end_date', $eCols) ? 'end_date' : 'created_at');
        $col_desc = in_array('description', $eCols) ? 'description' : (in_array('scope', $eCols) ? 'scope' : 'title');

        // Fetch elections that are either 'active' or 'scheduled' so candidates can file early
        $stmt = $pdo->prepare("SELECT * FROM elections WHERE status IN ('active', 'scheduled') ORDER BY $col_start DESC");
        $stmt->execute();
        $activeElections = $stmt->fetchAll();

        // Resilience for positions table
        $posStmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = CURRENT_SCHEMA() AND table_name = 'positions'");
        $posStmt->execute();
        $pCols = array_map('strtolower', $posStmt->fetchAll(PDO::FETCH_COLUMN));
        $col_pos_order = in_array('order_index', $pCols) ? 'order_index' : (in_array('display_order', $pCols) ? 'display_order' : 'id');

        $posStmt = $pdo->query("SELECT * FROM positions ORDER BY $col_pos_order ASC");
        $positions = $posStmt->fetchAll();
    } catch (Exception $e) {
        error_log('Positions fetch error: ' . $e->getMessage());
    }
}

$alertMessage = '';
$alertType = '';
if (isset($_GET['success'])) {
    $alertType = 'success';
    $alertMessage = 'Filing submitted successfully. Your role stays as student until admin approval.';
} elseif (isset($_GET['error'])) {
    $alertType = 'error';
    $errorCode = $_GET['error'] ?? 'server';
    $errorDetails = $_SESSION['filing_error_details'] ?? '';
    
    if ($errorCode === 'docs') {
        $alertMessage = 'Filing failed: Not all 5 required documents were uploaded. Please upload all documents.';
    } elseif ($errorCode === 'missing') {
        $alertMessage = 'Filing failed: Please select an Election and Position.';
    } elseif ($errorCode === 'type') {
        $alertMessage = 'Filing failed: Invalid file type. Only PDF, DOC, DOCX, JPG, JPEG, PNG allowed.';
    } elseif ($errorCode === 'upload') {
        $alertMessage = 'Filing failed: Could not upload file to server.';
    } elseif ($errorCode === 'duplicate') {
        $alertMessage = 'Filing failed: You have already filed for this position in this election.';
    } else {
        $alertMessage = 'Filing failed: ' . ($errorDetails ? htmlspecialchars($errorDetails) : 'Please check your inputs and try again.');
    }
    
    unset($_SESSION['filing_error_details']);
} elseif (isset($_GET['application'])) {
    $alertType = 'success';
    $alertMessage = 'Candidate application mode enabled. Submit filing documents for admin review.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidacy Filing | VoteChain</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { navy: '#0A1F44', royal: '#1E3A8A', gold: '#FFC107' } }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .file-input-hidden { display: none; }
    </style>
</head>
<body class="pb-20">
    <?php $role = 'candidate'; $activePage = 'filing'; include '../includes/sidebar.php'; ?>

    <header class="h-20 bg-white border-b sticky top-0 z-30 flex items-center justify-between px-8 lg:ml-72">
        <h2 class="text-xl font-black text-navy">Filing of Candidacy</h2>
        <div class="flex items-center gap-2 text-xs font-bold text-amber-500 bg-amber-50 px-4 py-2 rounded-full border border-amber-100">
            <i class="fa-solid fa-circle-notch animate-spin"></i> UNDER REVIEW
        </div>
    </header>

    <main class="lg:ml-72 p-8 max-w-5xl mx-auto space-y-10">
        <!-- Requirements Section -->
        <div class="bg-gradient-to-b from-navy to-royal p-12 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-5">
                <i class="fa-solid fa-file-lines absolute top-8 right-8 text-[200px]"></i>
            </div>
            <div class="relative z-10">
                <h2 class="text-4xl font-black mb-2">REQUIREMENTS</h2>
                <p class="text-gold text-lg font-extrabold mb-8">FOR FILING</p>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-4 group">
                        <div class="w-6 h-6 rounded-full bg-gold/20 border-2 border-gold flex items-center justify-center mt-1 flex-shrink-0 group-hover:bg-gold/40 transition">
                            <i class="fa-solid fa-check text-gold text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-lg">Certificate of Candidacy</p>
                            <p class="text-white/60 text-sm">Official nomination document</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="w-6 h-6 rounded-full bg-gold/20 border-2 border-gold flex items-center justify-center mt-1 flex-shrink-0 group-hover:bg-gold/40 transition">
                            <i class="fa-solid fa-check text-gold text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-lg">Certificate of Registration</p>
                            <p class="text-white/60 text-sm">Proof of enrollment for the current year</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="w-6 h-6 rounded-full bg-gold/20 border-2 border-gold flex items-center justify-center mt-1 flex-shrink-0 group-hover:bg-gold/40 transition">
                            <i class="fa-solid fa-check text-gold text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-lg">Report of Grades – 2nd Semester, A.Y. 2024–2025</p>
                            <p class="text-white/60 text-sm">Academic records showing current semester performance</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="w-6 h-6 rounded-full bg-gold/20 border-2 border-gold flex items-center justify-center mt-1 flex-shrink-0 group-hover:bg-gold/40 transition">
                            <i class="fa-solid fa-check text-gold text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-lg">Certificate of Good Moral Character</p>
                            <p class="text-white/60 text-sm">Character verification from school records</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="w-6 h-6 rounded-full bg-gold/20 border-2 border-gold flex items-center justify-center mt-1 flex-shrink-0 group-hover:bg-gold/40 transition">
                            <i class="fa-solid fa-check text-gold text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-lg">Recommendation Letter</p>
                            <p class="text-white/60 text-sm">Support letter from a faculty member or department</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filing Status Box -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
            <?php if ($alertMessage): ?>
                <div class="mb-6 px-4 py-3 rounded-2xl text-sm font-bold <?php echo $alertType === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100'; ?>">
                    <?php echo htmlspecialchars($alertMessage); ?>
                </div>
            <?php endif; ?>
            <div class="mb-6">
                <h3 class="text-2xl font-extrabold text-navy mb-2">Filing Progress</h3>
                <p class="text-slate-500 text-sm">Upload all required documents to complete your candidacy filing.</p>
            </div>
            <div class="w-full bg-slate-100 h-4 rounded-full overflow-hidden mb-4">
                <div id="progressBar" class="bg-gradient-to-r from-royal to-gold h-full w-[40%]" style="transition: width 0.5s ease-in-out"></div>
            </div>
            <p class="text-sm font-bold text-slate-600"><span id="uploadedCount">2</span> of 5 Documents Uploaded</p>
        </div>

        <!-- Document Upload Grid -->
        <form id="filingForm" class="space-y-6" method="POST" action="filing_handler.php" enctype="multipart/form-data">
            <!-- Profile Photo Section -->
            <div class="bg-gradient-to-br from-blue-50 to-slate-50 p-8 rounded-[2rem] border border-blue-200 shadow-sm">
                <div class="flex items-start gap-6">
                    <div class="flex-1">
                        <h4 class="text-lg font-extrabold text-navy mb-2">Candidate Profile Photo</h4>
                        <p class="text-slate-600 text-sm mb-4">Upload a professional photo for your candidate profile. This will be displayed to voters on the ballot and other campaign materials.</p>
                        <p class="text-xs text-slate-500 mb-4">✓ JPG, PNG, or WebP format | ✓ Recommended: 500x500px or larger | ✓ Professional headshot preferred</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div id="profilePhotoPreview" class="w-32 h-32 bg-slate-200 rounded-2xl border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden">
                            <div class="text-center text-slate-500">
                                <i class="fa-solid fa-image text-3xl mb-2 block"></i>
                                <p class="text-xs font-bold">No image</p>
                            </div>
                        </div>
                    </div>
                </div>
                <label class="cursor-pointer block mt-6">
                    <input type="file" id="profilePhoto" name="profile_photo" class="file-input-hidden" accept=".jpg,.jpeg,.png,.webp" onchange="handleProfilePhotoUpload(this)">
                    <div class="w-full md:w-64 py-3 bg-royal text-white rounded-xl font-bold text-sm hover:bg-navy transition text-center inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-camera"></i>Choose Profile Photo
                    </div>
                </label>
                <p id="profilePhotoFilename" class="text-xs text-slate-500 mt-3 hidden"></p>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <h4 class="text-lg font-extrabold text-navy mb-4">Election & Position</h4>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">Election</label>
                        <select name="election_id" required class="w-full mt-2 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-medium outline-none focus:ring-2 focus:ring-royal">
                            <option value="" disabled selected>Select Election</option>
                            <?php foreach ($activeElections as $election): ?>
                                <option value="<?php echo htmlspecialchars($election['id']); ?>">
                                    <?php echo htmlspecialchars($election['title'] ?? $election['name'] ?? 'Election'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">Position</label>
                        <select name="position_id" required class="w-full mt-2 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-medium outline-none focus:ring-2 focus:ring-royal">
                            <option value="" disabled selected>Select Position</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?php echo htmlspecialchars($position['id']); ?>">
                                    <?php echo htmlspecialchars($position['name'] ?? $position['title'] ?? 'Position'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Certificate of Candidacy -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-blue-50 text-royal rounded-2xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <span id="cert-candidacy-status" class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-3 py-1 rounded-lg hidden">UPLOADED</span>
                        <span id="cert-candidacy-pending" class="text-[10px] font-black text-amber-500 bg-amber-50 px-3 py-1 rounded-lg">PENDING</span>
                    </div>
                    <h4 class="font-bold text-navy mb-2">Certificate of Candidacy</h4>
                    <p class="text-xs text-slate-500 mb-4 flex-1">Official nomination document confirming your candidacy status.</p>
                    <label class="cursor-pointer">
                        <input type="file" id="cert-candidacy" name="cert_candidacy" class="file-input-hidden" accept=".pdf,.doc,.docx,.jpg,.png" onchange="handleFileUpload(this, 'cert-candidacy')">
                        <div class="w-full py-2 bg-navy text-white rounded-xl font-bold text-sm hover:bg-royal transition text-center">
                            <i class="fa-solid fa-cloud-arrow-up mr-2"></i>Choose File
                        </div>
                    </label>
                    <p id="cert-candidacy-filename" class="text-xs text-slate-500 mt-2 hidden"></p>
                </div>

                <!-- Certificate of Registration -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-green-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <span id="cert-registration-status" class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-3 py-1 rounded-lg hidden">UPLOADED</span>
                        <span id="cert-registration-pending" class="text-[10px] font-black text-amber-500 bg-amber-50 px-3 py-1 rounded-lg">PENDING</span>
                    </div>
                    <h4 class="font-bold text-navy mb-2">Certificate of Registration</h4>
                    <p class="text-xs text-slate-500 mb-4 flex-1">Proof of current semester enrollment status.</p>
                    <label class="cursor-pointer">
                        <input type="file" id="cert-registration" name="cert_registration" class="file-input-hidden" accept=".pdf,.doc,.docx,.jpg,.png" onchange="handleFileUpload(this, 'cert-registration')">
                        <div class="w-full py-2 bg-navy text-white rounded-xl font-bold text-sm hover:bg-royal transition text-center">
                            <i class="fa-solid fa-cloud-arrow-up mr-2"></i>Choose File
                        </div>
                    </label>
                    <p id="cert-registration-filename" class="text-xs text-slate-500 mt-2 hidden"></p>
                </div>

                <!-- Report of Grades -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-chart-bar"></i>
                        </div>
                        <span id="report-grades-status" class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-3 py-1 rounded-lg hidden">UPLOADED</span>
                        <span id="report-grades-pending" class="text-[10px] font-black text-amber-500 bg-amber-50 px-3 py-1 rounded-lg">PENDING</span>
                    </div>
                    <h4 class="font-bold text-navy mb-2">Report of Grades</h4>
                    <p class="text-xs text-slate-500 mb-4 flex-1">Academic records for 2nd Semester, A.Y. 2024–2025.</p>
                    <label class="cursor-pointer">
                        <input type="file" id="report-grades" name="report_grades" class="file-input-hidden" accept=".pdf,.doc,.docx,.jpg,.png" onchange="handleFileUpload(this, 'report-grades')">
                        <div class="w-full py-2 bg-navy text-white rounded-xl font-bold text-sm hover:bg-royal transition text-center">
                            <i class="fa-solid fa-cloud-arrow-up mr-2"></i>Choose File
                        </div>
                    </label>
                    <p id="report-grades-filename" class="text-xs text-slate-500 mt-2 hidden"></p>
                </div>

                <!-- Certificate of Good Moral Character -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-heart"></i>
                        </div>
                        <span id="good-moral-status" class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-3 py-1 rounded-lg hidden">UPLOADED</span>
                        <span id="good-moral-pending" class="text-[10px] font-black text-amber-500 bg-amber-50 px-3 py-1 rounded-lg">PENDING</span>
                    </div>
                    <h4 class="font-bold text-navy mb-2">Good Moral Character</h4>
                    <p class="text-xs text-slate-500 mb-4 flex-1">Character verification certificate from school records.</p>
                    <label class="cursor-pointer">
                        <input type="file" id="good-moral" name="good_moral" class="file-input-hidden" accept=".pdf,.doc,.docx,.jpg,.png" onchange="handleFileUpload(this, 'good-moral')">
                        <div class="w-full py-2 bg-navy text-white rounded-xl font-bold text-sm hover:bg-royal transition text-center">
                            <i class="fa-solid fa-cloud-arrow-up mr-2"></i>Choose File
                        </div>
                    </label>
                    <p id="good-moral-filename" class="text-xs text-slate-500 mt-2 hidden"></p>
                </div>

                <!-- Recommendation Letter -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <span id="recommendation-status" class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-3 py-1 rounded-lg hidden">UPLOADED</span>
                        <span id="recommendation-pending" class="text-[10px] font-black text-amber-500 bg-amber-50 px-3 py-1 rounded-lg">PENDING</span>
                    </div>
                    <h4 class="font-bold text-navy mb-2">Recommendation Letter</h4>
                    <p class="text-xs text-slate-500 mb-4 flex-1">Support letter from a faculty member or department head.</p>
                    <label class="cursor-pointer">
                        <input type="file" id="recommendation" name="recommendation" class="file-input-hidden" accept=".pdf,.doc,.docx,.jpg,.png" onchange="handleFileUpload(this, 'recommendation')">
                        <div class="w-full py-2 bg-navy text-white rounded-xl font-bold text-sm hover:bg-royal transition text-center">
                            <i class="fa-solid fa-cloud-arrow-up mr-2"></i>Choose File
                        </div>
                    </label>
                    <p id="recommendation-filename" class="text-xs text-slate-500 mt-2 hidden"></p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4 justify-end">
                <button type="reset" class="px-8 py-3 bg-slate-100 text-navy rounded-xl font-bold hover:bg-slate-200 transition">
                    Clear All
                </button>
                <button type="submit" class="px-8 py-3 bg-navy text-white rounded-xl font-bold hover:bg-royal transition">
                    <i class="fa-solid fa-arrow-right mr-2"></i>Submit Filing
                </button>
            </div>
        </form>

        <!-- Timeline Section -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200">
            <h3 class="text-2xl font-extrabold text-navy mb-8">Election Timeline</h3>
            <div class="space-y-6">
                <?php if (empty($activeElections)): ?>
                    <p class="text-slate-400 font-bold text-center py-4 uppercase text-xs tracking-widest">No scheduled elections found</p>
                <?php else: ?>
                    <?php foreach ($activeElections as $election): ?>
                        <div class="flex gap-6">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-navy text-white rounded-full flex items-center justify-center font-bold text-lg shadow-lg shadow-navy/20">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <div class="w-1 h-16 bg-slate-100 my-2"></div>
                            </div>
                            <div class="pb-6">
                                <p class="text-[10px] font-black text-gold uppercase tracking-widest">
                                    <?php echo date('M d, Y', strtotime($election[$col_start])); ?> 
                                    <?php if ($election[$col_end]): ?> — <?php echo date('M d, Y', strtotime($election[$col_end])); ?><?php endif; ?>
                                </p>
                                <h4 class="text-lg font-bold text-navy mt-1 uppercase"><?php echo htmlspecialchars($election['title'] ?? $election['name'] ?? 'Election'); ?></h4>
                                <p class="text-slate-500 text-sm mt-2 leading-relaxed"><?php echo htmlspecialchars($election[$col_desc] ?? 'Voting period for university leaders.'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function handleFileUpload(input, docType) {
            const file = input.files[0];
            if (!file) return;

            const filenameEl = document.getElementById(`${docType}-filename`);
            filenameEl.textContent = `📄 ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
            filenameEl.classList.remove('hidden');

            document.getElementById(`${docType}-pending`)?.classList.add('hidden');
            document.getElementById(`${docType}-status`)?.classList.remove('hidden');

            updateProgress();
        }

        function updateProgress() {
            const docs = ['cert-candidacy', 'cert-registration', 'report-grades', 'good-moral', 'recommendation'];
            const uploaded = docs.filter(doc => document.getElementById(doc).files.length > 0).length;
            
            document.getElementById('uploadedCount').textContent = uploaded;
            const percentage = (uploaded / 5) * 100;
            document.getElementById('progressBar').style.width = percentage + '%';
        }

        function handleProfilePhotoUpload(input) {
            const file = input.files[0];
            if (!file) return;

            const filenameEl = document.getElementById('profilePhotoFilename');
            filenameEl.textContent = `✓ ${file.name} selected (${(file.size / 1024).toFixed(2)} KB)`;
            filenameEl.classList.remove('hidden');

            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = document.getElementById('profilePhotoPreview');
                preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;" alt="Profile preview">`;
            };
            reader.readAsDataURL(file);
        }

        document.getElementById('filingForm').addEventListener('submit', function(e) {
            // Verify election and position are selected
            const electionId = document.querySelector('select[name="election_id"]').value;
            const positionId = document.querySelector('select[name="position_id"]').value;
            
            if (!electionId) {
                e.preventDefault();
                alert('Please select an Election');
                return;
            }
            
            if (!positionId) {
                e.preventDefault();
                alert('Please select a Position');
                return;
            }
            
            // Check all 5 required documents
            const docs = ['cert-candidacy', 'cert-registration', 'report-grades', 'good-moral', 'recommendation'];
            const docLabels = {
                'cert-candidacy': 'Certificate of Candidacy',
                'cert-registration': 'Certificate of Registration',
                'report-grades': 'Report of Grades',
                'good-moral': 'Good Moral Character',
                'recommendation': 'Recommendation Letter'
            };
            
            const uploaded = docs.filter(doc => document.getElementById(doc).files.length > 0).length;
            const missing = docs.filter(doc => document.getElementById(doc).files.length === 0).map(doc => docLabels[doc]);

            if (uploaded !== 5) {
                e.preventDefault();
                alert('All 5 documents are required:\n\n' + missing.join('\n') + '\n\nPlease upload all required documents before submitting.');
                return;
            }

            document.querySelector('button[type="submit"]')?.setAttribute('disabled', 'disabled');
        });

        window.addEventListener('load', updateProgress);
    </script>

</body>
</html>
