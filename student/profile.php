<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | VoteChain DOrSU</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .sidebar-gradient { background: linear-gradient(180deg, #0A1F44 0%, #1E3A8A 100%); }
        .nav-item-active { background: rgba(255, 255, 255, 0.1); border-left: 4px solid #FFC107; color: white !important; }
        .profile-card { background: white; border-radius: 2.5rem; border: 1px solid #e2e8f0; }
        .input-field { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; transition: all 0.2s; }
        .input-field:focus { border-color: #1E3A8A; background: white; outline: none; box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.05); }
    </style>
</head>
<body class="flex min-h-screen">
    <?php $role = 'student'; $activePage = 'profile'; include '../includes/sidebar.php'; ?>

    <main class="flex-1 lg:ml-72 p-4 md:p-8">
        <header class="mb-10">
            <h1 class="text-3xl font-extrabold text-navy">Account Profile</h1>
            <p class="text-slate-500 font-medium mt-1">Manage your student credentials and security settings.</p>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            
            <!-- Left: Basic Info -->
            <div class="xl:col-span-4 space-y-8">
                <div class="profile-card p-8 text-center">
                    <div class="relative inline-block mb-6 group">
                        <div class="w-32 h-32 rounded-[2.5rem] bg-slate-100 border-4 border-white shadow-xl overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=300&h=300&fit=crop" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                        <button class="absolute -bottom-2 -right-2 w-10 h-10 bg-navy text-white rounded-2xl border-4 border-white flex items-center justify-center hover:bg-royal transition-all">
                            <i class="fa-solid fa-camera text-sm"></i>
                        </button>
                    </div>
                    <h2 class="text-2xl font-black text-navy">James Blanco</h2>
                    <p class="text-royal font-bold text-sm">BS Information Technology</p>
                    <div class="mt-8 pt-8 border-t border-slate-50 space-y-4">
                        <div class="flex justify-between items-center px-4 py-3 bg-slate-50 rounded-2xl">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">School ID</span>
                            <span class="font-bold text-navy">2023-IT-0042</span>
                        </div>
                        <div class="flex justify-between items-center px-4 py-3 bg-slate-50 rounded-2xl">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Faculty</span>
                            <span class="font-bold text-navy">FACET</span>
                        </div>
                    </div>
                </div>

                <div class="bg-navy rounded-[2rem] p-8 text-white">
                    <h4 class="font-bold mb-2">Voter Status</h4>
                    <div class="flex items-center gap-3 text-gold mb-6">
                        <i class="fa-solid fa-circle-check"></i>
                        <span class="text-sm font-black uppercase tracking-widest">Verified Voter</span>
                    </div>
                    <p class="text-white/60 text-xs leading-relaxed">Your account is linked to the blockchain network. All casted votes are verifiable through your unique voter hash.</p>
                </div>
            </div>

            <!-- Right: Editable Settings -->
            <div class="xl:col-span-8 space-y-8">
                <!-- Profile Settings -->
                <div class="profile-card p-8 md:p-10">
                    <h3 class="text-xl font-black text-navy mb-8 flex items-center gap-3">
                        <i class="fa-solid fa-user-pen text-royal"></i>
                        Edit Profile
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Full Name</label>
                            <input type="text" value="James B. Blanco" class="w-full px-5 py-4 input-field font-bold text-navy">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Email Address</label>
                            <input type="email" value="james.blanco@dorsu.edu.ph" class="w-full px-5 py-4 input-field font-bold text-navy">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Faculty</label>
                            <select class="w-full px-5 py-4 input-field font-bold text-navy appearance-none">
                                <option>Computing, Engineering & Technology</option>
                                <option>Education</option>
                                <option>Business & Management</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Program</label>
                            <input type="text" value="BS Information Technology" class="w-full px-5 py-4 input-field font-bold text-navy">
                        </div>
                    </div>
                    <div class="mt-10 flex justify-end">
                        <button class="px-8 py-4 bg-navy text-white rounded-2xl font-bold hover:bg-royal transition-all shadow-xl shadow-navy/20">
                            Save Changes
                        </button>
                    </div>
                </div>

                <!-- Password Settings -->
                <div id="settings" class="profile-card p-8 md:p-10">
                    <h3 class="text-xl font-black text-navy mb-8 flex items-center gap-3">
                        <i class="fa-solid fa-lock text-royal"></i>
                        Change Password
                    </h3>
                    <div class="space-y-6 max-w-lg">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Current Password</label>
                            <input type="password" placeholder="••••••••••••" class="w-full px-5 py-4 input-field font-bold text-navy">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">New Password</label>
                            <input type="password" placeholder="Min. 8 characters" class="w-full px-5 py-4 input-field font-bold text-navy">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Confirm New Password</label>
                            <input type="password" class="w-full px-5 py-4 input-field font-bold text-navy">
                        </div>
                    </div>
                    <div class="mt-10 flex justify-end">
                        <button class="px-8 py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold hover:bg-slate-200 transition-all">
                            Update Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>