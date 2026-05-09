// VoteChain Application Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    initializeSidebarToggle();
    initializeTooltips();
    initializeDropdowns();
}

// Sidebar Toggle
function initializeSidebarToggle() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');

    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnMenuBtn = mobileMenuBtn.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnMenuBtn) {
                sidebar.classList.add('-translate-x-full');
            }
        });

        // Close sidebar when a link is clicked
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
            });
        });
    }
}

// Initialize Tooltips
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseover', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-box';
            tooltip.textContent = tooltipText;
            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
        });

        element.addEventListener('mouseout', function() {
            const tooltip = document.querySelector('.tooltip-box');
            if (tooltip) tooltip.remove();
        });
    });
}

// Initialize Dropdowns
function initializeDropdowns() {
    const dropdownTriggers = document.querySelectorAll('[data-dropdown]');
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdownId = this.getAttribute('data-dropdown');
            const dropdown = document.getElementById(dropdownId);

            if (dropdown) {
                const isVisible = !dropdown.classList.contains('hidden');
                document.querySelectorAll('[id$="Dropdown"]').forEach(el => {
                    el.classList.add('hidden');
                });

                if (!isVisible) {
                    dropdown.classList.remove('hidden');
                }
            }
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('[id$="Dropdown"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    });
}

// Utility: Format Currency
function formatCurrency(value) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(value);
}

// Utility: Format Date
function formatDate(date, format = 'MM/DD/YYYY') {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    return format
        .replace('DD', day)
        .replace('MM', month)
        .replace('YYYY', year);
}

// Utility: Debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search/Filter
function initializeSearch() {
    const searchInputs = document.querySelectorAll('[data-search]');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function() {
            const targetSelector = this.getAttribute('data-search');
            const searchTerm = this.value.toLowerCase();
            const targets = document.querySelectorAll(targetSelector);

            targets.forEach(target => {
                const text = target.textContent.toLowerCase();
                target.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }, 300));
    });
}

// Show Notification
function showNotification(message, type = 'success', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `fixed top-6 right-6 px-6 py-4 rounded-xl font-bold text-sm ${
        type === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
        type === 'error' ? 'bg-red-50 text-red-700 border border-red-200' :
        type === 'warning' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
        'bg-blue-50 text-blue-700 border border-blue-200'
    } shadow-lg z-50 animate-slideInRight`;

    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(400px)';
        notification.style.transition = 'all 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, duration);
}

// Confirm Dialog
function showConfirm(message, onConfirm, onCancel) {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-navy/60 backdrop-blur-sm flex items-center justify-center z-50';

    const dialog = document.createElement('div');
    dialog.className = 'bg-white p-8 rounded-2xl shadow-2xl max-w-sm animate-fadeIn';
    dialog.innerHTML = `
        <p class="text-lg font-bold text-navy mb-6">${message}</p>
        <div class="flex gap-4">
            <button class="flex-1 px-4 py-2.5 bg-slate-100 text-navy rounded-lg font-bold hover:bg-slate-200 transition" data-action="cancel">Cancel</button>
            <button class="flex-1 px-4 py-2.5 bg-navy text-white rounded-lg font-bold hover:bg-royal transition" data-action="confirm">Confirm</button>
        </div>
    `;

    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    dialog.querySelector('[data-action="confirm"]').addEventListener('click', () => {
        onConfirm();
        overlay.remove();
    });

    dialog.querySelector('[data-action="cancel"]').addEventListener('click', () => {
        if (onCancel) onCancel();
        overlay.remove();
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeSearch);
