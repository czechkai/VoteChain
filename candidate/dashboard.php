<?php
require_once '../includes/config.php';
requireRole('candidate');
$role = 'candidate';
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Dashboard | VoteChain</title>
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
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(226, 232, 240, 0.8); }
    </style>
</head>
<body class="min-h-screen">
    <?php include '../includes/sidebar.php'; ?>

    <header class="h-20 bg-white border-b sticky top-0 z-30 flex items-center justify-between px-8 lg:ml-72">
        <div>
            <h2 class="text-xl font-black text-navy">Creator Studio</h2>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Candidate Analytics Overview</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="px-4 py-2 bg-amber-50 text-amber-600 rounded-full border border-amber-100 text-[10px] font-black uppercase">
                Filing Status: <span class="text-amber-700">Pending Review</span>
            </div>
            <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden border-2 border-white shadow-sm">
                <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100" alt="">
            </div>
        </div>
    </header>

    <main class="lg:ml-72 p-8 space-y-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="glass-card p-6 rounded-[2rem] shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-blue-50 text-royal rounded-2xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <span class="text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg">+12%</span>
                </div>
                <h3 class="text-slate-400 text-xs font-black uppercase tracking-widest">Total Reach</h3>
                <p class="text-3xl font-black text-navy mt-1">2,482</p>
            </div>
            <div class="glass-card p-6 rounded-[2rem] shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-amber-50 text-gold rounded-2xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <span class="text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg">+5.4%</span>
                </div>
                <h3 class="text-slate-400 text-xs font-black uppercase tracking-widest">Endorsements</h3>
                <p class="text-3xl font-black text-navy mt-1">842</p>
            </div>
            <div class="glass-card p-6 rounded-[2rem] shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg">Stable</span>
                </div>
                <h3 class="text-slate-400 text-xs font-black uppercase tracking-widest">Profile Visits</h3>
                <p class="text-3xl font-black text-navy mt-1">12.5k</p>
            </div>
            <div class="glass-card p-6 rounded-[2rem] shadow-sm border-2 border-gold/20">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-gold/10 text-gold rounded-2xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                </div>
                <h3 class="text-slate-400 text-xs font-black uppercase tracking-widest">Current Rank</h3>
                <p class="text-3xl font-black text-navy mt-1">#2 <span class="text-sm font-medium text-slate-400">of 5</span></p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Recent Campaign Posts -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex justify-between items-center px-2">
                    <h3 class="text-lg font-black text-navy">Campaign Performance</h3>
                    <a href="campaign.php" class="text-royal font-bold text-sm hover:underline">View All</a>
                </div>
                <div class="space-y-4">
                    <div class="glass-card p-4 rounded-3xl flex gap-4 items-center">
                        <img src="https://images.unsplash.com/photo-1540317580384-e5d43616b9aa?w=200" class="w-20 h-20 rounded-2xl object-cover shadow-sm" alt="">
                        <div class="flex-1">
                            <h4 class="font-bold text-navy truncate">Modernizing the Student Lounge Proposal</h4>
                            <p class="text-xs text-slate-400 line-clamp-1">Today we discussed our vision for the renovation of the...</p>
                            <div class="flex gap-4 mt-2">
                                <span class="text-[10px] font-bold text-slate-500"><i class="fa-solid fa-comment mr-1"></i> 142</span>
                                <span class="text-[10px] font-bold text-slate-500"><i class="fa-solid fa-share mr-1"></i> 28</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Cards -->
            <div class="space-y-6">
                <div class="bg-navy text-white p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-gold text-[10px] font-black uppercase tracking-widest mb-2">Upcoming Event</p>
                        <h4 class="text-xl font-extrabold mb-4">Miting De Avance: USC Level</h4>
                        <div class="flex items-center gap-3 text-xs text-white/70 mb-4">
                            <i class="fa-solid fa-calendar"></i> May 25, 2026 • 1:00 PM
                        </div>
                        <button class="w-full py-3 bg-gold text-navy font-black rounded-2xl hover:scale-105 transition">SET REMINDER</button>
                    </div>
                    <i class="fa-solid fa-microphone-lines absolute -bottom-4 -right-4 text-8xl text-white/5 rotate-12"></i>
                </div>

                <div class="glass-card p-6 rounded-[2.5rem]">
                    <h4 class="font-black text-navy mb-4">Quick Checklist</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 bg-emerald-50 rounded-2xl border border-emerald-100 cursor-pointer group">
                            <input type="checkbox" checked class="w-5 h-5 rounded-lg accent-emerald-500">
                            <span class="text-sm font-bold text-emerald-800 line-through opacity-60">Submit Filing Docs</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200 cursor-pointer group">
                            <input type="checkbox" class="w-5 h-5 rounded-lg accent-royal">
                            <span class="text-sm font-bold text-navy">Update Campaign Bio</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200 cursor-pointer group">
                            <input type="checkbox" class="w-5 h-5 rounded-lg accent-royal">
                            <span class="text-sm font-bold text-navy">Upload Platform PDF</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>