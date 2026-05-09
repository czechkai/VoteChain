<?php 
if (file_exists('../includes/config.php')) {
    include '../includes/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | VoteChain DOrSU</title>
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
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .auth-gradient { background: radial-gradient(circle at top right, #1E3A8A, #0A1F44); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 md:p-8">

    <div class="max-w-5xl w-full grid md:grid-cols-2 bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 overflow-hidden border border-slate-100">
        
        <!-- Left Side: Decorative/Branding -->
        <div class="hidden md:flex auth-gradient p-12 flex-col justify-between text-white relative">
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-16">
                    <div class="w-10 h-10 bg-gold rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-link text-navy text-xl"></i>
                    </div>
                    <span class="text-2xl font-extrabold tracking-tight">VOTE<span class="text-gold">CHAIN</span></span>
                </div>
                <h2 class="text-4xl font-extrabold leading-tight mb-6">Securing the <br><span class="text-gold">Student Voice</span> of DOrSU.</h2>
                <p class="text-blue-100/70 leading-relaxed max-w-sm">
                    Access the university's official blockchain-integrated voting platform. Your vote is immutable, transparent, and secure.
                </p>
            </div>
            
            <div class="relative z-10 flex items-center gap-4 bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10">
                <div class="w-12 h-12 bg-gold/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-shield-check text-gold"></i>
                </div>
                <div class="text-xs">
                    <p class="font-bold">Verified Institution</p>
                    <p class="text-white/60">Davao Oriental State University</p>
                </div>
            </div>

            <!-- Background Decoration -->
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-royal rounded-full opacity-20 blur-3xl"></div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="p-8 md:p-16 flex flex-col justify-center">
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold text-navy mb-2">Welcome Back</h1>
                <p class="text-slate-500 font-medium">Please enter your credentials to cast your vote.</p>
            </div>

            <form action="../student/dashboard.php" method="POST" class="space-y-6">
                <!-- Email/ID -->
                <div class="space-y-2">
                    <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">Email or School ID</label>
                    <div class="relative group">
                        <i class="fa-solid fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-royal transition-colors"></i>
                        <input type="text" name="identifier" required placeholder="e.g. 2026-1234" 
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-xs font-extrabold text-navy uppercase tracking-widest">Password</label>
                        <a href="#" class="text-xs font-bold text-royal hover:text-navy transition">Forgot Password?</a>
                    </div>
                    <div class="relative group">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-royal transition-colors"></i>
                        <input type="password" id="loginPassword" name="password" required placeholder="••••••••" 
                            class="w-full pl-12 pr-12 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium">
                        <button type="button" onclick="togglePassword('loginPassword', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-navy px-2">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <label class="flex items-center gap-3 cursor-pointer group w-fit">
                    <div class="relative flex items-center">
                        <input type="checkbox" class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 checked:bg-royal transition-all">
                        <i class="fa-solid fa-check absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                    </div>
                    <span class="text-sm text-slate-500 group-hover:text-navy font-medium transition-colors">Keep me logged in</span>
                </label>

                <!-- Submit -->
                <button type="submit" class="w-full bg-navy text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-navy/20 hover:bg-royal transition-all transform active:scale-[0.98] flex items-center justify-center gap-2">
                    Sign In <i class="fa-solid fa-arrow-right-to-bracket text-sm"></i>
                </button>
            </form>

            <div class="mt-10 pt-8 border-t border-slate-100">
                <p class="text-center text-slate-500 font-medium">
                    Don't have an account? 
                    <a href="register.php" class="text-royal font-extrabold hover:underline ml-1">Create Account</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>