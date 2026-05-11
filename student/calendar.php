<?php
require_once '../includes/config.php';

/** @var PDO $pdo */
if (!$pdo) {
    die('Database connection failed. Please check your configuration.');
}

requireRole('student');
$role = 'student';
$activePage = 'calendar';

try {
    $stmt = $pdo->query("SELECT * FROM elections ORDER BY starts_at ASC");
    $elections = $stmt->fetchAll();
} catch (Exception $e) {
    $elections = [];
}

// Calendar Calculation Logic
$targetDate = !empty($elections) ? $elections[0]['starts_at'] : date('Y-m-d');
$monthNum = (int)date('m', strtotime($targetDate));
$yearNum = (int)date('Y', strtotime($targetDate));
$monthName = date('F Y', strtotime($targetDate));
$daysInMonth = (int)date('t', strtotime("$yearNum-$monthNum-01"));
$firstDayOffset = (int)date('N', strtotime("$yearNum-$monthNum-01")) - 1; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Calendar | VoteChain DOrSU</title>
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
        .sidebar-gradient { background: linear-gradient(180deg, #0A1F44 0%, #1E3A8A 100%); }
        .nav-item-active { background: rgba(255, 255, 255, 0.1); border-left: 4px solid #FFC107; color: white !important; }
        .calendar-day { min-height: 120px; transition: all 0.2s; }
        .calendar-day:hover { background-color: #f1f5f9; }
        .event-dot { width: 6px; height: 6px; border-radius: 99px; }
    </style>
</head>
<body class="flex min-h-screen">
    <?php $role = 'student'; $activePage = 'calendar'; include '../includes/sidebar.php'; ?>

    <main class="flex-1 lg:ml-72 p-4 md:p-8 pb-20">
        <header class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6">
            <div>
                <h1 class="text-3xl font-extrabold text-navy">Election Calendar</h1>
                <p class="text-slate-500 font-medium mt-1">Academic Year 2025-2026 Second Semester</p>
            </div>
            <div class="flex items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200">
                <button class="p-2 hover:bg-slate-50 rounded-xl transition-colors"><i class="fa-solid fa-chevron-left"></i></button>
                <span class="px-4 font-black text-navy uppercase tracking-widest text-sm"><?php echo $monthName; ?></span>
                <button class="p-2 hover:bg-slate-50 rounded-xl transition-colors"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            
            <!-- Main Calendar Grid -->
            <div class="xl:col-span-8 bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50/50">
                    <div class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Sun</div>
                    <div class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Mon</div>
                    <div class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Tue</div>
                    <div class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Wed</div>
                    <div class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Thu</div>
                    <div class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Fri</div>
                    <div class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Sat</div>
                </div>
                
                <div class="grid grid-cols-7">
                    <?php
                    for ($i = 0; $i < $firstDayOffset; $i++) {
                        echo '<div class="calendar-day p-4 border-r border-b border-slate-100 bg-slate-50/30"></div>';
                    }
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $event = null;
                        foreach ($elections as $e) {
                            if ((int)date('j', strtotime($e['starts_at'])) === $day) {
                                $event = $e;
                                break;
                            }
                        }
                        echo '<div class="calendar-day p-4 border-r border-b border-slate-100 ' . ($event ? 'bg-royal/5' : '') . '">';
                        echo '<span class="text-sm font-bold ' . ($event ? 'text-royal' : 'text-slate-400') . '">' . $day . '</span>';
                        if ($event) {
                            echo '<div class="mt-2 p-1.5 bg-royal text-white rounded-lg"><p class="text-[9px] font-black leading-tight uppercase">' . htmlspecialchars(substr($event['title'], 0, 15)) . '...</p></div>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Side Timeline & Legend -->
            <div class="xl:col-span-4 space-y-8">
                <!-- Upcoming Timeline -->
                <div class="bg-white rounded-[2.5rem] border border-slate-200 p-8">
                    <h3 class="text-xl font-black text-navy mb-8">Timeline</h3>
                    <div class="space-y-8 relative">
                        <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-100"></div>
                        
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-royal"></span>
                            <span class="text-xs font-bold text-navy">Voting Day</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-bold text-navy">Filing</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-navy">Campaign</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-bold text-navy">Deadline</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>