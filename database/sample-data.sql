-- VoteChain Sample Data Initialization
-- Run this in Supabase SQL Editor to add test data

-- ============================================
-- SAMPLE FACULTIES (Already added in schema.sql)
-- ============================================
-- Faculties are already inserted in schema.sql

-- ============================================
-- SAMPLE PROGRAMS
-- ============================================
INSERT INTO programs (faculty_id, code, name) 
SELECT id, 'BS-CS', 'Bachelor of Science in Computer Science' FROM faculties WHERE code = 'FACET'
ON CONFLICT DO NOTHING;

INSERT INTO programs (faculty_id, code, name) 
SELECT id, 'BS-IT', 'Bachelor of Science in Information Technology' FROM faculties WHERE code = 'FACET'
ON CONFLICT DO NOTHING;

INSERT INTO programs (faculty_id, code, name) 
SELECT id, 'BS-CE', 'Bachelor of Science in Civil Engineering' FROM faculties WHERE code = 'FACET'
ON CONFLICT DO NOTHING;

-- ============================================
-- SAMPLE USERS (STUDENTS)
-- ============================================
INSERT INTO users (email, first_name, last_name, student_id, faculty_id, program_id, year_level, password_hash, role, is_verified, is_eligible_to_vote)
SELECT 
    'juan.delacruz@dorsu.edu.ph',
    'Juan',
    'Dela Cruz',
    '2024-0001',
    f.id,
    p.id,
    2,
    '$2y$10$abcd1234efgh5678ijkl9012mnop3456qrstu7890vwxyz', -- Use real hashed passwords
    'student',
    true,
    true
FROM faculties f
JOIN programs p ON f.id = p.faculty_id
WHERE f.code = 'FACET' AND p.code = 'BS-CS'
ON CONFLICT (email) DO NOTHING;

INSERT INTO users (email, first_name, last_name, student_id, faculty_id, program_id, year_level, password_hash, role, is_verified, is_eligible_to_vote)
SELECT 
    'maria.santos@dorsu.edu.ph',
    'Maria',
    'Santos',
    '2024-0002',
    f.id,
    p.id,
    3,
    '$2y$10$abcd1234efgh5678ijkl9012mnop3456qrstu7890vwxyz',
    'student',
    true,
    true
FROM faculties f
JOIN programs p ON f.id = p.faculty_id
WHERE f.code = 'FACET' AND p.code = 'BS-IT'
ON CONFLICT (email) DO NOTHING;

-- ============================================
-- SAMPLE ADMIN USER
-- ============================================
INSERT INTO users (email, first_name, last_name, password_hash, role, is_verified)
VALUES (
    'admin@dorsu.edu.ph',
    'Admin',
    'User',
    '$2y$10$abcd1234efgh5678ijkl9012mnop3456qrstu7890vwxyz',
    'admin',
    true
)
ON CONFLICT (email) DO NOTHING;

-- ============================================
-- SAMPLE ELECTION
-- ============================================
INSERT INTO elections (title, description, status, start_date, end_date, is_open, total_eligible_voters)
VALUES (
    'USC General Elections 2026',
    'University-wide supreme student council election for all departments.',
    'scheduled',
    NOW() + INTERVAL '1 day',
    NOW() + INTERVAL '3 days',
    false,
    0
)
ON CONFLICT DO NOTHING;

-- ============================================
-- SAMPLE POSITIONS
-- ============================================
INSERT INTO positions (election_id, title, description, slot_count, display_order)
SELECT 
    e.id,
    'President',
    'President of the University Student Government',
    1,
    1
FROM elections e
WHERE e.title = 'USC General Elections 2026'
ON CONFLICT DO NOTHING;

INSERT INTO positions (election_id, title, description, slot_count, display_order)
SELECT 
    e.id,
    'Vice President',
    'Vice President of the University Student Government',
    1,
    2
FROM elections e
WHERE e.title = 'USC General Elections 2026'
ON CONFLICT DO NOTHING;

INSERT INTO positions (election_id, title, description, slot_count, display_order)
SELECT 
    e.id,
    'Secretary General',
    'Secretary General of the University Student Government',
    1,
    3
FROM elections e
WHERE e.title = 'USC General Elections 2026'
ON CONFLICT DO NOTHING;

-- ============================================
-- SAMPLE CANDIDATES
-- ============================================
INSERT INTO candidates (user_id, position_id, election_id, campaign_description, filing_status)
SELECT 
    u.id,
    p.id,
    e.id,
    'I am committed to improving student services and fostering unity across all faculties.',
    'approved'
FROM users u
JOIN elections e ON e.title = 'USC General Elections 2026'
JOIN positions p ON e.id = p.election_id
WHERE u.email = 'juan.delacruz@dorsu.edu.ph' AND p.title = 'President'
ON CONFLICT DO NOTHING;

INSERT INTO candidates (user_id, position_id, election_id, campaign_description, filing_status)
SELECT 
    u.id,
    p.id,
    e.id,
    'Let us work together for a better university experience.',
    'approved'
FROM users u
JOIN elections e ON e.title = 'USC General Elections 2026'
JOIN positions p ON e.id = p.election_id
WHERE u.email = 'maria.santos@dorsu.edu.ph' AND p.title = 'Vice President'
ON CONFLICT DO NOTHING;

-- ============================================
-- VERIFY DATA INSERTION
-- ============================================
-- Run these queries to verify data:
-- SELECT COUNT(*) as total_users FROM users;
-- SELECT COUNT(*) as total_elections FROM elections;
-- SELECT COUNT(*) as total_candidates FROM candidates;
-- SELECT * FROM elections;
-- SELECT u.first_name, u.last_name, p.title FROM candidates c
--   JOIN users u ON c.user_id = u.id
--   JOIN positions p ON c.position_id = p.id;
