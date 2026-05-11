-- VoteChain Database Schema for Supabase PostgreSQL
-- Run this in your Supabase SQL Editor

-- ============================================
-- 1. FACULTIES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS faculties (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 2. PROGRAMS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS programs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    faculty_id UUID NOT NULL REFERENCES faculties(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 3. USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(20) UNIQUE,
    faculty_id UUID REFERENCES faculties(id),
    program_id UUID REFERENCES programs(id),
    year_level INT,
    role VARCHAR(20) DEFAULT 'student', -- 'student', 'candidate', 'admin'
    password_hash VARCHAR(255),
    is_verified BOOLEAN DEFAULT false,
    is_eligible_to_vote BOOLEAN DEFAULT true,
    blockchain_wallet_address VARCHAR(255),
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 4. ELECTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS elections (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'scheduled', -- 'scheduled', 'active', 'closed', 'completed'
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    is_open BOOLEAN DEFAULT false,
    total_eligible_voters INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 5. POSITIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS positions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    election_id UUID NOT NULL REFERENCES elections(id) ON DELETE CASCADE,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    slot_count INT DEFAULT 1, -- Number of candidates that can win
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 6. CANDIDATES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS candidates (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    position_id UUID NOT NULL REFERENCES positions(id) ON DELETE CASCADE,
    election_id UUID NOT NULL REFERENCES elections(id) ON DELETE CASCADE,
    campaign_description TEXT,
    platform_image_url VARCHAR(500),
    image_url VARCHAR(500), -- Candidate profile photo URL
    filing_status VARCHAR(50) DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
    vote_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 7. CANDIDACY_FILINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS candidacy_filings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    candidate_id UUID NOT NULL REFERENCES candidates(id) ON DELETE CASCADE,
    document_type VARCHAR(100), -- 'certificate_of_candidacy', 'certificate_of_registration', etc.
    document_url VARCHAR(500),
    uploaded_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 8. VOTES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS votes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    election_id UUID NOT NULL REFERENCES elections(id) ON DELETE CASCADE,
    position_id UUID NOT NULL REFERENCES positions(id) ON DELETE CASCADE,
    candidate_id UUID NOT NULL REFERENCES candidates(id) ON DELETE CASCADE,
    voter_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    blockchain_transaction_hash VARCHAR(255), -- Hash on blockchain for immutability
    vote_status VARCHAR(50) DEFAULT 'valid', -- 'valid', 'spoiled', 'invalid'
    created_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 9. VOTE_VERIFICATION TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS vote_verification (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    voter_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    election_id UUID NOT NULL REFERENCES elections(id) ON DELETE CASCADE,
    has_voted BOOLEAN DEFAULT false,
    verification_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 10. ELECTION_RESULTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS election_results (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    election_id UUID NOT NULL REFERENCES elections(id) ON DELETE CASCADE,
    position_id UUID NOT NULL REFERENCES positions(id) ON DELETE CASCADE,
    candidate_id UUID NOT NULL REFERENCES candidates(id) ON DELETE CASCADE,
    final_vote_count INT DEFAULT 0,
    rank INT,
    is_winner BOOLEAN DEFAULT false,
    calculated_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- 11. ANNOUNCEMENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS announcements (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    election_id UUID REFERENCES elections(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    announcement_type VARCHAR(50), -- 'news', 'alert', 'reminder'
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT now()
);

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_student_id ON users(student_id);
CREATE INDEX idx_candidates_election_id ON candidates(election_id);
CREATE INDEX idx_candidates_user_id ON candidates(user_id);
CREATE INDEX idx_votes_election_id ON votes(election_id);
CREATE INDEX idx_votes_voter_id ON votes(voter_id);
CREATE INDEX idx_votes_candidate_id ON votes(candidate_id);
CREATE INDEX idx_elections_status ON elections(status);

-- ============================================
-- INSERT SAMPLE FACULTIES
-- ============================================
INSERT INTO faculties (code, name) VALUES
('FACET', 'Computing, Engineering & Technology'),
('FCJE', 'Criminal Justice Education'),
('FNAHS', 'Nursing & Allied Health Sciences'),
('FALS', 'Agriculture & Life Sciences'),
('FAHSC', 'Human Sciences & Communication'),
('FBM', 'Business & Management'),
('FTED', 'Teachers Education')
ON CONFLICT DO NOTHING;

-- ============================================
-- SECURITY: Enable Row Level Security (RLS)
-- ============================================
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE votes ENABLE ROW LEVEL SECURITY;
ALTER TABLE vote_verification ENABLE ROW LEVEL SECURITY;

-- Allow users to see their own data
CREATE POLICY "Users can see their own data" ON users
    FOR SELECT USING (auth.uid()::uuid = id);

-- Allow users to see votes only if they're admin or the voter
CREATE POLICY "Vote visibility policy" ON votes
    FOR SELECT USING (
        voter_id = auth.uid()::uuid OR
        EXISTS (SELECT 1 FROM users WHERE id = auth.uid()::uuid AND role = 'admin')
    );
