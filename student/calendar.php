<?php
require_once '../includes/config.php';
requireRole('student');
$role = 'student';
$activePage = 'calendar';
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
                <span class="px-4 font-black text-navy uppercase tracking-widest text-sm">May 2026</span>
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
                    <!-- Week 1 Empty -->
                    <div class="calendar-day p-4 border-r border-b border-slate-100 bg-slate-50/30"></div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100 bg-slate-50/30"></div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100 bg-slate-50/30"></div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100 bg-slate-50/30"></div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100 bg-slate-50/30"></div>
                    <!-- May 1 -->
                    <div class="calendar-day p-4 border-r border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">1</span>
                    </div>
                    <div class="calendar-day p-4 border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">2</span>
                    </div>

                    <!-- Week 2 -->
                    <div class="calendar-day p-4 border-r border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">3</span>
                    </div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">4</span>
                        <div class="mt-2 p-1.5 bg-blue-50 rounded-lg">
                            <p class="text-[9px] font-black text-blue-600 leading-tight">Filing Period Begins</p>
                        </div>
                    </div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">5</span>
                    </div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">6</span>
                    </div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">7</span>
                    </div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">8</span>
                        <div class="mt-2 p-1.5 bg-amber-50 rounded-lg">
                            <p class="text-[9px] font-black text-amber-600 leading-tight">Deadline of Filing</p>
                        </div>
                    </div>
                    <div class="calendar-day p-4 border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">9</span>
                    </div>

                    <!-- Week 3 -->
                    <div class="calendar-day p-4 border-r border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-400">10</span>
                        <div class="mt-2 p-1.5 bg-emerald-50 rounded-lg">
                            <p class="text-[9px] font-black text-emerald-600 leading-tight">Campaign Period</p>
                        </div>
                    </div>
                    <div class="calendar-day p-4 border-r border-b border-slate-100 bg-royal/5">
                        <span class="text-sm font-black text-royal">11</span>
                        <div class="mt-2 p-2 bg-royal text-white rounded-xl shadow-lg shadow-royal/20">
                            <p class="text-[9px] font-black uppercase tracking-tighter">Election Day</p>
                            <p class="text-[8px] font-medium opacity-80">08:00 AM Start</p>
                        </div>
                    </div>
                    <!-- ... More days can be filled here -->
                </div>
            </div>

            <!-- Side Timeline & Legend -->
            <div class="xl:col-span-4 space-y-8">
                <!-- Upcoming Timeline -->
                <div class="bg-white rounded-[2.5rem] border border-slate-200 p-8">
                    <h3 class="text-xl font-black text-navy mb-8">Timeline</h3>
                    <div class="space-y-8 relative">
                        <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-100"></div>
                        
                        <div class="relative flex gap-6">
                            <div class="w-6 h-6 rounded-full bg-blue-500 border-4 border-white shadow-sm z-10 flex-shrink-0"></div>
                            <div>
                                <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">May 4 - 8</p>
                                <h4 class="font-bold text-navy">Filing of Candidacy</h4>
                                <p class="text-xs text-slate-400 mt-1">Submit documents via the Student Affairs portal.</p>
                            </div>
                        </div>

                        <div class="relative flex gap-6">
                            <div class="w-6 h-6 rounded-full bg-emerald-500 border-4 border-white shadow-sm z-10 flex-shrink-0"></div>
                            <div>
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">May 9 - 10</p>
                                <h4 class="font-bold text-navy">Campaign Period</h4>
                                <p class="text-xs text-slate-400 mt-1">Official platform presentation and debates.</p>
                            </div>
                        </div>

                        <div class="relative flex gap-6">
                            <div class="w-6 h-6 rounded-full bg-royal border-4 border-white shadow-sm z-10 flex-shrink-0"></div>
                            <div>
                                <p class="text-[10px] font-black text-royal uppercase tracking-widest mb-1">May 11, 2026</p>
                                <h4 class="font-bold text-navy">Election Day</h4>
                                <p class="text-xs text-slate-400 mt-1">Digital booths open from 8:00 AM to 5:00 PM.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="bg-white rounded-[2rem] border border-slate-200 p-6">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Event Types</h4>
                    <div class="grid grid-cols-2 gap-4">
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