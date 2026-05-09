<?php $role = 'admin'; $activePage = 'results'; $pageTitle = 'Live Results'; ?>
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
            <!-- Live Status -->
            <div class="mb-8 p-6 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-[2rem] border border-emerald-200 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center text-white animate-pulse">
                        <i class="fa-solid fa-circle-dot"></i>
                    </div>
                    <div>
                        <p class="font-black text-emerald-700 text-lg">ELECTION LIVE</p>
                        <p class="text-sm text-emerald-600">Votes are being counted in real-time</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-black text-emerald-700">12:34:56</p>
                    <p class="text-sm text-emerald-600">Time remaining</p>
                </div>
            </div>

            <!-- Results Overview -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Voter Turnout -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xl font-black text-navy mb-6">Voter Turnout</h3>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-5xl font-black text-navy">68.4%</p>
                            <p class="text-sm text-slate-600 mt-2">2,847 votes cast out of 4,162 registered voters</p>
                        </div>
                        <div class="text-right">
                            <div class="w-24 h-24">
                                <div class="relative w-full h-full">
                                    <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#FFC107" stroke-width="8" stroke-dasharray="245.04" stroke-dashoffset="79.27" stroke-linecap="round"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="font-black text-sm text-navy">68.4%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Votes -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xl font-black text-navy mb-6">Vote Distribution</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-navy">Valid Votes</span>
                                <span class="font-black text-navy">2,847</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full w-[98%]"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-navy">Spoiled Votes</span>
                                <span class="font-black text-navy">32</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-red-500 h-full w-[1%]"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-navy">Invalid Votes</span>
                                <span class="font-black text-navy">18</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-yellow-500 h-full w-[0.6%]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results by Position -->
            <div class="space-y-8">
                <!-- USC President -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xl font-black text-navy mb-6">USC President Results</h3>
                    <div class="space-y-6">
                        <!-- Candidate 1 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">JB</div>
                                <div>
                                    <p class="font-bold text-navy">James Blanco</p>
                                    <p class="text-xs text-slate-500">BS IT - 4A</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-full" style="width: 42%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">1,194</p>
                                <p class="text-xs text-slate-500">42.0%</p>
                            </div>
                        </div>

                        <!-- Candidate 2 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-bold">SR</div>
                                <div>
                                    <p class="font-bold text-navy">Sarah Rodriguez</p>
                                    <p class="text-xs text-slate-500">BSN - 4B</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-purple-400 to-purple-600 h-full" style="width: 38%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">1,078</p>
                                <p class="text-xs text-slate-500">38.0%</p>
                            </div>
                        </div>

                        <!-- Candidate 3 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold">MC</div>
                                <div>
                                    <p class="font-bold text-navy">Maria Cruz</p>
                                    <p class="text-xs text-slate-500">BSED - 3C</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-full" style="width: 20%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">575</p>
                                <p class="text-xs text-slate-500">20.0%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vice President -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xl font-black text-navy mb-6">Vice President Results</h3>
                    <div class="space-y-6">
                        <!-- Candidate 1 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white font-bold">JP</div>
                                <div>
                                    <p class="font-bold text-navy">John Park</p>
                                    <p class="text-xs text-slate-500">BSCS - 3A</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-teal-400 to-teal-600 h-full" style="width: 55%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">1,563</p>
                                <p class="text-xs text-slate-500">55.0%</p>
                            </div>
                        </div>

                        <!-- Candidate 2 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold">AL</div>
                                <div>
                                    <p class="font-bold text-navy">Angela Lopez</p>
                                    <p class="text-xs text-slate-500">BSBA - 4B</p>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-orange-400 to-orange-600 h-full" style="width: 45%"></div>
                                </div>
                            </div>
                            <div class="text-right ml-6">
                                <p class="font-black text-navy text-lg">1,284</p>
                                <p class="text-xs text-slate-500">45.0%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Results -->
            <div class="flex gap-4 justify-center mt-12">
                <button class="px-8 py-3 bg-slate-100 text-navy rounded-xl font-bold hover:bg-slate-200 transition">
                    <i class="fa-solid fa-download mr-2"></i>Download PDF
                </button>
                <button class="px-8 py-3 bg-navy text-white rounded-xl font-bold hover:bg-royal transition">
                    <i class="fa-solid fa-table mr-2"></i>Export CSV
                </button>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
