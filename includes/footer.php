<?php
$currentYear = date('Y');
?>

<footer class="bg-navy text-white py-12 mt-20 border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <!-- Footer Grid -->
        <div class="grid md:grid-cols-4 gap-12 mb-12">
            <!-- DOrSU Info -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-gold/20 rounded-lg flex items-center justify-center border border-gold/40">
                        <i class="fa-solid fa-graduation-cap text-gold"></i>
                    </div>
                    <h3 class="text-lg font-black">DOrSU</h3>
                </div>
                <p class="text-white/70 text-sm leading-relaxed">
                    Davao Oriental State University - Dedicated to excellence in student governance and democratic participation.
                </p>
            </div>

            <!-- COMELEC Info -->
            <div>
                <h4 class="text-sm font-black uppercase tracking-[0.1em] text-gold mb-6">COMELEC</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-white/70 hover:text-gold text-sm transition-colors">Commission on Elections</a></li>
                    <li><a href="#" class="text-white/70 hover:text-gold text-sm transition-colors">Election Rules</a></li>
                    <li><a href="#" class="text-white/70 hover:text-gold text-sm transition-colors">Code of Conduct</a></li>
                    <li><a href="#" class="text-white/70 hover:text-gold text-sm transition-colors">FAQs</a></li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-sm font-black uppercase tracking-[0.1em] text-gold mb-6">Quick Links</h4>
                <ul class="space-y-3">
                    <li><a href="/vc/student/dashboard.php" class="text-white/70 hover:text-gold text-sm transition-colors">Student Portal</a></li>
                    <li><a href="/vc/candidate/dashboard.php" class="text-white/70 hover:text-gold text-sm transition-colors">Candidate Hub</a></li>
                    <li><a href="/vc/admin/dashboard.php" class="text-white/70 hover:text-gold text-sm transition-colors">Admin Panel</a></li>
                    <li><a href="#" class="text-white/70 hover:text-gold text-sm transition-colors">Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact & Social -->
            <div>
                <h4 class="text-sm font-black uppercase tracking-[0.1em] text-gold mb-6">Contact</h4>
                <div class="space-y-3 mb-6">
                    <p class="text-white/70 text-sm">
                        <i class="fa-solid fa-phone text-gold w-4"></i>
                        <a href="tel:+639876543210" class="ml-2 hover:text-gold transition-colors">(+63) 987-654-3210</a>
                    </p>
                    <p class="text-white/70 text-sm">
                        <i class="fa-solid fa-envelope text-gold w-4"></i>
                        <a href="mailto:comelec@dorsu.edu.ph" class="ml-2 hover:text-gold transition-colors">comelec@dorsu.edu.ph</a>
                    </p>
                    <p class="text-white/70 text-sm">
                        <i class="fa-solid fa-map-marker-alt text-gold w-4"></i>
                        <span class="ml-2">DOrSU Main Campus</span>
                    </p>
                </div>

                <!-- Social Icons -->
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 bg-white/10 hover:bg-gold rounded-lg flex items-center justify-center text-white hover:text-navy transition-all">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-white/10 hover:bg-gold rounded-lg flex items-center justify-center text-white hover:text-navy transition-all">
                        <i class="fa-brands fa-twitter"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-white/10 hover:bg-gold rounded-lg flex items-center justify-center text-white hover:text-navy transition-all">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-white/10 hover:bg-gold rounded-lg flex items-center justify-center text-white hover:text-navy transition-all">
                        <i class="fa-brands fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-white/10 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-white/60 text-sm">
                    &copy; <?php echo $currentYear; ?> Davao Oriental State University COMELEC. All rights reserved.
                </p>
                <div class="flex gap-6">
                    <a href="#" class="text-white/60 hover:text-gold text-sm transition-colors">Privacy Policy</a>
                    <a href="#" class="text-white/60 hover:text-gold text-sm transition-colors">Terms of Service</a>
                    <a href="#" class="text-white/60 hover:text-gold text-sm transition-colors">Accessibility</a>
                </div>
            </div>
        </div>
    </div>
</footer>
