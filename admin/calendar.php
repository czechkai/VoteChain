<?php
require_once '../includes/config.php';

/** @var PDO $pdo */
if (!$pdo) {
    die('Database connection failed. Please check your configuration.');
}

requireRole('admin');
$role = 'admin';
$activePage = 'calendar';
$pageTitle = 'Election Calendar';

// Fetch elections from database to sync the calendar
try {
    $stmt = $pdo->query("SELECT * FROM elections ORDER BY starts_at ASC");
    $elections = $stmt->fetchAll();
} catch (Exception $e) {
    $elections = [];
}

// Calendar Calculation Logic
$targetDate = !empty($elections) ? $elections[0]['starts_at'] : date('Y-m-d');
$month = (int)date('m', strtotime($targetDate));
$year = (int)date('Y', strtotime($targetDate));
$daysInMonth = (int)date('t', strtotime("$year-$month-01"));
$firstDayOffset = (int)date('N', strtotime("$year-$month-01")) - 1; // 0 (Mon) to 6 (Sun)

$eventsByDay = [];
foreach ($elections as $e) {
    if (date('m-Y', strtotime($e['starts_at'])) === date('m-Y', strtotime($targetDate))) {
        $d = (int)date('j', strtotime($e['starts_at']));
        $eventsByDay[$d][] = $e;
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
            <!-- Calendar Header -->
            <div class="flex justify-between items-center mb-8">
                <?php $displayDate = !empty($elections) ? date('F Y', strtotime($elections[0]['starts_at'])) : date('F Y'); ?>
                <div>
                    <h1 class="text-3xl font-black text-navy"><?php echo $displayDate; ?></h1>
                    <p class="text-slate-600">Syncing with active database schedules</p>
                </div>
                <div class="flex gap-4">
                    <button class="w-10 h-10 rounded-lg bg-slate-100 text-navy hover:bg-slate-200 transition">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button class="px-6 py-2 bg-navy text-white rounded-lg font-bold hover:bg-royal transition">Today</button>
                    <button class="w-10 h-10 rounded-lg bg-slate-100 text-navy hover:bg-slate-200 transition">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div data-admin-search-item class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-6 mb-8">
                <!-- Days of Week -->
                <div class="grid grid-cols-7 gap-2 mb-4">
                    <div class="text-center font-black text-slate-600 text-sm py-4">MON</div>
                    <div class="text-center font-black text-slate-600 text-sm py-4">TUE</div>
                    <div class="text-center font-black text-slate-600 text-sm py-4">WED</div>
                    <div class="text-center font-black text-slate-600 text-sm py-4">THU</div>
                    <div class="text-center font-black text-slate-600 text-sm py-4">FRI</div>
                    <div class="text-center font-black text-slate-600 text-sm py-4">SAT</div>
                    <div class="text-center font-black text-slate-600 text-sm py-4">SUN</div>
                </div>

                <!-- Calendar Dates -->
                <div class="grid grid-cols-7 gap-2">
                    <?php
                    // Render Empty Slots for Previous Month
                    for ($i = 0; $i < $firstDayOffset; $i++) {
                        echo '<div class="aspect-square flex items-center justify-center text-slate-200 font-bold text-sm bg-slate-50/50 rounded-lg"></div>';
                    }

                    // Render Current Month Days
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $hasEvent = isset($eventsByDay[$day]);
                        $eventClass = '';
                        $label = '';
                        
                        if ($hasEvent) {
                            $status = strtolower($eventsByDay[$day][0]['status']);
                            if ($status === 'active') {
                                $eventClass = 'border-2 border-emerald-400 bg-emerald-50';
                                $label = '<span class="text-[8px] text-emerald-600 font-bold">LIVE</span>';
                            } elseif ($status === 'scheduled') {
                                $eventClass = 'border-2 border-amber-400 bg-amber-50';
                                $label = '<span class="text-[8px] text-amber-600 font-bold">POLL</span>';
                            } else {
                                $eventClass = 'border-2 border-slate-400 bg-slate-50';
                                $label = '<span class="text-[8px] text-slate-600 font-bold">DONE</span>';
                            }
                        }

                        echo '<div class="aspect-square flex flex-col items-center justify-center rounded-lg font-bold text-sm text-navy hover:bg-slate-100 transition cursor-pointer ' . $eventClass . '">';
                        echo '<span>' . $day . '</span>';
                        echo $label;
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Legend -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div data-admin-search-item class="bg-emerald-50 p-4 rounded-xl border border-emerald-200">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-emerald-400 rounded"></div>
                        <div>
                            <p class="font-bold text-navy">Active Elections</p>
                            <p class="text-xs text-slate-600">Polls currently open</p>
                        </div>
                    </div>
                </div>
                <div data-admin-search-item class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-amber-400 rounded"></div>
                        <div>
                            <p class="font-bold text-navy">Scheduled</p>
                            <p class="text-xs text-slate-600">Upcoming events</p>
                        </div>
                    </div>
                </div>
                <div data-admin-search-item class="bg-slate-100 p-4 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-slate-400 rounded"></div>
                        <div>
                            <p class="font-bold text-navy">Completed</p>
                            <p class="text-xs text-slate-600">Past elections</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div data-admin-search-item class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                <h3 class="text-2xl font-black text-navy mb-8">Timeline of Events</h3>
                <div class="space-y-6">
                    <?php if (empty($elections)): ?>
                        <div class="text-center py-10">
                            <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No scheduled events found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($elections as $election): ?>
                            <div class="flex gap-6">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-royal text-white rounded-full flex items-center justify-center font-bold shadow-lg shadow-royal/20">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </div>
                                    <div class="w-1 h-16 bg-slate-100"></div>
                                </div>
                                <div>
                                    <p class="font-black text-gold text-sm uppercase tracking-wide">
                                        <?php echo date('M d, Y', strtotime($election['starts_at'])); ?> 
                                        <?php if ($election['ends_at']): ?>
                                            — <?php echo date('M d, Y', strtotime($election['ends_at'])); ?>
                                        <?php endif; ?>
                                    </p>
                                    <h4 class="text-lg font-black text-navy"><?php echo htmlspecialchars($election['title']); ?></h4>
                                    <p class="text-slate-500 text-sm leading-relaxed"><?php echo htmlspecialchars($election['description']); ?></p>
                                    <span class="inline-block mt-2 text-[10px] font-black px-3 py-1 rounded-lg uppercase border 
                                        <?php echo strtolower($election['status']) === 'active' ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-amber-600 bg-amber-50 border-amber-100'; ?>">
                                        <?php echo htmlspecialchars($election['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
