<?php $showLogout = isset($_GET['logout']) && $_GET['logout'] === '1'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoteChain | DOrSU Secure Student Elections</title>
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
                    },
                    borderRadius: {
                        '2xl': '1rem',
                        '3xl': '1.5rem',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-nav { background: rgba(10, 31, 68, 0.9); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .hero-gradient { background: radial-gradient(circle at top right, #1E3A8A, #0A1F44); }
        .step-line::after { content: ''; position: absolute; top: 50%; right: -50%; width: 100%; height: 2px; background: #e2e8f0; z-index: -1; }
        @media (max-width: 768px) { .step-line::after { display: none; } }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 scroll-smooth">

    <!-- Sticky Navbar -->
    <nav class="fixed w-full z-[100] glass-nav">
        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gold rounded-lg flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-link text-navy text-xl"></i>
                </div>
                <span class="text-2xl font-extrabold text-white tracking-tight">VOTE<span class="text-gold">CHAIN</span></span>
            </div>
            
            <div class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-200">
                <a href="#process" class="hover:text-gold transition">Election Process</a>
                <a href="#levels" class="hover:text-gold transition">Levels</a>
                <a href="#students" class="hover:text-gold transition">For Students</a>
                <a href="#candidates" class="hover:text-gold transition">For Candidates</a>
                <a href="#announcements" class="hover:text-gold transition">Announcements</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="auth/login.php" class="text-white hover:text-gold px-4 font-bold">Login</a>
                <a href="auth/register.php" class="bg-gold text-navy px-6 py-2.5 rounded-full font-bold hover:shadow-lg hover:shadow-gold/20 transition transform hover:-translate-y-0.5">Register</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="pt-40 pb-32 px-6 hero-gradient text-white overflow-hidden relative">
        <div class="absolute inset-0 opacity-10" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-gold text-xs font-bold mb-8">
                    <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                    SECURE BLOCKCHAIN LEDGER ACTIVE
                </div>
                <h1 class="text-5xl lg:text-7xl font-extrabold leading-[1.1] mb-6">
                    The Future of <br><span class="text-gold">DOrSU Elections</span>
                </h1>
                <p class="text-lg text-blue-100/80 mb-10 max-w-lg leading-relaxed">
                    A decentralized, tamper-proof, and transparent voting ecosystem designed for the Davao Oriental State University community.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="auth/register.php" class="bg-gold text-navy px-10 py-4 rounded-2xl font-bold shadow-2xl shadow-gold/20 hover:scale-105 transition">Start Voting Now</a>
                    <a href="#why" class="bg-white/10 border border-white/20 backdrop-blur-md px-10 py-4 rounded-2xl font-bold hover:bg-white/20 transition">Learn More</a>
                </div>
            </div>

            <!-- Dashboard Preview Graphics -->
            <div class="relative">
                <div class="bg-slate-900/50 p-3 rounded-[2.5rem] border border-white/10 shadow-2xl backdrop-blur-sm">
                    <div class="bg-white rounded-[2rem] overflow-hidden shadow-inner">
                        <!-- Mockup Top Bar -->
                        <div class="bg-slate-50 border-b p-4 flex justify-between items-center">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                            </div>
                            <div class="h-4 w-32 bg-slate-200 rounded-full"></div>
                        </div>
                        <!-- Mockup Content -->
                        <div class="p-6 space-y-6">
                            <div class="flex gap-4">
                                <div class="h-24 w-1/3 bg-blue-50 rounded-2xl border border-blue-100 p-4">
                                    <div class="h-2 w-12 bg-blue-200 rounded mb-3"></div>
                                    <div class="h-6 w-full bg-blue-600 rounded-lg"></div>
                                </div>
                                <div class="h-24 w-1/3 bg-gold/10 rounded-2xl border border-gold/20 p-4">
                                    <div class="h-2 w-12 bg-gold/40 rounded mb-3"></div>
                                    <div class="h-6 w-full bg-gold rounded-lg"></div>
                                </div>
                                <div class="h-24 w-1/3 bg-green-50 rounded-2xl border border-green-100 p-4">
                                    <div class="h-2 w-12 bg-green-200 rounded mb-3"></div>
                                    <div class="h-6 w-full bg-green-600 rounded-lg"></div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="h-12 w-full bg-slate-50 border rounded-xl flex items-center px-4 justify-between">
                                    <div class="flex gap-3 items-center">
                                        <div class="w-6 h-6 bg-slate-200 rounded-full"></div>
                                        <div class="h-3 w-32 bg-slate-200 rounded"></div>
                                    </div>
                                    <div class="h-6 w-16 bg-blue-100 rounded-lg"></div>
                                </div>
                                <div class="h-12 w-full bg-slate-50 border rounded-xl flex items-center px-4 justify-between">
                                    <div class="flex gap-3 items-center">
                                        <div class="w-6 h-6 bg-slate-200 rounded-full"></div>
                                        <div class="h-3 w-40 bg-slate-200 rounded"></div>
                                    </div>
                                    <div class="h-6 w-16 bg-blue-100 rounded-lg"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Why VoteChain (Benefits) -->
    <section id="why" class="py-24 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-4 gap-8">
            <div class="lg:col-span-1">
                <h2 class="text-3xl font-extrabold text-navy leading-tight">Why Choose VoteChain?</h2>
                <p class="text-slate-500 mt-4">Security and transparency are at the core of our system.</p>
            </div>
            <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:shadow-xl transition group">
                <i class="fa-solid fa-shield-halved text-royal text-3xl mb-6 group-hover:scale-110 transition block"></i>
                <h3 class="font-bold text-xl mb-3">Tamper-Proof</h3>
                <p class="text-slate-500 text-sm">Once a vote is cast, blockchain technology ensures it cannot be altered or deleted.</p>
            </div>
            <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:shadow-xl transition group">
                <i class="fa-solid fa-bolt text-royal text-3xl mb-6 group-hover:scale-110 transition block"></i>
                <h3 class="font-bold text-xl mb-3">Instant Counting</h3>
                <p class="text-slate-500 text-sm">No more manual tallying. Results are calculated in real-time as the blockchain updates.</p>
            </div>
            <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:shadow-xl transition group">
                <i class="fa-solid fa-fingerprint text-royal text-3xl mb-6 group-hover:scale-110 transition block"></i>
                <h3 class="font-bold text-xl mb-3">Verified Identity</h3>
                <p class="text-slate-500 text-sm">Integration with DOrSU records ensures only eligible students can participate.</p>
            </div>
        </div>
    </section>

    <!-- Election Levels -->
    <section id="levels" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6 text-center mb-16">
            <h2 class="text-4xl font-extrabold text-navy">Election Levels</h2>
            <p class="text-slate-500 mt-4 max-w-2xl mx-auto">Vote for your leaders across multiple administrative tiers of the university.</p>
        </div>
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 text-center hover:border-royal transition transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-landmark-flag text-royal text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-navy mb-4">University</h3>
                <p class="text-slate-500 text-sm mb-6">Supreme Student Council elections for the entire DOrSU campus.</p>
                <span class="text-xs font-bold text-royal px-4 py-1.5 bg-blue-50 rounded-full">ALL STUDENTS ELIGIBLE</span>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 text-center hover:border-royal transition transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-yellow-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-building-user text-gold text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-navy mb-4">Faculty</h3>
                <p class="text-slate-500 text-sm mb-6">Organization elections specific to your Faculty (e.g. FACET, FALS, FTED).</p>
                <span class="text-xs font-bold text-gold px-4 py-1.5 bg-yellow-50 rounded-full">FACULTY-SPECIFIC</span>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 text-center hover:border-royal transition transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-green-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-user-graduate text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-navy mb-4">Program</h3>
                <p class="text-slate-500 text-sm mb-6">Course-level organization elections (e.g. Codebyters, JPICE).</p>
                <span class="text-xs font-bold text-green-600 px-4 py-1.5 bg-green-50 rounded-full">PROGRAM-SPECIFIC</span>
            </div>
        </div>
    </section>

    <!-- Election Process Timeline -->
    <section id="process" class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-extrabold text-navy text-center mb-20">How to Vote</h2>
            <div class="grid md:grid-cols-4 gap-12 relative">
                <div class="text-center relative step-line">
                    <div class="w-16 h-16 bg-navy text-gold rounded-full flex items-center justify-center mx-auto mb-6 font-bold text-xl shadow-xl z-10 relative">1</div>
                    <h4 class="font-bold mb-2">Register</h4>
                    <p class="text-slate-500 text-xs px-4">Create your account using DOrSU ID and details.</p>
                </div>
                <div class="text-center relative step-line">
                    <div class="w-16 h-16 bg-navy text-gold rounded-full flex items-center justify-center mx-auto mb-6 font-bold text-xl shadow-xl z-10 relative">2</div>
                    <h4 class="font-bold mb-2">Login</h4>
                    <p class="text-slate-500 text-xs px-4">Authenticate securely via the student dashboard.</p>
                </div>
                <div class="text-center relative step-line">
                    <div class="w-16 h-16 bg-navy text-gold rounded-full flex items-center justify-center mx-auto mb-6 font-bold text-xl shadow-xl z-10 relative">3</div>
                    <h4 class="font-bold mb-2">Cast Vote</h4>
                    <p class="text-slate-500 text-xs px-4">Select your candidates for USC, Faculty, and Program.</p>
                </div>
                <div class="text-center relative">
                    <div class="w-16 h-16 bg-gold text-navy rounded-full flex items-center justify-center mx-auto mb-6 font-bold text-xl shadow-xl z-10 relative border-4 border-navy">4</div>
                    <h4 class="font-bold mb-2">Chain Record</h4>
                    <p class="text-slate-500 text-xs px-4">Your vote is encrypted and linked to the blockchain.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcement Preview -->
    <section id="announcements" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-3 gap-12">
            <div class="lg:col-span-1">
                <h2 class="text-3xl font-extrabold text-navy">Latest Updates</h2>
                <p class="text-slate-500 mt-4 mb-8">Stay informed about the ongoing election schedule and reminders from the COMELEC.</p>
                <a href="#" class="text-royal font-bold flex items-center gap-2 hover:gap-4 transition-all">
                    View all announcements <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="lg:col-span-2 space-y-4">
                <div class="p-6 bg-white rounded-2xl border border-slate-200 flex gap-6 items-start hover:shadow-md transition cursor-pointer">
                    <div class="min-w-[60px] h-20 bg-blue-50 rounded-xl flex flex-col items-center justify-center text-royal">
                        <span class="text-lg font-extrabold">24</span>
                        <span class="text-[10px] uppercase font-bold">May</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold text-gold uppercase tracking-widest bg-navy px-2 py-0.5 rounded">Election Day</span>
                        <h4 class="font-bold text-navy mt-2 mb-1">Official Start of 2026 USC Elections</h4>
                        <p class="text-slate-500 text-sm">The voting portal will open at exactly 8:00 AM PST for all eligible voters.</p>
                    </div>
                </div>
                <div class="p-6 bg-white rounded-2xl border border-slate-200 flex gap-6 items-start hover:shadow-md transition cursor-pointer">
                    <div class="min-w-[60px] h-20 bg-slate-100 rounded-xl flex flex-col items-center justify-center text-slate-400">
                        <span class="text-lg font-extrabold">20</span>
                        <span class="text-[10px] uppercase font-bold">May</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded">Campaign</span>
                        <h4 class="font-bold text-navy mt-2 mb-1">Miting de Avance: Faculty Level</h4>
                        <p class="text-slate-500 text-sm">Meet your candidates for FACET and FALS at the University Gym.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Logout Status Modal -->
    <div id="logoutModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-[200]">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 p-8">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100 text-green-700">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-navy">Logout successful</h3>
                        <p class="text-slate-500 text-sm mt-1">You have been signed out.</p>
                    </div>
                </div>
                <button type="button" id="logoutClose" class="text-slate-400 hover:text-navy">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" id="logoutOk" class="px-6 py-3 bg-navy text-white rounded-2xl font-bold hover:bg-royal transition-all">OK</button>
            </div>
        </div>
    </div>

    <script>
        const showLogoutModal = <?php echo $showLogout ? 'true' : 'false'; ?>;
        const logoutModal = document.getElementById('logoutModal');
        const logoutClose = document.getElementById('logoutClose');
        const logoutOk = document.getElementById('logoutOk');

        function closeLogoutModal() {
            logoutModal.classList.add('hidden');
            logoutModal.classList.remove('flex');
            if (window.history.replaceState) {
                const cleanUrl = window.location.href.replace(/\?logout=1$/, '');
                window.history.replaceState({}, document.title, cleanUrl);
            }
        }

        if (showLogoutModal) {
            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('flex');
        }

        logoutClose?.addEventListener('click', closeLogoutModal);
        logoutOk?.addEventListener('click', closeLogoutModal);
        logoutModal?.addEventListener('click', (e) => {
            if (e.target === logoutModal) closeLogoutModal();
        });
    </script>

    <!-- COMELEC Officers -->
    <section class="py-24 bg-navy text-white">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-extrabold mb-16">The COMELEC Team</h2>
            <div class="grid md:grid-cols-4 gap-8">
                <div class="space-y-4">
                    <div class="w-32 h-32 bg-slate-700 rounded-full mx-auto border-4 border-gold/30"></div>
                    <h5 class="font-bold">Juan Dela Cruz</h5>
                    <p class="text-gold text-xs font-bold uppercase">Commissioner</p>
                </div>
                <div class="space-y-4">
                    <div class="w-32 h-32 bg-slate-700 rounded-full mx-auto border-4 border-gold/30"></div>
                    <h5 class="font-bold">Maria Clara</h5>
                    <p class="text-gold text-xs font-bold uppercase">Secretary</p>
                </div>
                <div class="space-y-4">
                    <div class="w-32 h-32 bg-slate-700 rounded-full mx-auto border-4 border-gold/30"></div>
                    <h5 class="font-bold">Simeon Ibarra</h5>
                    <p class="text-gold text-xs font-bold uppercase">Public Relations</p>
                </div>
                <div class="space-y-4">
                    <div class="w-32 h-32 bg-slate-700 rounded-full mx-auto border-4 border-gold/30"></div>
                    <h5 class="font-bold">Basilio Santos</h5>
                    <p class="text-gold text-xs font-bold uppercase">Technical Lead</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t py-20 px-6">
        <div class="max-w-7xl mx-auto grid md:grid-cols-4 gap-12">
            <div class="col-span-1 md:col-span-1">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 bg-navy rounded flex items-center justify-center">
                        <i class="fa-solid fa-link text-gold text-sm"></i>
                    </div>
                    <span class="text-xl font-extrabold text-navy tracking-tight">VOTECHAIN</span>
                </div>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Building trust in student governance through innovative blockchain technology. Secure, transparent, and immutable.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center hover:bg-navy hover:text-gold transition"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center hover:bg-navy hover:text-gold transition"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center hover:bg-navy hover:text-gold transition"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            <div>
                <h6 class="font-bold text-navy mb-6 uppercase tracking-wider text-xs">For Students</h6>
                <ul class="space-y-4 text-sm text-slate-500">
                    <li><a href="#" class="hover:text-royal transition">Voting Guide</a></li>
                    <li><a href="#" class="hover:text-royal transition">Election Integrity</a></li>
                    <li><a href="#" class="hover:text-royal transition">F.A.Q</a></li>
                </ul>
            </div>
            <div>
                <h6 class="font-bold text-navy mb-6 uppercase tracking-wider text-xs">For Candidates</h6>
                <ul class="space-y-4 text-sm text-slate-500">
                    <li><a href="#" class="hover:text-royal transition">Filing of Candidacy</a></li>
                    <li><a href="#" class="hover:text-royal transition">Campaign Rules</a></li>
                    <li><a href="#" class="hover:text-royal transition">Media Kit</a></li>
                </ul>
            </div>
            <div>
                <h6 class="font-bold text-navy mb-6 uppercase tracking-wider text-xs">Contact COMELEC</h6>
                <p class="text-sm text-slate-500 mb-4">DOrSU Main Campus, Guang-guang, Dahican, City of Mati</p>
                <p class="text-sm font-bold text-navy">comelec@dorsu.edu.ph</p>
            </div>
        </div>
        <div class="max-w-7xl mx-auto border-t mt-16 pt-8 text-center text-slate-400 text-xs">
            &copy; 2026 VoteChain | BS Information Technology 3A - DOrSU
        </div>
    </footer>

</body>
</html>