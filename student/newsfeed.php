<?php
require_once '../includes/config.php';
requireRole('student');
$role = 'student';
$activePage = 'newsfeed';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Feed | VoteChain DOrSU</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        .sidebar-gradient { background: linear-gradient(180deg, #0A1F44 0%, #1E3A8A 100%); }
        .nav-item-active { background: rgba(255, 255, 255, 0.1); border-left: 4px solid #FFC107; color: white !important; }
        .feed-card { background: white; border-radius: 1.5rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .trending-card { background: white; border-radius: 1.5rem; border: 1px solid #e2e8f0; }
        .post-input { background: #f8fafc; border: 1px solid #e2e8f0; transition: all 0.2s; }
        .post-input:focus { background: white; border-color: #1E3A8A; outline: none; }
    </style>
</head>
<body class="flex min-h-screen">
    <?php $role = 'student'; $activePage = 'newsfeed'; include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-72 flex justify-center p-4 md:p-8">
        <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Feed -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Search & Filters -->
                <div class="flex flex-col md:flex-row gap-4 mb-2">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" placeholder="Search election news or candidates..." class="w-full pl-12 pr-6 py-4 feed-card outline-none focus:ring-2 focus:ring-royal/20 transition-all font-medium">
                    </div>
                    <div class="flex gap-2">
                        <button class="px-6 py-4 bg-navy text-white rounded-2xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-sliders"></i> Filter
                        </button>
                    </div>
                </div>

                <!-- COMELEC Announcement Post -->
                <div class="feed-card overflow-hidden animate-in">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-navy rounded-full flex items-center justify-center text-white border-4 border-slate-50">
                                    <i class="fa-solid fa-building-columns text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-navy flex items-center gap-2">
                                        DOrSU COMELEC
                                        <i class="fa-solid fa-circle-check text-royal text-[10px]"></i>
                                    </h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Official Announcement • 2h ago</p>
                                </div>
                            </div>
                            <button class="text-slate-400 hover:text-navy"><i class="fa-solid fa-ellipsis"></i></button>
                        </div>
                        <p class="text-slate-600 mb-4 leading-relaxed">
                            📢 <span class="font-bold text-navy">ATTENTION VOTERS:</span> The 2026 General Student Elections officially starts in 48 hours. Please ensure your accounts are verified and your institutional emails are active. Blockchain keys will be distributed via encrypted notification.
                        </p>
                        <div class="rounded-2xl overflow-hidden mb-4 bg-slate-100 aspect-video relative group cursor-pointer">
                            <div class="absolute inset-0 bg-navy/20 group-hover:bg-navy/10 transition-all"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center text-navy shadow-xl">
                                    <i class="fa-solid fa-play ml-1"></i>
                                </div>
                            </div>
                            <div class="absolute bottom-4 left-4 right-4 bg-white/10 backdrop-blur-md p-3 rounded-xl border border-white/20">
                                <p class="text-white text-xs font-bold">How to Vote: Step-by-Step Blockchain Guide</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <div class="flex gap-6">
                                <button class="flex items-center gap-2 text-slate-500 hover:text-royal font-bold text-sm transition-colors">
                                    <i class="fa-regular fa-thumbs-up"></i> 1.2k
                                </button>
                                <button class="flex items-center gap-2 text-slate-500 hover:text-royal font-bold text-sm transition-colors">
                                    <i class="fa-regular fa-comment"></i> 45
                                </button>
                            </div>
                            <button class="flex items-center gap-2 text-slate-500 hover:text-royal font-bold text-sm transition-colors">
                                <i class="fa-solid fa-share-nodes"></i> Share
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Candidate Post -->
                <div class="feed-card overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gold rounded-full flex items-center justify-center text-navy font-black border-4 border-slate-50">
                                    MA
                                </div>
                                <div>
                                    <h4 class="font-bold text-navy flex items-center gap-2">
                                        Marco Agapito
                                        <span class="text-[9px] bg-gold/20 text-navy px-2 py-0.5 rounded-full font-black uppercase">Candidate</span>
                                    </h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Running for President • 5h ago</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-slate-600 mb-4 leading-relaxed font-medium">
                            The future of DOrSU is digital. Our "Tech-Forward Campus" initiative aims to integrate better Wi-Fi access in every building and a streamlined digital ID system. Together, we can build a smarter university. 🏛️💻 <br><br>
                            <span class="text-royal font-bold">#MarcoForPresident #DOrSUTechForward #VoteChain2026</span>
                        </p>
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <div class="aspect-square bg-slate-200 rounded-xl overflow-hidden">
                                <div class="w-full h-full bg-[url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80')] bg-cover"></div>
                            </div>
                            <div class="aspect-square bg-slate-200 rounded-xl overflow-hidden">
                                <div class="w-full h-full bg-[url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80')] bg-cover"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <div class="flex gap-6">
                                <button class="flex items-center gap-2 text-royal font-bold text-sm transition-colors">
                                    <i class="fa-solid fa-thumbs-up"></i> 856
                                </button>
                                <button class="flex items-center gap-2 text-slate-500 hover:text-royal font-bold text-sm transition-colors">
                                    <i class="fa-regular fa-comment"></i> 128
                                </button>
                            </div>
                            <button class="flex items-center gap-2 text-slate-500 hover:text-royal font-bold text-sm transition-colors">
                                <i class="fa-solid fa-share-nodes"></i> Share
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Trending Candidates -->
                <div class="trending-card p-6 sticky top-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-extrabold text-navy">Trending Now</h3>
                        <i class="fa-solid fa-fire text-orange-500"></i>
                    </div>
                    
                    <div class="space-y-5">
                        <div class="flex items-center justify-between group cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center font-bold text-navy group-hover:bg-royal/10 transition-colors">
                                    1
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-navy">Marco Agapito</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Presidential Race</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black text-green-500">+12%</p>
                                <p class="text-[8px] text-slate-400 uppercase font-bold">Engagement</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between group cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center font-bold text-navy group-hover:bg-royal/10 transition-colors">
                                    2
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-navy">Sarah Jenkins</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Vice President</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black text-green-500">+8%</p>
                                <p class="text-[8px] text-slate-400 uppercase font-bold">Engagement</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between group cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center font-bold text-navy group-hover:bg-royal/10 transition-colors">
                                    3
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-navy">#DOrSUVotes</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Trending Tag</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black text-royal">2.4k</p>
                                <p class="text-[8px] text-slate-400 uppercase font-bold">Posts</p>
                            </div>
                        </div>
                    </div>

                    <button class="w-full mt-8 py-3 bg-slate-50 text-navy font-bold text-xs rounded-xl hover:bg-royal hover:text-white transition-all border border-slate-100">
                        View Ranking
                    </button>
                </div>

                <!-- Recommended Groups -->
                <div class="trending-card p-6">
                    <h3 class="text-sm font-extrabold text-navy mb-4 uppercase tracking-widest">Recommended</h3>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-royal/10 text-royal rounded-lg flex items-center justify-center text-xs">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-navy">IT Student Council</p>
                                <p class="text-[9px] text-slate-400">450 Members</p>
                            </div>
                            <button class="text-royal font-bold text-[10px]">Follow</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile Menu Bottom Nav -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 flex justify-around items-center z-50">
        <a href="dashboard.php" class="text-slate-400"><i class="fa-solid fa-chart-pie text-xl"></i></a>
        <a href="#" class="text-royal"><i class="fa-solid fa-newspaper text-xl"></i></a>
        <div class="w-12 h-12 bg-navy rounded-full -mt-10 flex items-center justify-center text-white shadow-xl border-4 border-white">
            <i class="fa-solid fa-box-archive"></i>
        </div>
        <a href="#" class="text-slate-400"><i class="fa-solid fa-square-poll-vertical text-xl"></i></a>
        <a href="#" class="text-slate-400"><i class="fa-solid fa-user-gear text-xl"></i></a>
    </div>

</body>
</html>