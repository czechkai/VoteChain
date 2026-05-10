<?php
require_once '../includes/config.php';
requireRole('student');
$role = 'student';
$activePage = 'vote';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Now | VoteChain DOrSU</title>
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
                        slate: { 850: '#1e293b', 950: '#0f172a' }
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
        .election-card { background: white; border-radius: 2rem; border: 1px solid #e2e8f0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .election-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05); border-color: #1E3A8A; }
        .status-badge { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 4px 12px; border-radius: 9999px; }
    </style>
</head>
<body class="flex min-h-screen">
    <?php $role = 'student'; $activePage = 'vote'; include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-72 p-4 md:p-8">
        <header class="mb-10">
            <h1 class="text-3xl font-extrabold text-navy">Election Center</h1>
            <p class="text-slate-500 font-medium mt-1">Select an active election to cast your secure blockchain vote.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- USC Election Card -->
            <div class="election-card flex flex-col">
                <div class="p-8 flex-1">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-navy text-white rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-navy/20">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <span class="status-badge bg-green-100 text-green-600">Active</span>
                    </div>
                    
                    <h3 class="text-xl font-extrabold text-navy mb-2">USC General Elections</h3>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mb-6">University Wide</p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex items-center gap-3 text-slate-600">
                            <i class="fa-solid fa-users text-royal w-5"></i>
                            <span class="text-sm font-semibold">12 Candidates Running</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-600">
                            <i class="fa-solid fa-clock text-royal w-5"></i>
                            <span class="text-sm font-semibold">Ends in: <span class="text-navy">12h 45m</span></span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-600">
                            <i class="fa-solid fa-shield-halved text-royal w-5"></i>
                            <span class="text-sm font-semibold">Blockchain Verified</span>
                        </div>
                    </div>
                </div>
                <div class="p-8 pt-0">
                    <a href="ballot_usc.php" class="w-full py-4 bg-navy text-white rounded-2xl font-bold flex items-center justify-center gap-3 hover:bg-royal transition-all shadow-xl shadow-navy/10 group">
                        Proceed to Ballot
                        <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            <!-- Faculty Election Card -->
            <div class="election-card flex flex-col">
                <div class="p-8 flex-1">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-royal text-white rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-royal/20">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <span class="status-badge bg-green-100 text-green-600">Active</span>
                    </div>
                    
                    <h3 class="text-xl font-extrabold text-navy mb-2">FACET Council</h3>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mb-6">Faculty Level</p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex items-center gap-3 text-slate-600">
                            <i class="fa-solid fa-users text-royal w-5"></i>
                            <span class="text-sm font-semibold">8 Candidates Running</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-600">
                            <i class="fa-solid fa-clock text-royal w-5"></i>
                            <span class="text-sm font-semibold">Ends in: <span class="text-navy">08h 12m</span></span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-600">
                            <i class="fa-solid fa-location-dot text-royal w-5"></i>
                            <span class="text-sm font-semibold">Engineering Bldg.</span>
                        </div>
                    </div>
                </div>
                <div class="p-8 pt-0">
                    <a href="ballot_facet.php" class="w-full py-4 bg-navy text-white rounded-2xl font-bold flex items-center justify-center gap-3 hover:bg-royal transition-all shadow-xl shadow-navy/10 group">
                        Proceed to Ballot
                        <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            <!-- Program Election Card -->
            <div class="election-card flex flex-col opacity-75 grayscale-[0.5]">
                <div class="p-8 flex-1">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-slate-200 text-slate-500 rounded-2xl flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-code"></i>
                        </div>
                        <span class="status-badge bg-amber-100 text-amber-600">Upcoming</span>
                    </div>
                    
                    <h3 class="text-xl font-extrabold text-navy mb-2">IT Society Officers</h3>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mb-6">Program Level</p>
                    
                    <div class="space-y-4 mb-8 text-slate-400">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-calendar text-slate-300 w-5"></i>
                            <span class="text-sm font-semibold">Starts: May 15, 2026</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-user-check text-slate-300 w-5"></i>
                            <span class="text-sm font-semibold">Registration Required</span>
                        </div>
                    </div>
                </div>
                <div class="p-8 pt-0">
                    <button disabled class="w-full py-4 bg-slate-100 text-slate-400 rounded-2xl font-bold flex items-center justify-center gap-3 cursor-not-allowed">
                        <i class="fa-solid fa-lock text-xs"></i>
                        Not Yet Open
                    </button>
                </div>
            </div>

        </div>

        <!-- Security Notice -->
        <div class="mt-12 bg-white p-8 rounded-[2rem] border border-slate-100 flex items-center gap-6">
            <div class="hidden sm:flex w-16 h-16 bg-gold/10 text-gold rounded-full items-center justify-center text-2xl flex-shrink-0">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
            <div>
                <h4 class="text-navy font-extrabold">Voter Security Protocol</h4>
                <p class="text-slate-500 text-sm max-w-2xl">Your vote is encrypted and recorded on a private blockchain. Once submitted, it cannot be altered or deleted. Ensure you are alone while casting your vote to maintain ballot secrecy.</p>
            </div>
        </div>
    </main>

    <!-- Mobile Navigation (Same as Newsfeed) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 flex justify-around items-center z-50">
        <a href="dashboard.php" class="text-slate-400"><i class="fa-solid fa-chart-pie text-xl"></i></a>
        <a href="newsfeed.php" class="text-slate-400"><i class="fa-solid fa-newspaper text-xl"></i></a>
        <div class="w-12 h-12 bg-navy rounded-full -mt-10 flex items-center justify-center text-white shadow-xl border-4 border-white">
            <i class="fa-solid fa-box-archive"></i>
        </div>
        <a href="#" class="text-slate-400"><i class="fa-solid fa-square-poll-vertical text-xl"></i></a>
        <a href="#" class="text-slate-400"><i class="fa-solid fa-user-gear text-xl"></i></a>
    </div>

</body>
</html>