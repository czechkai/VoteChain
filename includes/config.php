<?php
/**
 * VoteChain - Global Configuration
 * Database: Supabase (PostgreSQL)
 */

// 1. Force Error Reporting to see exactly what is wrong
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Database Configuration
// Note: Replace these placeholders with your actual Supabase Project Settings
define('DB_HOST', 'db.xxxxxxxxxxxxxxxxxxxx.supabase.co');
define('DB_PORT', '5432');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres');
define('DB_PASS', 'your_supabase_password_here');

// 3. Site Settings
define('SITE_NAME', 'VoteChain');
define('BASE_URL', 'http://localhost/votechain/'); // Adjust based on your local folder name

// 4. Initialize Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. Database Connection using PDO
// We wrap this in a check to see if the pgsql driver exists to prevent fatal crashes
$pdo = null;
if (extension_loaded('pdo_pgsql')) {
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        // Log error but don't 'die' yet, so the HTML UI can still render for design testing
        error_log("Database Error: " . $e->getMessage());
        $db_error = $e->getMessage();
    }
} else {
    $db_error = "PHP Extension 'pdo_pgsql' is not enabled in your web server (XAMPP/WAMP).";
}

/**
 * Helper function to check if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Helper function to sanitize user input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Optional: Display DB error only if needed for debugging
// if (isset($db_error)) { echo "<!-- DB Setup Note: $db_error -->"; }
?>