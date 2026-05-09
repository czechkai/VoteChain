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
    <title>Register | VoteChain DOrSU</title>
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
        .auth-gradient { background: radial-gradient(circle at bottom left, #1E3A8A, #0A1F44); }
        .animate-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        select::-webkit-scrollbar { width: 6px; }
        select::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 md:p-8">

    <div class="max-w-6xl w-full grid md:grid-cols-12 bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 overflow-hidden border border-slate-100">
        
        <!-- Left Side: Registration Form -->
        <div class="md:col-span-7 p-8 md:p-16 flex flex-col justify-center">
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6 md:hidden">
                    <div class="w-8 h-8 bg-navy rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-link text-gold text-sm"></i>
                    </div>
                    <span class="text-xl font-extrabold text-navy tracking-tight">VOTE<span class="text-royal">CHAIN</span></span>
                </div>
                <h1 class="text-3xl font-extrabold text-navy mb-2">Create Account</h1>
                <p class="text-slate-500 font-medium">Join the blockchain-secured voting community.</p>
            </div>

            <!-- Progress Tracker -->
            <div class="flex items-center gap-4 mb-10">
                <div id="p-bar-1" class="h-1.5 w-12 bg-navy rounded-full transition-all duration-500"></div>
                <div id="p-bar-2" class="h-1.5 w-12 bg-slate-100 rounded-full transition-all duration-500"></div>
                <div id="p-bar-3" class="h-1.5 w-12 bg-slate-100 rounded-full transition-all duration-500"></div>
                <span id="step-text" class="text-[10px] font-bold uppercase tracking-widest text-navy ml-2">Step 1: Personal</span>
            </div>

            <form id="regForm" action="../student/dashboard.php" method="POST" class="space-y-6">
                
                <!-- Step 1: Personal -->
                <div id="step-1" class="space-y-5 animate-in">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">First Name</label>
                            <input type="text" name="fname" required placeholder="John" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">Last Name</label>
                            <input type="text" name="lname" required placeholder="Doe" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">Institutional Email</label>
                        <div class="relative group">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-royal transition-colors"></i>
                            <input type="email" name="email" required placeholder="j.doe@dorsu.edu.ph" class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Academic -->
                <div id="step-2" class="hidden space-y-5 animate-in">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-xs font-extrabold text-navy uppercase tracking-widest">Student ID Number</label>
                            <span id="yearLevelBadge" class="hidden text-[10px] font-bold bg-gold/20 text-navy px-2 py-0.5 rounded-full border border-gold/30"></span>
                        </div>
                        <div class="relative group">
                            <i class="fa-solid fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-royal transition-colors"></i>
                            <input type="text" id="sidInput" name="sid" required onkeyup="calculateYearLevel(this.value)" placeholder="2026-0001" class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium">
                            <input type="hidden" name="year_level" id="yearLevelInput">
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">Faculty</label>
                            <div class="relative">
                                <select id="facultySelect" name="faculty" required onchange="updatePrograms()" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium appearance-none">
                                    <option value="" selected disabled>Select your Faculty</option>
                                    <option value="FACET">FACET - Computing, Engineering & Tech</option>
                                    <option value="FCJE">FCJE - Criminal Justice Education</option>
                                    <option value="FNAHS">FNAHS - Nursing & Allied Health</option>
                                    <option value="FALS">FALS - Agriculture & Life Sciences</option>
                                    <option value="FAHSC">FAHSC - Human Sciences & Communication</option>
                                    <option value="FBM">FBM - Business & Management</option>
                                    <option value="FTED">FTED - Teachers Education</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">Academic Program / Organization</label>
                            <div class="relative">
                                <select id="programSelect" name="program" required disabled class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium appearance-none disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="" selected disabled>Select Faculty first</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Account -->
                <div id="step-3" class="hidden space-y-5 animate-in">
                    <div class="space-y-2">
                        <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">Password</label>
                        <div class="relative">
                            <input type="password" id="regPass" name="password" onkeyup="validatePasswords()" required placeholder="••••••••" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium">
                            <button type="button" onclick="togglePass('regPass', 'eye-1')" class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-navy transition-colors">
                                <i id="eye-1" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <!-- Conditional Password Prompts -->
                        <div id="passRequirements" class="mt-2 space-y-1">
                            <p id="reqLength" class="text-[10px] font-bold text-red-500 hidden"><i class="fa-solid fa-circle-xmark mr-1"></i> At least 8 characters</p>
                            <p id="reqComplexity" class="text-[10px] font-bold text-red-500 hidden"><i class="fa-solid fa-circle-xmark mr-1"></i> Add a number or special character</p>
                        </div>
                        <div class="flex gap-1 mt-2">
                            <div id="bar-1" class="h-1 w-full bg-slate-100 rounded-full transition-all"></div>
                            <div id="bar-2" class="h-1 w-full bg-slate-100 rounded-full transition-all"></div>
                            <div id="bar-3" class="h-1 w-full bg-slate-100 rounded-full transition-all"></div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-extrabold text-navy uppercase tracking-widest ml-1">Confirm Password</label>
                        <div class="relative">
                            <input type="password" id="confirmPass" onkeyup="validatePasswords()" required placeholder="••••••••" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-royal focus:bg-white outline-none transition-all font-medium">
                            <button type="button" onclick="togglePass('confirmPass', 'eye-2')" class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-navy transition-colors">
                                <i id="eye-2" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <p id="matchError" class="text-[10px] font-bold text-red-500 hidden ml-1"><i class="fa-solid fa-circle-xmark mr-1"></i> Passwords do not match</p>
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer group pt-2">
                        <div class="relative flex items-center mt-1">
                            <input type="checkbox" required class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 checked:bg-royal transition-all">
                            <i class="fa-solid fa-check absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                        </div>
                        <span class="text-xs text-slate-500 font-medium leading-relaxed group-hover:text-navy transition-colors">
                            I agree to the <a href="#" class="text-royal font-bold">Terms & Conditions</a> and DOrSU Data Privacy Policy.
                        </span>
                    </label>
                </div>

                <!-- Footer Actions -->
                <div class="mt-10 flex items-center justify-between">
                    <button type="button" id="prevBtn" onclick="nextPrev(-1)" class="invisible text-sm font-bold text-slate-400 hover:text-navy transition-all flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" id="nextBtn" onclick="nextPrev(1)" class="px-10 py-4 bg-navy text-white rounded-2xl font-bold shadow-xl shadow-navy/20 hover:bg-royal transition-all transform active:scale-[0.98]">
                        Continue
                    </button>
                </div>
            </form>

            <div class="mt-10 pt-8 border-t border-slate-100 text-center">
                <p class="text-slate-500 font-medium text-sm">
                    Already registered? 
                    <a href="login.php" class="text-royal font-extrabold hover:underline ml-1">Login here</a>
                </p>
            </div>
        </div>

        <!-- Right Side: Info Panel -->
        <div class="hidden md:flex md:col-span-5 auth-gradient p-12 flex-col justify-between text-white relative">
            <div class="relative z-10">
                <div class="flex items-center justify-end gap-3 mb-16">
                    <span class="text-2xl font-extrabold tracking-tight text-right">VOTE<span class="text-gold">CHAIN</span></span>
                    <div class="w-10 h-10 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20">
                        <i class="fa-solid fa-user-plus text-gold text-lg"></i>
                    </div>
                </div>

                <div class="space-y-8">
                    <div id="side-info" class="transition-all duration-500">
                        <h3 class="text-gold font-bold text-xs uppercase tracking-[0.2em] mb-4">Identity Verification</h3>
                        <h2 id="side-title" class="text-3xl font-extrabold leading-tight">One Person, <br>One Secure Vote.</h2>
                        <p id="side-desc" class="mt-4 text-blue-100/60 text-sm leading-relaxed">We use your Student ID to verify eligibility against the official university registrar records.</p>
                    </div>
                    
                    <div class="space-y-4 pt-8">
                        <div class="flex items-center gap-4 group">
                            <div id="check-1" class="w-6 h-6 border-2 border-white/20 rounded-lg flex items-center justify-center transition-all bg-white/5">
                                <i class="fa-solid fa-check text-[10px] text-transparent"></i>
                            </div>
                            <span class="text-sm font-bold text-white/40 transition-all" id="check-text-1">Personal Details</span>
                        </div>
                        <div class="flex items-center gap-4 group">
                            <div id="check-2" class="w-6 h-6 border-2 border-white/20 rounded-lg flex items-center justify-center transition-all bg-white/5">
                                <i class="fa-solid fa-check text-[10px] text-transparent"></i>
                            </div>
                            <span class="text-sm font-bold text-white/40 transition-all" id="check-text-2">Academic Standing</span>
                        </div>
                        <div class="flex items-center gap-4 group">
                            <div id="check-3" class="w-6 h-6 border-2 border-white/20 rounded-lg flex items-center justify-center transition-all bg-white/5">
                                <i class="fa-solid fa-check text-[10px] text-transparent"></i>
                            </div>
                            <span class="text-sm font-bold text-white/40 transition-all" id="check-text-3">Security Config</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative z-10 flex items-center gap-4 bg-navy/40 backdrop-blur-md p-5 rounded-[2rem] border border-white/10">
                <div class="w-10 h-10 bg-gold rounded-full flex items-center justify-center shadow-lg shadow-gold/20">
                    <i class="fa-solid fa-shield-halved text-navy text-sm"></i>
                </div>
                <div class="text-[10px]">
                    <p class="font-extrabold text-white uppercase tracking-widest">End-to-End Encryption</p>
                    <p class="text-white/40 mt-0.5">Your data is hashed before being stored.</p>
                </div>
            </div>

            <div class="absolute -top-20 -right-20 w-80 h-80 bg-royal rounded-full opacity-30 blur-3xl"></div>
        </div>
    </div>

    <script>
        const programsData = {
            "FACET": [
                { val: "BSIT", text: "BS Information Technology (Codebyters)" },
                { val: "BSMath", text: "BS Mathematics (Society of Math Majors)" },
                { val: "BSCE", text: "BS Civil Engineering (JPICE)" },
                { val: "BITM", text: "BS Industrial Technology Mgmt (ASSITI)" }
            ],
            "FCJE": [{ val: "BSCrim", text: "BS Criminology (FCJE Student Org)" }],
            "FNAHS": [{ val: "BSN", text: "BS Nursing (PULSNOR)" }],
            "FALS": [
                { val: "BSA", text: "BS Agriculture (AFA)" },
                { val: "BSAM", text: "BS Agribusiness Mgmt (JABES)" },
                { val: "BSBio", text: "BS Biology (SNS)" },
                { val: "BSES", text: "BS Environmental Science (Green Pulse)" }
            ],
            "FAHSC": [
                { val: "BSDevCom", text: "BS Development Communication (SAMAHAN)" },
                { val: "BSPsych", text: "BS Psychology (KAMASIKO)" },
                { val: "BAPolSci", text: "BA Political Science (Dunong Society)" }
            ],
            "FBM": [
                { val: "BSBM", text: "BS Business Management (FBMSO)" },
                { val: "BSHM", text: "BS Hospitality Management (FBMSO)" }
            ],
            "FTED": [
                { val: "BEED", text: "Bachelor of Elementary Education (MS)" },
                { val: "BSED-Eng", text: "BSED English (GEMS)" },
                { val: "BSED-Sci", text: "BSED Science (OISA)" },
                { val: "BSED-Fil", text: "BSED Filipino (KamaFil)" },
                { val: "BSED-Math", text: "BSED Mathematics (Mathrix)" },
                { val: "BSPE", text: "BS Physical Education (KO)" },
                { val: "BECE", text: "Bachelor of Early Childhood Ed (MS)" },
                { val: "BSTLE", text: "BS Technology & Livelihood Ed (MS)" },
                { val: "BSNE", text: "Bachelor of Special Needs Ed (MS)" }
            ]
        };

        function updatePrograms() {
            const faculty = document.getElementById('facultySelect').value;
            const programSelect = document.getElementById('programSelect');
            programSelect.innerHTML = '<option value="" selected disabled>Select your Program</option>';
            programSelect.disabled = !faculty;
            if (faculty && programsData[faculty]) {
                programsData[faculty].forEach(prog => {
                    const opt = document.createElement('option');
                    opt.value = prog.val;
                    opt.textContent = prog.text;
                    programSelect.appendChild(opt);
                });
            }
        }

        function calculateYearLevel(sid) {
            const currentYear = 2026;
            const badge = document.getElementById('yearLevelBadge');
            const hiddenInput = document.getElementById('yearLevelInput');
            const entryYearMatch = sid.match(/^(\d{4})/);
            
            if (entryYearMatch) {
                const entryYear = parseInt(entryYearMatch[1]);
                let yearLevel = (currentYear - entryYear) + 1;
                if (yearLevel < 1) yearLevel = 1;
                if (yearLevel > 5) yearLevel = "5+";

                let suffix = "th Year";
                if (yearLevel == 1) suffix = "st Year";
                if (yearLevel == 2) suffix = "nd Year";
                if (yearLevel == 3) suffix = "rd Year";
                if (yearLevel == "5+") suffix = " Year (Irreg/Advanced)";

                badge.innerText = `${yearLevel}${suffix}`;
                badge.classList.remove('hidden');
                hiddenInput.value = yearLevel;
            } else {
                badge.classList.add('hidden');
                hiddenInput.value = "";
            }
        }

        let currentStep = 1;
        
        function nextPrev(n) {
            if (n === 1 && currentStep === 3) {
                if(!validatePasswords()) return;
                document.getElementById("regForm").submit();
                return;
            }
            document.getElementById(`step-${currentStep}`).classList.add('hidden');
            if(n === 1) {
                const check = document.getElementById(`check-${currentStep}`);
                const text = document.getElementById(`check-text-${currentStep}`);
                check.classList.replace('border-white/20', 'bg-gold');
                check.classList.replace('bg-white/5', 'bg-gold');
                check.querySelector('i').classList.replace('text-transparent', 'text-navy');
                text.classList.replace('text-white/40', 'text-white');
            }
            currentStep += n;
            document.getElementById(`step-${currentStep}`).classList.remove('hidden');
            updateUI();
        }

        function updateUI() {
            for (let i = 1; i <= 3; i++) {
                const bar = document.getElementById(`p-bar-${i}`);
                if (i <= currentStep) bar.classList.replace('bg-slate-100', 'bg-navy');
                else bar.classList.replace('bg-navy', 'bg-slate-100');
            }
            const labels = ["Personal", "Academic", "Account"];
            document.getElementById('step-text').innerText = `Step ${currentStep}: ${labels[currentStep-1]}`;
            document.getElementById('prevBtn').style.visibility = currentStep === 1 ? 'hidden' : 'visible';
            document.getElementById('nextBtn').innerText = currentStep === 3 ? 'Complete Setup' : 'Continue';
            const title = document.getElementById('side-title');
            const desc = document.getElementById('side-desc');
            if(currentStep === 1) {
                title.innerHTML = "One Person, <br>One Secure Vote.";
                desc.innerText = "We use your Student ID to verify eligibility against the official university registrar records.";
            } else if(currentStep === 2) {
                title.innerHTML = "DOrSU <br>Representation.";
                desc.innerText = "Select your specific Faculty and Program to ensure you see the correct local and university-wide candidates.";
            } else {
                title.innerHTML = "Immutable <br>Security.";
                desc.innerText = "Your account is your gateway to the blockchain. We use high-standard hashing to keep your password private.";
            }
        }

        function togglePass(id, iconId) {
            const input = document.getElementById(id);
            const icon = document.getElementById(iconId);
            if(input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function validatePasswords() {
            const pass = document.getElementById('regPass').value;
            const confirm = document.getElementById('confirmPass').value;
            const reqLength = document.getElementById('reqLength');
            const reqComplexity = document.getElementById('reqComplexity');
            const matchError = document.getElementById('matchError');
            const nextBtn = document.getElementById('nextBtn');
            
            let isLengthValid = pass.length >= 8;
            let isComplexValid = /[0-9!@#$%^&*]/.test(pass);
            let isMatchValid = confirm === "" || pass === confirm;
            let isConfirmFilled = confirm !== "";

            // Progressive Prompts Logic
            // Step A: Check Length
            if (pass.length > 0 && !isLengthValid) {
                reqLength.classList.remove('hidden');
            } else {
                reqLength.classList.add('hidden');
            }

            // Step B: Check Complexity (Only show if length is satisfied)
            if (isLengthValid && !isComplexValid) {
                reqComplexity.classList.remove('hidden');
            } else {
                reqComplexity.classList.add('hidden');
            }

            // Step C: Check Match
            if (isConfirmFilled && !isMatchValid) {
                matchError.classList.remove('hidden');
            } else {
                matchError.classList.add('hidden');
            }

            // Strength Bars
            const bars = [document.getElementById('bar-1'), document.getElementById('bar-2'), document.getElementById('bar-3')];
            bars.forEach(b => b.className = 'h-1 w-full bg-slate-100 rounded-full transition-all');
            if (pass.length > 0) bars[0].classList.add(isLengthValid ? 'bg-green-400' : 'bg-red-400');
            if (isLengthValid) bars[1].classList.add(isComplexValid ? 'bg-green-400' : 'bg-yellow-400');
            if (isLengthValid && isComplexValid && isMatchValid && isConfirmFilled) bars[2].classList.add('bg-green-400');

            // Disable Next Button if current step requirements aren't met
            const isValid = isLengthValid && isComplexValid && isMatchValid && isConfirmFilled;
            if (currentStep === 3) {
                nextBtn.style.opacity = isValid ? "1" : "0.5";
                nextBtn.style.pointerEvents = isValid ? "auto" : "none";
            } else {
                nextBtn.style.opacity = "1";
                nextBtn.style.pointerEvents = "auto";
            }

            return isValid;
        }

        updateUI();
    </script>
</body>
</html>