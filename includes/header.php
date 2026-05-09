<?php
$role = $role ?? 'student';
$pageTitle = $pageTitle ?? 'Dashboard';
?>

<header class="h-20 bg-white border-b border-slate-100 sticky top-0 z-30 flex items-center justify-between px-6 md:px-8 shadow-sm">
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-navy hover:bg-slate-100 transition-colors">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
        <h2 class="text-xl md:text-2xl font-black text-navy"><?php echo $pageTitle; ?></h2>
    </div>

    <div class="flex items-center gap-4">
        <!-- Search Bar (Desktop) -->
        <div class="hidden md:flex relative flex-1 max-w-sm">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 rounded-xl border border-slate-200 text-sm outline-none focus:border-gold focus:bg-white transition-colors">
        </div>

        <!-- Notifications -->
        <button class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-navy hover:bg-slate-100 transition-colors group">
            <i class="fa-solid fa-bell text-lg"></i>
            <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full"></span>
            
            <!-- Notification Dropdown -->
            <div class="absolute top-full right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl shadow-navy/10 border border-slate-100 hidden group-hover:block p-4 max-h-96 overflow-y-auto">
                <h3 class="font-bold text-navy mb-3">Notifications</h3>
                <div class="space-y-2">
                    <div class="p-3 bg-slate-50 rounded-lg text-sm border-l-4 border-gold">
                        <p class="font-semibold text-navy">Election Status Update</p>
                        <p class="text-slate-600 text-xs mt-1">Voting period ends in 2 hours</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg text-sm border-l-4 border-blue-500">
                        <p class="font-semibold text-navy">New Announcement</p>
                        <p class="text-slate-600 text-xs mt-1">COMELEC has posted updates</p>
                    </div>
                </div>
            </div>
        </button>

        <!-- Dark Mode Toggle (Optional) -->
        <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-navy hover:bg-slate-100 transition-colors">
            <i class="fa-solid fa-moon"></i>
        </button>

        <!-- Profile Dropdown -->
        <div class="relative group">
            <button class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gold to-yellow-600 flex items-center justify-center text-white font-bold text-sm">
                    <?php 
                    $initials = 'U'; // Default
                    if ($role === 'admin') $initials = 'A';
                    elseif ($role === 'candidate') $initials = 'C';
                    echo $initials;
                    ?>
                </div>
                <i class="fa-solid fa-chevron-down text-xs text-slate-600"></i>
            </button>

            <!-- Dropdown Menu -->
            <div class="absolute top-full right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl shadow-navy/10 border border-slate-100 hidden group-hover:block p-2">
                <div class="px-3 py-3 border-b border-slate-100 mb-2">
                    <p class="font-bold text-navy text-sm">Profile</p>
                    <?php if ($role === 'candidate'): ?>
                        <p class="text-slate-600 text-xs">Candidate Account</p>
                    <?php elseif ($role === 'admin'): ?>
                        <p class="text-slate-600 text-xs">Administrator</p>
                    <?php else: ?>
                        <p class="text-slate-600 text-xs">Student Account</p>
                    <?php endif; ?>
                </div>
                <a href="/vc/profile.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-navy hover:bg-slate-50 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-user"></i> My Profile
                </a>
                <a href="/vc/settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-navy hover:bg-slate-50 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
                <button class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>
</header>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar?.classList.toggle('-translate-x-full');
    }
</script>
