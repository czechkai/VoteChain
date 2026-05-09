<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballot | USC General Elections</title>
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
        .selection-ring { border: 3px solid transparent; transition: all 0.2s; }
        .candidate-card.selected { border-color: #1E3A8A; background-color: #f0f4ff; }
        .candidate-card.selected .check-icon { display: flex; }
        .focused-container { max-width: 900px; margin: 0 auto; }
    </style>
</head>
<body class="pb-32">

    <!-- Stepper Header -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="vote.php" class="text-slate-400 hover:text-navy transition-colors">
                    <i class="fa-solid fa-chevron-left text-lg"></i>
                </a>
                <h1 class="font-black text-navy text-xl uppercase tracking-tight">USC <span class="text-royal">Ballot</span></h1>
            </div>
            <div class="hidden md:flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-bold">1</span>
                    <span class="text-sm font-bold text-navy">Select</span>
                </div>
                <div class="w-12 h-px bg-slate-200"></div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xs font-bold">2</span>
                    <span class="text-sm font-bold text-slate-400">Review</span>
                </div>
                <div class="w-12 h-px bg-slate-200"></div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xs font-bold">3</span>
                    <span class="text-sm font-bold text-slate-400">Submit</span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Time Remaining</p>
                <p id="timer" class="text-royal font-black text-lg">44:02</p>
            </div>
        </div>
    </nav>

    <div class="focused-container px-4 py-12">
        
        <!-- Guidelines -->
        <section class="bg-amber-50 border border-amber-100 p-6 rounded-3xl mb-12 flex gap-4">
            <i class="fa-solid fa-circle-info text-amber-500 text-xl mt-1"></i>
            <div>
                <h4 class="font-bold text-amber-900 mb-1">Voting Guidelines</h4>
                <ul class="text-sm text-amber-800/80 space-y-1 list-disc ml-4">
                    <li>Select exactly one (1) candidate for the Presidential position.</li>
                    <li>Verify your selection before clicking the review button.</li>
                    <li>Your vote is final once submitted to the blockchain.</li>
                </ul>
            </div>
        </section>

        <!-- Presidential Section -->
        <div class="mb-16">
            <div class="flex items-end justify-between mb-8 border-b-2 border-slate-100 pb-4">
                <div>
                    <h2 class="text-2xl font-black text-navy">President</h2>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">University Student Government</p>
                </div>
                <span class="text-xs font-bold text-royal bg-royal/10 px-3 py-1 rounded-full italic">Required</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" id="presidential-candidates">
                <!-- Candidate 1 -->
                <div class="candidate-card p-6 rounded-[2.5rem] bg-white border-2 border-slate-100 cursor-pointer hover:border-royal/30 transition-all relative group" onclick="selectCandidate(this, 'pres')">
                    <div class="check-icon absolute top-6 right-6 w-8 h-8 bg-royal text-white rounded-full hidden items-center justify-center shadow-lg">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="flex items-center gap-5">
                        <div class="w-20 h-20 rounded-3xl bg-slate-100 overflow-hidden border-2 border-white shadow-sm">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop" alt="Candidate" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-navy group-hover:text-royal transition-colors">Marco Agapito</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Progressive Alliance</p>
                            <button class="mt-2 text-[10px] font-black text-royal uppercase hover:underline">View Platform</button>
                        </div>
                    </div>
                </div>

                <!-- Candidate 2 -->
                <div class="candidate-card p-6 rounded-[2.5rem] bg-white border-2 border-slate-100 cursor-pointer hover:border-royal/30 transition-all relative group" onclick="selectCandidate(this, 'pres')">
                    <div class="check-icon absolute top-6 right-6 w-8 h-8 bg-royal text-white rounded-full hidden items-center justify-center shadow-lg">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="flex items-center gap-5">
                        <div class="w-20 h-20 rounded-3xl bg-slate-100 overflow-hidden border-2 border-white shadow-sm">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&h=200&fit=crop" alt="Candidate" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-navy group-hover:text-royal transition-colors">Sarah Jenkins</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">United Students Party</p>
                            <button class="mt-2 text-[10px] font-black text-royal uppercase hover:underline">View Platform</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Abstain Option -->
        <div class="flex justify-center mb-20">
            <button class="flex items-center gap-3 px-8 py-3 rounded-full border-2 border-slate-200 text-slate-400 font-bold hover:bg-slate-50 transition-all">
                <i class="fa-solid fa-ban"></i>
                Abstain from this position
            </button>
        </div>

    </div>

    <!-- Bottom Action Bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 p-6 z-40">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-center sm:text-left">
                <p class="text-xs font-bold text-slate-400 uppercase">Selections</p>
                <p class="text-navy font-extrabold">1 of 12 Positions Completed</p>
            </div>
            <div class="flex gap-4 w-full sm:w-auto">
                <button class="flex-1 sm:flex-none px-10 py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold hover:bg-slate-200 transition-all">
                    Save Draft
                </button>
                <button onclick="showReview()" class="flex-1 sm:flex-none px-12 py-4 bg-navy text-white rounded-2xl font-bold hover:bg-royal transition-all shadow-xl shadow-navy/20">
                    Review Ballot
                </button>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" class="fixed inset-0 bg-navy/60 backdrop-blur-sm z-[60] hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-[3rem] p-8 md:p-12 shadow-2xl animate-in zoom-in-95 duration-200">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-6">
                    <i class="fa-solid fa-magnifying-glass-chart"></i>
                </div>
                <h3 class="text-2xl font-black text-navy">Final Review</h3>
                <p class="text-slate-500 font-medium">Please confirm your selections before submission.</p>
            </div>

            <div class="bg-slate-50 rounded-3xl p-6 mb-8 space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-slate-200">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">Position</span>
                    <span class="text-slate-400 font-bold uppercase text-[10px]">Your Choice</span>
                </div>
                <div class="flex justify-between items-center font-bold">
                    <span class="text-navy">President</span>
                    <span class="text-royal">Marco Agapito</span>
                </div>
                <!-- Mock other positions -->
                <div class="flex justify-between items-center font-bold text-slate-300">
                    <span>Vice President</span>
                    <span>Abstained</span>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <button onclick="submitVote()" class="w-full py-5 bg-navy text-white rounded-2xl font-black text-lg hover:bg-royal transition-all shadow-xl shadow-navy/20">
                    Submit Secure Vote
                </button>
                <button onclick="hideReview()" class="w-full py-4 text-slate-400 font-bold hover:text-navy transition-all">
                    Back to Selection
                </button>
            </div>
        </div>
    </div>

    <script>
        function selectCandidate(card, group) {
            // Deselect others in group
            const groupCards = card.parentElement.querySelectorAll('.candidate-card');
            groupCards.forEach(c => c.classList.remove('selected', 'border-royal'));
            groupCards.forEach(c => c.querySelector('.check-icon').style.display = 'none');
            
            // Select this one
            card.classList.add('selected', 'border-royal');
            card.querySelector('.check-icon').style.display = 'flex';
        }

        function showReview() {
            document.getElementById('reviewModal').style.display = 'flex';
        }

        function hideReview() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        function submitVote() {
            // Simplified for prototype
            const btn = event.currentTarget;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Encrypting & Recording...';
            btn.disabled = true;
            
            setTimeout(() => {
                alert('Success! Your vote has been recorded on the blockchain. Transaction ID: 0x72a...f91');
                window.location.href = 'results.php';
            }, 2000);
        }
    </script>
</body>
</html>