<?php $role = 'admin'; $activePage = 'calendar'; $pageTitle = 'Election Calendar'; ?>
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
<body class="min-h-screen flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 lg:ml-72 flex flex-col min-w-0">
        <?php include '../includes/header.php'; ?>

        <main class="p-8 flex-1">
            <!-- Calendar Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-black text-navy">May 2026</h1>
                    <p class="text-slate-600">Election Period & Important Dates</p>
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
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-6 mb-8">
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
                    <!-- Previous month dates (grayed out) -->
                    <div class="aspect-square flex items-center justify-center text-slate-300 font-bold text-sm bg-slate-50 rounded-lg">28</div>
                    <div class="aspect-square flex items-center justify-center text-slate-300 font-bold text-sm bg-slate-50 rounded-lg">29</div>
                    <div class="aspect-square flex items-center justify-center text-slate-300 font-bold text-sm bg-slate-50 rounded-lg">30</div>
                    <div class="aspect-square flex items-center justify-center text-slate-300 font-bold text-sm bg-slate-50 rounded-lg">1</div>

                    <!-- May dates with events -->
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg border-2 border-emerald-400 bg-emerald-50 cursor-pointer hover:shadow-md transition" data-event="Application Start">
                        <span class="font-black text-navy">2</span>
                        <span class="text-[8px] text-emerald-600 font-bold">APP</span>
                    </div>

                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">3</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">4</div>

                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg border-2 border-blue-400 bg-blue-50 cursor-pointer hover:shadow-md transition" data-event="Last Day Filing">
                        <span class="font-black text-navy">5</span>
                        <span class="text-[8px] text-blue-600 font-bold">DEADLINE</span>
                    </div>

                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">6</div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg border-2 border-purple-400 bg-purple-50 cursor-pointer hover:shadow-md transition" data-event="Campaign Start">
                        <span class="font-black text-navy">7</span>
                        <span class="text-[8px] text-purple-600 font-bold">CAMP</span>
                    </div>

                    <!-- Rest of May -->
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">8</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">9</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">10</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition" style="background-color: #f8fafc; border: 2px dashed #94a3b8;">11</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">12</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">13</div>

                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg border-2 border-red-400 bg-red-50 cursor-pointer hover:shadow-md transition" data-event="Election Day">
                        <span class="font-black text-navy">14</span>
                        <span class="text-[8px] text-red-600 font-bold">VOTE</span>
                    </div>

                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">15</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">16</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">17</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">18</div>

                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg border-2 border-gold bg-yellow-50 cursor-pointer hover:shadow-md transition" data-event="Proclamation">
                        <span class="font-black text-navy">19</span>
                        <span class="text-[8px] text-amber-600 font-bold">PROC</span>
                    </div>

                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg border-2 border-slate-400 bg-slate-50 cursor-pointer hover:shadow-md transition" data-event="Legislative Elections">
                        <span class="font-black text-navy">20</span>
                        <span class="text-[8px] text-slate-600 font-bold">LEG</span>
                    </div>

                    <!-- Rest -->
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">21</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">22</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">23</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">24</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">25</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">26</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">27</div>

                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">28</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">29</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">30</div>
                    <div class="aspect-square flex flex-col items-center justify-center font-bold text-sm text-navy rounded-lg hover:bg-slate-50 transition">31</div>
                    <!-- Next month -->
                    <div class="aspect-square flex flex-col items-center justify-center text-slate-300 font-bold text-sm bg-slate-50 rounded-lg">1</div>
                    <div class="aspect-square flex flex-col items-center justify-center text-slate-300 font-bold text-sm bg-slate-50 rounded-lg">2</div>
                    <div class="aspect-square flex flex-col items-center justify-center text-slate-300 font-bold text-sm bg-slate-50 rounded-lg">3</div>
                </div>
            </div>

            <!-- Legend -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-emerald-400 rounded"></div>
                        <div>
                            <p class="font-bold text-navy">Application Period</p>
                            <p class="text-xs text-slate-600">April 29 - May 5</p>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 p-4 rounded-xl border border-purple-200">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-purple-400 rounded"></div>
                        <div>
                            <p class="font-bold text-navy">Campaign Period</p>
                            <p class="text-xs text-slate-600">May 6 - May 13</p>
                        </div>
                    </div>
                </div>

                <div class="bg-red-50 p-4 rounded-xl border border-red-200">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-red-400 rounded"></div>
                        <div>
                            <p class="font-bold text-navy">Election Day</p>
                            <p class="text-xs text-slate-600">May 14, 2026</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                <h3 class="text-2xl font-black text-navy mb-8">Timeline of Events</h3>
                <div class="space-y-6">
                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-emerald-500 text-white rounded-full flex items-center justify-center font-bold">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="w-1 h-16 bg-slate-200"></div>
                        </div>
                        <div>
                            <p class="font-black text-gold text-sm">APRIL 29 - MAY 5</p>
                            <h4 class="text-lg font-black text-navy">Application Period</h4>
                            <p class="text-slate-600 text-sm">Candidates submit applications and required documents for candidacy filing.</p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-purple-500 text-white rounded-full flex items-center justify-center font-bold">
                                <i class="fa-solid fa-megaphone"></i>
                            </div>
                            <div class="w-1 h-16 bg-slate-200"></div>
                        </div>
                        <div>
                            <p class="font-black text-gold text-sm">MAY 6 - MAY 13</p>
                            <h4 class="text-lg font-black text-navy">Campaign Period</h4>
                            <p class="text-slate-600 text-sm">Candidates present platforms and campaign to the student body.</p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-red-500 text-white rounded-full flex items-center justify-center font-bold">
                                <i class="fa-solid fa-vote-yea"></i>
                            </div>
                            <div class="w-1 h-16 bg-slate-200"></div>
                        </div>
                        <div>
                            <p class="font-black text-gold text-sm">MAY 14</p>
                            <h4 class="text-lg font-black text-navy">Election Day</h4>
                            <p class="text-slate-600 text-sm">Students cast their votes for university leaders throughout the day.</p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-gold text-navy rounded-full flex items-center justify-center font-bold">
                                <i class="fa-solid fa-champagne-glasses"></i>
                            </div>
                            <div class="w-1 h-16 bg-slate-200"></div>
                        </div>
                        <div>
                            <p class="font-black text-gold text-sm">MAY 19</p>
                            <h4 class="text-lg font-black text-navy">Proclamation of Winners</h4>
                            <p class="text-slate-600 text-sm">Official announcement of elected candidates and winners.</p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-slate-500 text-white rounded-full flex items-center justify-center font-bold">
                                <i class="fa-solid fa-landmark"></i>
                            </div>
                        </div>
                        <div>
                            <p class="font-black text-gold text-sm">MAY 20</p>
                            <h4 class="text-lg font-black text-navy">Legislative & Judicial Elections</h4>
                            <p class="text-slate-600 text-sm">Secondary elections for senate and judicial positions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
