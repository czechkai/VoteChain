<?php
$role = $role ?? 'student';
$pageTitle = $pageTitle ?? 'Dashboard';
?>

<style>
    body[data-theme="dark"] {
        background-color: #07111f;
        color: #e5eefb;
    }

    body[data-theme="dark"] header {
        background-color: #0b1730 !important;
        border-color: #1e2a44 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35) !important;
    }

    body[data-theme="dark"] .bg-white {
        background-color: #0e1b33 !important;
    }

    body[data-theme="dark"] .bg-slate-50,
    body[data-theme="dark"] .bg-slate-100,
    body[data-theme="dark"] .bg-blue-50,
    body[data-theme="dark"] .bg-emerald-50,
    body[data-theme="dark"] .bg-amber-50,
    body[data-theme="dark"] .bg-yellow-50,
    body[data-theme="dark"] .bg-red-50,
    body[data-theme="dark"] .bg-purple-50,
    body[data-theme="dark"] .bg-teal-50 {
        background-color: #10213d !important;
    }

    body[data-theme="dark"] .text-navy,
    body[data-theme="dark"] header h2,
    body[data-theme="dark"] .text-slate-900 {
        color: #f8fbff !important;
    }

    body[data-theme="dark"] .text-slate-600,
    body[data-theme="dark"] .text-slate-500,
    body[data-theme="dark"] .text-slate-400 {
        color: #aebbd2 !important;
    }

    body[data-theme="dark"] .border-slate-50,
    body[data-theme="dark"] .border-slate-100,
    body[data-theme="dark"] .border-slate-200,
    body[data-theme="dark"] .border-white\/10 {
        border-color: #24334e !important;
    }

    body[data-theme="dark"] .shadow-sm,
    body[data-theme="dark"] .shadow-lg,
    body[data-theme="dark"] .shadow-2xl {
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25) !important;
    }

    body[data-theme="dark"] input,
    body[data-theme="dark"] textarea,
    body[data-theme="dark"] select {
        background-color: #0b1730 !important;
        color: #f8fbff !important;
        border-color: #263754 !important;
    }

    body[data-theme="dark"] input::placeholder,
    body[data-theme="dark"] textarea::placeholder {
        color: #7d8ea8 !important;
    }

    body[data-theme="dark"] table thead,
    body[data-theme="dark"] table thead tr {
        background-color: #0b172c !important;
    }

    body[data-theme="dark"] table tbody tr:hover {
        background-color: #12213d !important;
    }

    body[data-theme="dark"] footer {
        background-color: #08132a !important;
    }
</style>

<header class="h-20 bg-white border-b border-slate-100 sticky top-0 z-30 flex items-center justify-between px-6 md:px-8 shadow-sm">
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-navy hover:bg-slate-100 transition-colors">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
        <h2 class="text-xl md:text-2xl font-black text-navy"><?php echo $pageTitle; ?></h2>
    </div>

    <div class="flex items-center gap-4">
        <?php if ($role === 'admin'): ?>
        <!-- Search Bar (Desktop) -->
        <div class="hidden md:flex relative flex-1 max-w-sm">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input id="adminSearchInput" type="text" placeholder="Search current page..." data-admin-search class="w-full pl-10 pr-4 py-2.5 bg-slate-50 rounded-xl border border-slate-200 text-sm outline-none focus:border-gold focus:bg-white transition-colors">
        </div>
        <?php endif; ?>

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

        <?php if ($role === 'admin'): ?>
        <!-- Dark Mode Toggle -->
        <button id="themeToggleBtn" type="button" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-navy hover:bg-slate-100 transition-colors" aria-label="Toggle dark mode">
            <i id="themeToggleIcon" class="fa-solid fa-moon"></i>
        </button>
        <?php endif; ?>

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
                <a href="/votechain/profile.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-navy hover:bg-slate-50 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-user"></i> My Profile
                </a>
                <a href="/votechain/settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-navy hover:bg-slate-50 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
                <a href="/votechain/auth/logout.php" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    const ADMIN_THEME_KEY = 'votechain-admin-theme';

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar?.classList.toggle('-translate-x-full');
    }

    function setAdminTheme(theme) {
        document.body.setAttribute('data-theme', theme);

        const themeIcon = document.getElementById('themeToggleIcon');
        if (themeIcon) {
            themeIcon.classList.toggle('fa-moon', theme === 'light');
            themeIcon.classList.toggle('fa-sun', theme === 'dark');
        }

        localStorage.setItem(ADMIN_THEME_KEY, theme);
    }

    function getSearchItems(scope) {
        const explicitItems = Array.from(scope.querySelectorAll('[data-admin-search-item]'));
        if (explicitItems.length) {
            return explicitItems;
        }

        const tableRows = Array.from(scope.querySelectorAll('tbody tr'));
        if (tableRows.length) {
            return tableRows;
        }

        const directChildren = Array.from(scope.children).filter(child => !['SCRIPT', 'STYLE'].includes(child.tagName));
        if (directChildren.length) {
            return directChildren;
        }

        return [];
    }

    function initializeAdminSearch() {
        const searchInput = document.getElementById('adminSearchInput');
        if (!searchInput) return;

        const scope = document.querySelector('main') || document.body;
        const items = getSearchItems(scope);

        const applyFilter = () => {
            const term = searchInput.value.trim().toLowerCase();

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                const visible = !term || text.includes(term);
                item.style.display = visible ? '' : 'none';
            });
        };

        searchInput.addEventListener('input', debounce(applyFilter, 150));
    }

    function initializeAdminThemeToggle() {
        const toggleButton = document.getElementById('themeToggleBtn');
        if (!toggleButton) return;

        const savedTheme = localStorage.getItem(ADMIN_THEME_KEY) || 'light';
        setAdminTheme(savedTheme);

        toggleButton.addEventListener('click', () => {
            const currentTheme = document.body.getAttribute('data-theme') || 'light';
            setAdminTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        initializeAdminSearch();
        initializeAdminThemeToggle();
    });
</script>
