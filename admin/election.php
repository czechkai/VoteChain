<?php $role = 'admin'; $activePage = 'election'; $pageTitle = 'Election Management'; ?>
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

        <main class="p-8">
            <!-- Active Elections Grid -->
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6">Running / Scheduled</h3>
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Card 1 -->
                <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-8">
                        <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-4 py-2 rounded-full uppercase border border-emerald-100">Live Now</span>
                    </div>
                    
                    <div class="w-16 h-16 bg-blue-50 text-royal rounded-3xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-landmark-flag"></i>
                    </div>

                    <h4 class="text-2xl font-black text-navy mb-2 uppercase">USC General Elections 2026</h4>
                    <p class="text-slate-400 text-sm font-medium mb-8">University-wide supreme student council election for all departments.</p>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="p-4 bg-slate-50 rounded-2xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Start Date</p>
                            <p class="text-xs font-bold text-navy">May 24, 08:00 AM</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">End Date</p>
                            <p class="text-xs font-bold text-navy">May 24, 05:00 PM</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                        <div class="flex items-center gap-2">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-200"></div>
                                <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-300"></div>
                                <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-400"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase">42 Candidates</span>
                        </div>
                        <button class="text-royal font-black text-xs hover:underline">MANAGE PORTAL</button>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-8">
                        <span class="text-[10px] font-black text-slate-400 bg-slate-50 px-4 py-2 rounded-full uppercase border border-slate-100">Draft</span>
                    </div>
                    
                    <div class="w-16 h-16 bg-yellow-50 text-gold rounded-3xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-building-user"></i>
                    </div>

                    <h4 class="text-2xl font-black text-navy mb-2 uppercase">FACET Faculty Council</h4>
                    <p class="text-slate-400 text-sm font-medium mb-8">Elections for Faculty of Computing, Engineering & Technology officers.</p>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="p-4 bg-slate-50 rounded-2xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Faculty</p>
                            <p class="text-xs font-bold text-navy uppercase">FACET</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Type</p>
                            <p class="text-xs font-bold text-navy uppercase">Faculty-Level</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black text-amber-500 uppercase">Awaiting Schedule</span>
                        </div>
                        <button class="text-royal font-black text-xs hover:underline">CONFIGURE</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Election Modal -->
    <div id="createModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden bg-navy/60 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-xl rounded-[3rem] overflow-hidden shadow-2xl">
            <div class="p-8 border-b flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-black text-navy">Initialize New Election</h3>
                <button onclick="closeCreateModal()" class="w-10 h-10 rounded-full hover:bg-slate-200 transition"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">Election Title</label>
                    <input type="text" placeholder="e.g. Codebyters Program Election 2026" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">Type</label>
                        <select class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                            <option>University</option>
                            <option>Faculty</option>
                            <option>Program</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">Assignment</label>
                        <select class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                            <option>All Students</option>
                            <option>FACET</option>
                            <option>FALS</option>
                            <option>FTED</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">Start Time</label>
                        <input type="datetime-local" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-navy uppercase tracking-[0.2em] ml-1">End Time</label>
                        <input type="datetime-local" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium outline-none focus:ring-2 focus:ring-royal">
                    </div>
                </div>

                <div class="pt-4 flex gap-4">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 py-4 text-slate-400 font-black rounded-2xl hover:text-navy transition">CANCEL</button>
                    <button type="submit" class="flex-1 py-4 bg-navy text-white font-black rounded-2xl shadow-xl shadow-navy/20 hover:bg-royal transition">CREATE ELECTION</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCreateModal() { document.getElementById('createModal').classList.remove('hidden'); }
        function closeCreateModal() { document.getElementById('createModal').classList.add('hidden'); }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>