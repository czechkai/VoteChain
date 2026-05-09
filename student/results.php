<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Results | VoteChain DOrSU</title>
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
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        .sidebar-gradient { background: linear-gradient(180deg, #0A1F44 0%, #1E3A8A 100%); }
        .nav-item-active { background: rgba(255, 255, 255, 0.1); border-left: 4px solid #FFC107; color: white !important; }
        .glass-card { background: white; border-radius: 2rem; border: 1px solid #e2e8f0; }
        .result-bar { transition: width 1.5s cubic-bezier(0.65, 0, 0.35, 1); }
    </style>
</head>
<body class="flex min-h-screen">
    <?php $role = 'student'; $activePage = 'results'; include '../includes/sidebar.php'; ?>

    <main class="flex-1 lg:ml-72 p-4 md:p-8">
        <!-- Live Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                    <span class="text-xs font-black text-red-500 uppercase tracking-widest">Live Election Results</span>
                </div>
                <h1 class="text-3xl font-extrabold text-navy">USC General Elections 2026</h1>
                <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-1">Blockchain Hash: 0x4B29...A83F</p>
            </div>
            <div class="flex gap-4">
                <div class="glass-card px-6 py-3 flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Voter Turnout</p>
                        <p class="text-xl font-black text-navy">68.4%</p>
                    </div>
                    <div class="w-px h-8 bg-slate-100"></div>
                    <i class="fa-solid fa-users text-royal text-xl"></i>
                </div>
                <button class="bg-navy text-white px-6 rounded-2xl font-bold flex items-center gap-2 hover:bg-royal transition-all">
                    <i class="fa-solid fa-download"></i>
                    <span class="hidden sm:inline">Export PDF</span>
                </button>
            </div>
        </header>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            
            <!-- Presidential Race Rankings -->
            <div class="lg:col-span-8 glass-card p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black text-navy">Presidential Race</h3>
                    <select class="text-xs font-bold bg-slate-50 border-none px-4 py-2 rounded-xl outline-none">
                        <option>Sort by Votes</option>
                        <option>Sort by Party</option>
                    </select>
                </div>

                <div class="space-y-8">
                    <!-- Candidate Rank 1 -->
                    <div class="relative">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gold/20 text-gold flex items-center justify-center font-black">#1</div>
                                <div>
                                    <h4 class="font-bold text-navy">Marco Agapito</h4>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Progressive Alliance</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-navy">856</p>
                                <p class="text-[10px] text-green-500 font-bold uppercase">54.2%</p>
                            </div>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                            <div class="result-bar h-full bg-royal rounded-full" style="width: 54.2%"></div>
                        </div>
                    </div>

                    <!-- Candidate Rank 2 -->
                    <div class="relative">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center font-black">#2</div>
                                <div>
                                    <h4 class="font-bold text-navy">Sarah Jenkins</h4>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">United Students Party</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-navy">684</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">43.2%</p>
                            </div>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                            <div class="result-bar h-full bg-slate-300 rounded-full" style="width: 43.2%"></div>
                        </div>
                    </div>

                    <!-- Abstain -->
                    <div class="relative">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-4 pl-16">
                                <div>
                                    <h4 class="font-bold text-slate-400 italic">Abstain</h4>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-slate-400">42</p>
                                <p class="text-[10px] text-slate-300 font-bold uppercase">2.6%</p>
                            </div>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="result-bar h-full bg-slate-200 rounded-full" style="width: 2.6%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program Participation Pie -->
            <div class="lg:col-span-4 glass-card p-8 flex flex-col">
                <h3 class="text-xl font-black text-navy mb-8">Participation by Program</h3>
                <div class="flex-1 min-h-[300px] relative">
                    <canvas id="participationPie"></canvas>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-100 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Top Turnout</p>
                        <p class="font-black text-royal">IT Program</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Lowest Turnout</p>
                        <p class="font-black text-amber-500">BS Arch</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Blockchain Verifications -->
        <div class="glass-card p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-black text-navy">Blockchain Ledger (Real-time)</h3>
                <button class="text-xs font-bold text-royal flex items-center gap-2">
                    View on Explorer <i class="fa-solid fa-external-link"></i>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tx Hash</th>
                            <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Timestamp</th>
                            <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Position</th>
                            <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-medium">
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-4 font-mono text-royal">0x82f...a12b</td>
                            <td class="py-4 text-slate-500 text-xs">12:44:02 PM</td>
                            <td class="py-4 text-navy">President</td>
                            <td class="py-4"><span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-[10px] font-black uppercase">Confirmed</span></td>
                        </tr>
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-4 font-mono text-royal">0x31a...e89c</td>
                            <td class="py-4 text-slate-500 text-xs">12:43:58 PM</td>
                            <td class="py-4 text-navy">Vice President</td>
                            <td class="py-4"><span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-[10px] font-black uppercase">Confirmed</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        window.onload = function() {
            const ctx = document.getElementById('participationPie').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['IT', 'Engineering', 'Nursing', 'Education', 'Business'],
                    datasets: [{
                        data: [300, 250, 180, 220, 150],
                        backgroundColor: ['#1E3A8A', '#0A1F44', '#FFC107', '#6366f1', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: { size: 11, weight: '600' },
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        };
    </script>
</body>
</html>