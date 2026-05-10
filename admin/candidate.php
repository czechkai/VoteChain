<?php
require_once '../includes/config.php';
requireRole('admin');
$role = 'admin';
$activePage = 'candidate';
$pageTitle = 'Candidate Applications';
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
<body class="min-h-screen flex flex-col">

    <?php include '../includes/sidebar.php'; ?>

    <div class="lg:ml-72 flex flex-col min-w-0 min-h-screen">
        <?php include '../includes/header.php'; ?>

        <main class="p-8">
            <!-- Filter Bar -->
            <div class="bg-white p-4 rounded-[2rem] shadow-sm border border-slate-100 mb-8 flex flex-wrap gap-4 items-center">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input id="candidateSearchInput" type="text" placeholder="Search by name or program..." class="w-full pl-10 pr-4 py-3 bg-slate-50 rounded-2xl border-none text-sm outline-none">
                </div>
                <select id="candidateStatusFilter" class="bg-slate-50 text-sm font-bold border-none rounded-2xl px-6 py-3 outline-none">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                </select>
                <select id="candidatePositionFilter" class="bg-slate-50 text-sm font-bold border-none rounded-2xl px-6 py-3 outline-none">
                    <option value="">All Positions</option>
                    <option value="usc president">USC President</option>
                    <option value="usc secretary">USC Secretary</option>
                </select>
            </div>

            <!-- Table-Card Hybrid -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Candidate Info</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Position</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Requirements</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                        <tbody class="divide-y divide-slate-50">
                        <!-- Row 1 -->
                        <tr data-admin-search-item class="hover:bg-slate-50/50 transition">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                        <img src="https://i.pravatar.cc/150?u=1" alt="">
                                    </div>
                                    <div>
                                        <p class="font-bold text-navy">James Blanco</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">BSIT - 3A</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-xs font-black text-royal bg-blue-50 px-3 py-1.5 rounded-lg uppercase">USC President</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex gap-1">
                                    <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white" title="COC"><i class="fa-solid fa-check"></i></div>
                                    <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white" title="COR"><i class="fa-solid fa-check"></i></div>
                                    <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white" title="Evaluation"><i class="fa-solid fa-check"></i></div>
                                    <div class="w-6 h-6 bg-amber-400 rounded text-[10px] flex items-center justify-center text-white" title="Good Moral"><i class="fa-solid fa-clock"></i></div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-3 py-1.5 rounded-full uppercase border border-amber-100">Pending Review</span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <button onclick="showDetailsModal()" class="w-10 h-10 rounded-xl bg-slate-100 text-navy hover:bg-navy hover:text-white transition">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <!-- Row 2 -->
                        <tr data-admin-search-item class="hover:bg-slate-50/50 transition">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                        <img src="https://i.pravatar.cc/150?u=2" alt="">
                                    </div>
                                    <div>
                                        <p class="font-bold text-navy">Sarah Jenkins</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">BSN - 4B</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-xs font-black text-royal bg-blue-50 px-3 py-1.5 rounded-lg uppercase">USC Secretary</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex gap-1">
                                    <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                    <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                    <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                    <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full uppercase border border-emerald-100">Approved</span>
                            </td>
                            <td class="px-8 py-5 text-right text-slate-300">
                                <button class="w-10 h-10 rounded-xl bg-slate-100 text-navy opacity-50 cursor-not-allowed">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden bg-navy/60 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-2xl rounded-[3rem] overflow-hidden animate-in zoom-in-95 duration-200">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-black text-navy">Review Application</h3>
                <button onclick="closeDetailsModal()" class="w-10 h-10 rounded-full hover:bg-slate-200 transition"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-8 space-y-8 max-h-[70vh] overflow-y-auto">
                <div class="flex gap-6 items-center">
                    <div class="w-24 h-24 rounded-[2rem] bg-slate-200 border-4 border-white shadow-xl overflow-hidden">
                        <img src="https://i.pravatar.cc/150?u=1" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h4 class="text-2xl font-black text-navy">James Blanco</h4>
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Candidate for USC President</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400">COC Document</span>
                        <a href="#" class="text-royal font-black text-[10px] hover:underline">VIEW PDF</a>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400">COR (Current Sem)</span>
                        <a href="#" class="text-royal font-black text-[10px] hover:underline">VIEW PDF</a>
                    </div>
                </div>

                <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100">
                    <h5 class="text-xs font-black text-royal uppercase tracking-widest mb-2">Admin Notes</h5>
                    <textarea class="w-full bg-white border-none rounded-2xl p-4 text-sm outline-none focus:ring-2 focus:ring-royal" placeholder="Add comments for rejection or internal notes..."></textarea>
                </div>
            </div>
            <div class="p-8 bg-slate-50 grid grid-cols-2 gap-4">
                <button onclick="closeDetailsModal()" class="py-4 bg-red-50 text-red-500 font-black rounded-2xl border border-red-100 hover:bg-red-100 transition">REJECT CANDIDATE</button>
                <button onclick="closeDetailsModal()" class="py-4 bg-navy text-white font-black rounded-2xl shadow-xl shadow-navy/20 hover:bg-royal transition">APPROVE CANDIDATE</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('candidateSearchInput');
            const statusFilter = document.getElementById('candidateStatusFilter');
            const positionFilter = document.getElementById('candidatePositionFilter');
            const rows = document.querySelectorAll('tr[data-admin-search-item]');

            if (!searchInput || !statusFilter || !positionFilter || !rows.length) {
                return;
            }

            const applyFilters = () => {
                const searchTerm = searchInput.value.trim().toLowerCase();
                const selectedStatus = statusFilter.value;
                const selectedPosition = positionFilter.value;

                rows.forEach((row) => {
                    const rowText = row.textContent.toLowerCase();
                    const rowStatus = row.querySelector('td:nth-child(4) span')?.textContent.toLowerCase() || '';
                    const rowPosition = row.querySelector('td:nth-child(2) span')?.textContent.toLowerCase() || '';

                    const matchesSearch = !searchTerm || rowText.includes(searchTerm);
                    const matchesStatus = !selectedStatus || rowStatus.includes(selectedStatus);
                    const matchesPosition = !selectedPosition || rowPosition.includes(selectedPosition);

                    row.style.display = matchesSearch && matchesStatus && matchesPosition ? '' : 'none';
                });
            };

            searchInput.addEventListener('input', applyFilters);
            statusFilter.addEventListener('change', applyFilters);
            positionFilter.addEventListener('change', applyFilters);
        });

        function showDetailsModal() { document.getElementById('detailsModal').classList.remove('hidden'); }
        function closeDetailsModal() { document.getElementById('detailsModal').classList.add('hidden'); }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>