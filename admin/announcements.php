<?php
require_once '../includes/config.php';
requireRole('admin');
$role = 'admin';
$activePage = 'announcements';
$pageTitle = 'Announcements';
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
<body class="min-h-screen flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 lg:ml-72 flex flex-col min-w-0">
        <?php include '../includes/header.php'; ?>

        <main class="p-8 flex-1">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-black text-navy">Announcements</h1>
                <button onclick="openCreateModal()" class="bg-gold text-navy px-6 py-3 rounded-xl font-bold hover:scale-105 transition">
                    <i class="fa-solid fa-plus mr-2"></i>New Announcement
                </button>
            </div>

            <!-- Announcements Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Announcement Card 1 -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full uppercase border border-emerald-200">Active</span>
                                <span class="text-[10px] text-slate-500">2 days ago</span>
                            </div>
                            <h3 class="text-lg font-bold text-navy">Campaign Guidelines Updated</h3>
                        </div>
                        <button class="w-10 h-10 rounded-lg hover:bg-slate-100 transition text-slate-400">
                            <i class="fa-solid fa-ellipsis-v"></i>
                        </button>
                    </div>
                    <p class="text-slate-600 text-sm mb-4 line-clamp-3">New campaign guidelines have been released. All candidates must review and adhere to these guidelines. Failure to comply may result in disqualification from the election.</p>
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-slate-500 text-xs">
                            <i class="fa-solid fa-eye"></i>
                            <span>1,234 views</span>
                        </div>
                        <button class="text-gold font-bold text-sm hover:underline">Read More →</button>
                    </div>
                </div>

                <!-- Announcement Card 2 -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full uppercase border border-emerald-200">Active</span>
                                <span class="text-[10px] text-slate-500">5 days ago</span>
                            </div>
                            <h3 class="text-lg font-bold text-navy">Voting Schedule Confirmed</h3>
                        </div>
                        <button class="w-10 h-10 rounded-lg hover:bg-slate-100 transition text-slate-400">
                            <i class="fa-solid fa-ellipsis-v"></i>
                        </button>
                    </div>
                    <p class="text-slate-600 text-sm mb-4 line-clamp-3">The official voting schedule has been confirmed. Students will have 24 hours to cast their votes on May 14, 2026. Ensure your account is verified before election day.</p>
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-slate-500 text-xs">
                            <i class="fa-solid fa-eye"></i>
                            <span>2,567 views</span>
                        </div>
                        <button class="text-gold font-bold text-sm hover:underline">Read More →</button>
                    </div>
                </div>

                <!-- Announcement Card 3 -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-amber-100 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-3 py-1 rounded-full uppercase border border-amber-200">Scheduled</span>
                                <span class="text-[10px] text-slate-500">Publishing on May 12</span>
                            </div>
                            <h3 class="text-lg font-bold text-navy">Final Reminders</h3>
                        </div>
                        <button class="w-10 h-10 rounded-lg hover:bg-slate-100 transition text-slate-400">
                            <i class="fa-solid fa-ellipsis-v"></i>
                        </button>
                    </div>
                    <p class="text-slate-600 text-sm mb-4 line-clamp-3">Final reminders for students and candidates. Make sure all documents are submitted before the deadline. Voting rules and instructions will be available soon.</p>
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-slate-500 text-xs">
                            <i class="fa-solid fa-clock"></i>
                            <span>Not yet published</span>
                        </div>
                        <button class="text-gold font-bold text-sm hover:underline">Edit →</button>
                    </div>
                </div>

                <!-- Announcement Card 4 -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 opacity-60 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[10px] font-black text-slate-600 bg-slate-50 px-3 py-1 rounded-full uppercase border border-slate-200">Archived</span>
                                <span class="text-[10px] text-slate-500">April 20, 2026</span>
                            </div>
                            <h3 class="text-lg font-bold text-navy">Election Nominees Announced</h3>
                        </div>
                        <button class="w-10 h-10 rounded-lg hover:bg-slate-100 transition text-slate-400">
                            <i class="fa-solid fa-ellipsis-v"></i>
                        </button>
                    </div>
                    <p class="text-slate-600 text-sm mb-4 line-clamp-3">The complete list of election nominees has been announced. 24 candidates have filed their candidacy for various positions in the student body government.</p>
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-slate-500 text-xs">
                            <i class="fa-solid fa-eye"></i>
                            <span>5,432 views</span>
                        </div>
                        <button class="text-slate-400 font-bold text-sm hover:underline">View →</button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center items-center gap-2 mt-12">
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition disabled:opacity-50" disabled>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="w-10 h-10 rounded-lg bg-navy text-white font-bold">1</button>
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">2</button>
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">3</button>
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </main>
    </div>

    <!-- Create/Edit Announcement Modal -->
    <div id="createModal" class="fixed inset-0 z-[60] hidden bg-navy/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-[2rem] overflow-hidden animate-in zoom-in-95 duration-200">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-2xl font-black text-navy">Create Announcement</h3>
                <button onclick="closeCreateModal()" class="w-10 h-10 rounded-full hover:bg-slate-200 transition text-navy">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form class="p-8 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-navy mb-2">Title</label>
                    <input type="text" placeholder="Announcement title..." class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-gold focus:bg-slate-50 transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-navy mb-2">Content</label>
                    <textarea rows="6" placeholder="Announcement content..." class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-gold focus:bg-slate-50 transition resize-none"></textarea>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Status</label>
                        <select class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-gold focus:bg-slate-50 transition">
                            <option>Draft</option>
                            <option selected>Active</option>
                            <option>Scheduled</option>
                            <option>Archived</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Publish Date</label>
                        <input type="date" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-gold focus:bg-slate-50 transition">
                    </div>
                </div>

                <div class="flex gap-4 justify-end pt-6 border-t border-slate-100">
                    <button type="button" onclick="closeCreateModal()" class="px-6 py-3 bg-slate-100 text-navy rounded-xl font-bold hover:bg-slate-200 transition">Cancel</button>
                    <button type="submit" class="px-6 py-3 bg-navy text-white rounded-xl font-bold hover:bg-royal transition">Publish Announcement</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }
        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
