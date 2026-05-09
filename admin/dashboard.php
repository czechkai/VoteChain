<?php $role = 'admin'; $activePage = 'dashboard'; $pageTitle = 'Admin Dashboard'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <!-- Stats Grid -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-bold">Total Candidates</p>
                            <p class="text-4xl font-black text-navy mt-2">24</p>
                        </div>
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-2xl text-blue-500">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-bold">Filed Applications</p>
                            <p class="text-4xl font-black text-navy mt-2">18</p>
                        </div>
                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-2xl text-emerald-500">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-bold">Pending Review</p>
                            <p class="text-4xl font-black text-navy mt-2">6</p>
                        </div>
                        <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-2xl text-amber-500">
                            <i class="fa-solid fa-hourglass-end"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-bold">Approved</p>
                            <p class="text-4xl font-black text-navy mt-2">16</p>
                        </div>
                        <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center text-2xl text-green-600">
                            <i class="fa-solid fa-thumbs-up"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidate Filings Table -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-2xl font-black text-navy">Candidate Filing Status</h3>
                    <input type="text" placeholder="Search candidates..." class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm outline-none focus:border-gold">
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Candidate</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Position</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Documents</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Progress</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-slate-600 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">JB</div>
                                        <div>
                                            <p class="font-bold text-navy">James Blanco</p>
                                            <p class="text-xs text-slate-500">BS IT - 4A</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-sm font-bold text-slate-600">USC President</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex gap-1">
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-slate-300 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-times"></i></div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-gradient-to-r from-royal to-gold h-full w-[80%]"></div>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">4/5</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-xs font-black text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg uppercase border border-amber-100">Pending</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <button class="w-10 h-10 rounded-xl bg-slate-50 text-navy hover:bg-blue-50 hover:text-blue-600 transition">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-bold">SR</div>
                                        <div>
                                            <p class="font-bold text-navy">Sarah Rodriguez</p>
                                            <p class="text-xs text-slate-500">BSN - 4B</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-sm font-bold text-slate-600">Vice President</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex gap-1">
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-gradient-to-r from-royal to-gold h-full w-[100%]"></div>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">5/5</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg uppercase border border-emerald-100">Approved</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <button class="w-10 h-10 rounded-xl bg-slate-50 text-navy hover:bg-blue-50 hover:text-blue-600 transition">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold">MC</div>
                                        <div>
                                            <p class="font-bold text-navy">Maria Cruz</p>
                                            <p class="text-xs text-slate-500">BSED - 3C</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-sm font-bold text-slate-600">Treasurer</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex gap-1">
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-emerald-500 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-check"></i></div>
                                        <div class="w-6 h-6 bg-slate-300 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-times"></i></div>
                                        <div class="w-6 h-6 bg-slate-300 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-times"></i></div>
                                        <div class="w-6 h-6 bg-slate-300 rounded text-[10px] flex items-center justify-center text-white"><i class="fa-solid fa-times"></i></div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-gradient-to-r from-royal to-gold h-full w-[40%]"></div>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">2/5</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-xs font-black text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg uppercase border border-amber-100">Pending</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <button class="w-10 h-10 rounded-xl bg-slate-50 text-navy hover:bg-blue-50 hover:text-blue-600 transition">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
