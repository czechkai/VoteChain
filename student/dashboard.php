<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | VoteChain DOrSU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0A1F44',
                        royal: '#1E3A8A',
                        gold: '#FFC107',
                        slate: {
                            850: '#1e293b',
                            950: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .sidebar-gradient { background: linear-gradient(180deg, #0A1F44 0%, #1E3A8A 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(226, 232, 240, 0.8); }
        .nav-item-active { background: rgba(255, 255, 255, 0.1); border-left: 4px solid #FFC107; color: white !important; }
        .custom-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="flex min-h-screen">
    <?php $role = 'student'; $activePage = 'dashboard'; include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-72 p-4 md:p-8">
        <!-- Top Bar -->
        <header class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-navy">Student Dashboard</h1>
                <p class="text-slate-500 font-medium text-sm">Welcome back, <span class="text-royal font-bold">James Blanco</span></p>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <button class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-400 hover:text-navy custom-shadow transition-all">
                        <i class="fa-solid fa-bell"></i>
                        <span class="absolute top-3 right-3 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                    </button>
                </div>
                <div class="flex items-center gap-3 bg-white p-2 pr-4 rounded-2xl custom-shadow">
                    <div class="w-10 h-10 bg-navy rounded-xl overflow-hidden flex items-center justify-center text-white font-bold">JB</div>
                    <div class="hidden sm:block">
                        <p class="text-xs font-extrabold text-navy">2026-0001</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">3rd Year • IT</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-royal/10 text-royal rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-green-500 bg-green-50 px-2 py-1 rounded-lg uppercase">Active</span>
                </div>
                <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Active Elections</h3>
                <p class="text-2xl font-extrabold text-navy">02</p>
            </div>

            <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gold/10 text-gold rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg uppercase">Total</span>
                </div>
                <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Candidates</h3>
                <p class="text-2xl font-extrabold text-navy">14</p>
            </div>

            <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-500/10 text-green-500 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-bolt text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-blue-500 bg-blue-50 px-2 py-1 rounded-lg uppercase">Real-time</span>
                </div>
                <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Total Votes Cast</h3>
                <p class="text-2xl font-extrabold text-navy">1,204</p>
            </div>

            <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-navy/10 text-navy rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-clock text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-amber-500 bg-amber-50 px-2 py-1 rounded-lg uppercase">Deadline</span>
                </div>
                <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Time Remaining</h3>
                <p class="text-2xl font-extrabold text-navy">04:12:00</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Side -->
            <div class="lg:col-span-8 space-y-8">
                <!-- Participation Chart -->
                <div class="glass-card p-8 rounded-[2rem] custom-shadow">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-extrabold text-navy">Voting Participation</h3>
                            <p class="text-sm text-slate-400 font-medium">Turnout rate across programs</p>
                        </div>
                        <select class="bg-slate-50 border-none text-xs font-bold text-navy px-4 py-2 rounded-xl outline-none">
                            <option>University Wide</option>
                            <option>FACET Only</option>
                        </select>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="participationChart"></canvas>
                    </div>
                </div>

                <!-- Election Overview List -->
                <div class="space-y-4">
                    <h3 class="text-lg font-extrabold text-navy ml-2">Available Elections</h3>
                    
                    <div class="group bg-white p-6 rounded-[2rem] border border-slate-100 custom-shadow hover:border-royal/30 transition-all flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 bg-navy rounded-2xl flex items-center justify-center text-white text-2xl">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-navy">University Student Government</h4>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">2026-2027 General Election</p>
                                <div class="flex gap-2 mt-2">
                                    <span class="text-[10px] font-bold bg-green-100 text-green-600 px-2 py-0.5 rounded-full">Ongoing</span>
                                    <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">Blockchain Enabled</span>
                                </div>
                            </div>
                        </div>
                        <button class="px-8 py-3 bg-navy text-white rounded-xl font-bold hover:bg-royal transition-all shadow-lg shadow-navy/20">
                            Vote Now
                        </button>
                    </div>

                    <div class="group bg-white p-6 rounded-[2rem] border border-slate-100 custom-shadow hover:border-royal/30 transition-all flex flex-col md:flex-row md:items-center justify-between gap-6 opacity-80">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 text-2xl">
                                <i class="fa-solid fa-code"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-navy">FACET Local Council</h4>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Engineering & Technology Program</p>
                                <div class="flex gap-2 mt-2">
                                    <span class="text-[10px] font-bold bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full">Starts in 2h</span>
                                </div>
                            </div>
                        </div>
                        <button disabled class="px-8 py-3 bg-slate-100 text-slate-400 rounded-xl font-bold cursor-not-allowed">
                            Upcoming
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Schedule / Calendar Preview -->
                <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                    <h3 class="text-lg font-extrabold text-navy mb-6">Upcoming Schedule</h3>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center justify-center min-w-[50px] h-14 bg-navy text-white rounded-xl">
                                <span class="text-xs font-bold">MAY</span>
                                <span class="text-lg font-black leading-none">12</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-navy text-sm">Candidates Forum</h4>
                                <p class="text-xs text-slate-400">01:00 PM • DOrSU Gymnasium</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center justify-center min-w-[50px] h-14 bg-slate-100 text-navy rounded-xl">
                                <span class="text-xs font-bold">MAY</span>
                                <span class="text-lg font-black leading-none">15</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-navy text-sm">Miting de Avance</h4>
                                <p class="text-xs text-slate-400">08:00 AM • Virtual Hall</p>
                            </div>
                        </div>
                    </div>
                    <button class="w-full mt-8 py-3 text-xs font-bold text-navy border-2 border-slate-100 rounded-xl hover:bg-slate-50 transition-all">
                        View All Events
                    </button>
                </div>

                <!-- Timeline / Recent Activity -->
                <div class="glass-card p-6 rounded-[2rem] custom-shadow">
                    <h3 class="text-lg font-extrabold text-navy mb-6">Activity Log</h3>
                    <div class="relative space-y-6 ml-2">
                        <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-slate-100"></div>
                        
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1.5 w-6 h-6 bg-white border-4 border-royal rounded-full"></div>
                            <p class="text-xs font-bold text-navy">Account Verified</p>
                            <p class="text-[10px] text-slate-400">Identity successfully matched with registrar data.</p>
                            <p class="text-[9px] text-royal font-bold uppercase mt-1">2 hours ago</p>
                        </div>

                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1.5 w-6 h-6 bg-white border-4 border-gold rounded-full shadow-[0_0_10px_rgba(255,193,7,0.3)]"></div>
                            <p class="text-xs font-bold text-navy">Security Alert</p>
                            <p class="text-[10px] text-slate-400">New login detected from IP 192.168.1.1</p>
                            <p class="text-[9px] text-royal font-bold uppercase mt-1">Yesterday</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize Charts
        window.onload = function() {
            const ctx = document.getElementById('participationChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
                    datasets: [{
                        label: 'Voter Turnout',
                        data: [120, 350, 680, 890, 1100, 1204],
                        borderColor: '#1E3A8A',
                        borderWidth: 4,
                        pointBackgroundColor: '#FFC107',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        tension: 0.4,
                        fill: true,
                        backgroundColor: (context) => {
                            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, 'rgba(30, 58, 138, 0.1)');
                            gradient.addColorStop(1, 'rgba(30, 58, 138, 0)');
                            return gradient;
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { display: true, color: '#f1f5f9' },
                            ticks: { font: { size: 10, weight: '600' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10, weight: '600' } }
                        }
                    }
                }
            });
        };
    </script>
</body>
</html>