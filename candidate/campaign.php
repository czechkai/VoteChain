<?php
require_once '../includes/config.php';
requireRole('candidate');
$role = 'candidate';
$activePage = 'campaign';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Management | VoteChain</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-20">
    <?php $role = 'candidate'; $activePage = 'campaign'; include '../includes/sidebar.php'; ?>

    <header class="h-20 bg-white border-b sticky top-0 z-30 flex items-center justify-between px-8 lg:ml-72">
        <h2 class="text-xl font-black text-navy">Campaign Studio</h2>
        <button class="bg-gold text-navy px-6 py-2 rounded-full font-bold shadow-lg shadow-gold/20">Go Live</button>
    </header>

    <main class="lg:ml-72 p-8 max-w-6xl mx-auto grid lg:grid-cols-2 gap-12">
        <!-- Editor Section -->
        <div class="space-y-8">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border">
                <h3 class="text-lg font-black text-navy mb-6">Create New Post</h3>
                
                <div class="space-y-6">
                    <!-- Media Upload -->
                    <div class="border-2 border-dashed border-slate-200 rounded-3xl p-12 text-center hover:border-royal transition-colors group cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-slate-300 group-hover:text-royal transition-colors mb-4"></i>
                        <p class="text-sm font-bold text-slate-500">Drag and drop images or video</p>
                        <p class="text-[10px] text-slate-400 mt-1 uppercase font-black tracking-widest">Recommended: 1080x1080px</p>
                    </div>

                    <!-- Caption -->
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Caption / Platform Details</label>
                        <textarea placeholder="Tell your voters about your vision..." rows="5" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:ring-2 focus:ring-royal outline-none font-medium resize-none"></textarea>
                    </div>

                    <button class="w-full py-4 bg-navy text-white font-black rounded-2xl hover:bg-royal transition shadow-xl shadow-navy/20">POST TO NEWS FEED</button>
                </div>
            </div>

            <!-- Metrics -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-6 rounded-3xl border shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Views</p>
                    <p class="text-2xl font-black text-navy">15.2k</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Engagement Rate</p>
                    <p class="text-2xl font-black text-emerald-500">8.4%</p>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="space-y-6 sticky top-28 h-fit">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] ml-2">Mobile Preview</h3>
            
            <!-- Facebook-Style Mockup Card -->
            <div class="bg-white rounded-[2rem] shadow-2xl border border-slate-100 overflow-hidden max-w-sm mx-auto">
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-navy flex items-center justify-center font-black text-gold border-2 border-gold/20">JB</div>
                        <div>
                            <p class="text-sm font-bold text-navy">James Blanco</p>
                            <p class="text-[10px] text-slate-400 font-bold">Just now • USC Presidential Candidate</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-ellipsis text-slate-300"></i>
                </div>
                <div class="px-4 pb-4">
                    <p class="text-sm text-navy leading-relaxed italic">"The future of DOrSU belongs to the students who dream of a smarter, more digital campus. Join our vision today!"</p>
                </div>
                <div class="aspect-square bg-slate-100 flex items-center justify-center text-slate-300">
                    <i class="fa-regular fa-image text-6xl"></i>
                </div>
                <div class="p-4 border-t border-slate-50 flex justify-between">
                    <div class="flex gap-4">
                        <i class="fa-regular fa-heart text-xl text-slate-300"></i>
                        <i class="fa-regular fa-comment text-xl text-slate-300"></i>
                        <i class="fa-regular fa-paper-plane text-xl text-slate-300"></i>
                    </div>
                    <i class="fa-regular fa-bookmark text-xl text-slate-300"></i>
                </div>
            </div>
        </div>
    </main>
</body>
</html>