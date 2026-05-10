<?php
/**
 * VoteChain - Global Configuration & Helper Functions
 * Database: Supabase (PostgreSQL) with profiles table
 */

// 1. Load Environment Variables
// Load .env file from project root
$dotenv_path = __DIR__ . '/../.env';
if (file_exists($dotenv_path)) {
    $env_lines = file($dotenv_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
        }
    }
}

// 2. Force Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errors.log');

// 3. Database Configuration from .env
define('DB_HOST', $_ENV['DB_HOST'] ?? 'db.limigeafrtzxvjafglqy.supabase.co');
define('DB_PORT', $_ENV['DB_PORT'] ?? '5432');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'postgres');
define('DB_USER', $_ENV['DB_USER'] ?? 'postgres');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// 4. Site Settings
define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'VoteChain');
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/votechain/');

// 5. Supabase API Configuration
define('SUPABASE_URL', $_ENV['SUPABASE_URL'] ?? '');
define('SUPABASE_ANON_KEY', $_ENV['SUPABASE_ANON_KEY'] ?? '');

// 6. Initialize Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 7. Database Connection using PDO
$pdo = null;
$db_error = null;

if (extension_loaded('pdo_pgsql')) {
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $db_error = $e->getMessage();
    }
} else {
    $db_error = "PHP Extension 'pdo_pgsql' is not enabled.";
}


// =============================================
// HELPER FUNCTIONS
// =============================================

/**
 * Sanitize user input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['profile_id']);
}

/**
 * Require login and specific role(s)
 */
function requireRole($roles) {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit;
    }

    $allowed = is_array($roles) ? $roles : [$roles];
    $currentRole = $_SESSION['role'] ?? null;

    if (!$currentRole || !in_array($currentRole, $allowed, true)) {
        header('Location: ../auth/login.php');
        exit;
    }
}

/**
 * Check whether profile has at least one approved candidate record.
 */
function hasApprovedCandidateAccess($pdo, $profileId) {
    if (!$pdo || !$profileId) {
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM candidates WHERE profile_id = ? AND LOWER(COALESCE(status, '')) = 'approved' LIMIT 1"
        );
        $stmt->execute([$profileId]);
        return $stmt->fetch() !== null;
    } catch (Exception $e) {
        error_log('Error checking approved candidate access: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check whether profile has any candidate application record.
 */
function hasCandidateApplication($pdo, $profileId) {
    if (!$pdo || !$profileId) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT 1 FROM candidates WHERE profile_id = ? LIMIT 1");
        $stmt->execute([$profileId]);
        return $stmt->fetch() !== null;
    } catch (Exception $e) {
        error_log('Error checking candidate application: ' . $e->getMessage());
        return false;
    }
}

/**
 * Allow candidate filing for approved candidates and students who started application mode.
 */
function requireCandidateFilingAccess($pdo) {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit;
    }

    $currentRole = $_SESSION['role'] ?? 'student';
    if ($currentRole === 'candidate' || $currentRole === 'admin') {
        return;
    }

    if (!empty($_SESSION['candidate_application_mode'])) {
        return;
    }

    // Recover access after re-login if application already exists.
    $profileId = $_SESSION['profile_id'] ?? null;
    if ($profileId && hasCandidateApplication($pdo, $profileId)) {
        $_SESSION['candidate_application_mode'] = 1;
        return;
    }

    header('Location: ../student/dashboard.php');
    exit;
}

/**
 * Get profile by email
 */
function getProfileByEmail($pdo, $email) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM profiles WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching profile: " . $e->getMessage());
        return null;
    }
}

/**
 * Get profile by student ID
 */
function getProfileByStudentId($pdo, $student_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM profiles WHERE student_id = ? LIMIT 1");
        $stmt->execute([$student_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching profile: " . $e->getMessage());
        return null;
    }
}

/**
 * Create a new profile (user registration)
 */
function createProfile($pdo, $data) {
    try {
        // Check if email already exists
        if (getProfileByEmail($pdo, $data['email'])) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Check if student ID already exists
        if (getProfileByStudentId($pdo, $data['student_id'])) {
            return ['success' => false, 'message' => 'Student ID already registered'];
        }
        
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("
            INSERT INTO profiles 
            (first_name, last_name, email, student_id, year_level, faculty_code, program_code, role, password_hash, is_verified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, FALSE)
        ");
        
        $success = $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['student_id'],
            $data['year_level'],
            $data['faculty_code'],
            $data['program_code'],
            $data['role'] ?? 'student',
            $password_hash
        ]);
        
        if ($success) {
            return ['success' => true, 'message' => 'Profile created successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to create profile'];
    } catch (Exception $e) {
        error_log("Error creating profile: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Authenticate profile (login)
 */
function authenticateProfile($pdo, $email, $password) {
    try {
        $profile = getProfileByEmail($pdo, $email);
        
        if (!$profile) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        if (!password_verify($password, $profile['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Set session
        $_SESSION['profile_id'] = $profile['id'];
        $_SESSION['email'] = $profile['email'];
        $_SESSION['first_name'] = $profile['first_name'];
        $_SESSION['last_name'] = $profile['last_name'];
        $_SESSION['role'] = $profile['role'];
        $_SESSION['student_id'] = $profile['student_id'];
        
        return ['success' => true, 'message' => 'Login successful', 'profile' => $profile];
    } catch (Exception $e) {
        error_log("Error authenticating profile: " . $e->getMessage());
        return ['success' => false, 'message' => 'Authentication error'];
    }
}

/**
 * Get all active elections
 */
function getActiveElections($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM elections 
            WHERE status = 'active' 
            ORDER BY starts_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching elections: " . $e->getMessage());
        return [];
    }
}

/**
 * Get candidates for an election and position
 */
function getCandidates($pdo, $election_id, $position_id = null) {
    try {
        if ($position_id) {
            $stmt = $pdo->prepare("
                SELECT c.*, p.first_name, p.last_name, pos.name as position_title
                FROM candidates c
                JOIN profiles p ON c.profile_id = p.id
                JOIN positions pos ON c.position_id = pos.id
                WHERE c.election_id = ? AND c.position_id = ? AND c.status = 'approved'
                ORDER BY p.first_name ASC
            ");
            $stmt->execute([$election_id, $position_id]);
        } else {
            $stmt = $pdo->prepare("
                SELECT c.*, p.first_name, p.last_name, pos.name as position_title
                FROM candidates c
                JOIN profiles p ON c.profile_id = p.id
                JOIN positions pos ON c.position_id = pos.id
                WHERE c.election_id = ? AND c.status = 'approved'
                ORDER BY pos.order_index, p.first_name ASC
            ");
            $stmt->execute([$election_id]);
        }
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching candidates: " . $e->getMessage());
        return [];
    }
}

/**
 * Record a vote
 */
function recordVote($pdo, $voter_profile_id, $election_id, $position_id, $candidate_id, $tx_hash = null) {
    try {
        $prevHash = null;
        if ($tx_hash === null) {
            $prevStmt = $pdo->prepare(
                "SELECT tx_hash FROM votes WHERE election_id = ? ORDER BY created_at DESC LIMIT 1"
            );
            $prevStmt->execute([$election_id]);
            $prevHash = $prevStmt->fetchColumn() ?: 'GENESIS';

            $payload = implode('|', [
                $election_id,
                $voter_profile_id,
                $position_id,
                $candidate_id,
                $prevHash
            ]);
            $tx_hash = hash('sha256', $payload);
        }

        $stmt = $pdo->prepare("
            INSERT INTO votes (election_id, voter_profile_id, position_id, candidate_id, tx_hash, prev_hash)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $election_id,
            $voter_profile_id,
            $position_id,
            $candidate_id,
            $tx_hash,
            $prevHash
        ]);
        
        return ['success' => $success];
    } catch (PDOException $e) {
        error_log("Error recording vote: " . $e->getMessage());
        return ['success' => false, 'code' => $e->getCode(), 'message' => $e->getMessage()];
    } catch (Exception $e) {
        error_log("Error recording vote: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Verify vote chain integrity for an election
 */
function verifyVoteChain($pdo, $election_id) {
    try {
        $stmt = $pdo->prepare(
            "SELECT id, voter_profile_id, position_id, candidate_id, tx_hash, prev_hash
             FROM votes
             WHERE election_id = ?
             ORDER BY created_at ASC"
        );
        $stmt->execute([$election_id]);
        $votes = $stmt->fetchAll();

        $expectedPrev = 'GENESIS';
        foreach ($votes as $vote) {
            $payload = implode('|', [
                $election_id,
                $vote['voter_profile_id'],
                $vote['position_id'],
                $vote['candidate_id'],
                $expectedPrev
            ]);
            $expectedHash = hash('sha256', $payload);

            if ($vote['prev_hash'] !== $expectedPrev || $vote['tx_hash'] !== $expectedHash) {
                return ['valid' => false, 'broken_at' => $vote['id']];
            }

            $expectedPrev = $vote['tx_hash'];
        }

        return ['valid' => true];
    } catch (Exception $e) {
        error_log("Error verifying vote chain: " . $e->getMessage());
        return ['valid' => false, 'message' => 'Verification error'];
    }
}

/**
 * Check if user has already voted in an election for a position
 */
function hasUserVoted($pdo, $voter_profile_id, $election_id, $position_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 1 FROM votes 
            WHERE voter_profile_id = ? AND election_id = ? AND position_id = ?
            LIMIT 1
        ");
        $stmt->execute([$voter_profile_id, $election_id, $position_id]);
        return $stmt->fetch() !== null;
    } catch (Exception $e) {
        error_log("Error checking vote status: " . $e->getMessage());
        return false;
    }
}

/**
 * Get election results
 */
function getElectionResults($pdo, $election_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.*, 
                p.first_name, 
                p.last_name, 
                pos.name as position_title,
                COUNT(v.id) as vote_count
            FROM candidates c
            JOIN profiles p ON c.profile_id = p.id
            JOIN positions pos ON c.position_id = pos.id
            LEFT JOIN votes v ON c.id = v.candidate_id
            WHERE c.election_id = ?
            GROUP BY c.id, p.id, pos.id
            ORDER BY pos.order_index, vote_count DESC
        ");
        $stmt->execute([$election_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching results: " . $e->getMessage());
        return [];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    if (session_id() != '') {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

?>