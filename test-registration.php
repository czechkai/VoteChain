<?php
/**
 * Quick Test - Verify Registration Setup
 */
include 'includes/config.php';

echo "<h2>VoteChain Registration Setup Test</h2>";

// Test 1: Database Connection
echo "<h3>1. Database Connection:</h3>";
if ($pdo) {
    echo "<p style='color: green'>✓ Connected to Supabase PostgreSQL</p>";
} else {
    echo "<p style='color: red'>✗ Connection Failed: $db_error</p>";
    exit;
}

// Test 2: Check if profiles table exists
echo "<h3>2. Profiles Table:</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM profiles");
    $count = $stmt->fetchColumn();
    echo "<p style='color: green'>✓ Profiles table exists. Current profiles: $count</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 3: Check if helper functions exist
echo "<h3>3. Helper Functions:</h3>";
$functions = ['createProfile', 'getProfileByEmail', 'authenticateProfile'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "<p style='color: green'>✓ Function '$func' exists</p>";
    } else {
        echo "<p style='color: red'>✗ Function '$func' missing</p>";
    }
}

// Test 4: Sample registration test (optional)
echo "<h3>4. Test Registration (Optional):</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_data = [
        'first_name' => 'Test',
        'last_name' => 'Student',
        'email' => 'test.' . time() . '@dorsu.edu.ph',
        'student_id' => '2026-' . rand(1000, 9999),
        'year_level' => 2,
        'faculty_code' => 'FACET',
        'program_code' => 'BSIT',
        'password' => 'TestPassword123!',
        'role' => 'student'
    ];
    
    $result = createProfile($pdo, $test_data);
    
    if ($result['success']) {
        echo "<p style='color: green'>✓ Test registration successful!</p>";
        echo "<p>Email: " . $test_data['email'] . "</p>";
        echo "<p>Student ID: " . $test_data['student_id'] . "</p>";
    } else {
        echo "<p style='color: orange'>✗ Test registration failed: " . $result['message'] . "</p>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>VoteChain - Registration Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; }
        h2 { color: #0A1F44; }
        h3 { color: #1E3A8A; margin-top: 30px; }
        p { margin: 10px 0; }
        form { margin-top: 30px; }
        button { padding: 10px 20px; background: #1E3A8A; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0A1F44; }
    </style>
</head>
<body>
    <form method="POST">
        <button type="submit" name="test" value="1">Run Test Registration</button>
    </form>
    
    <hr>
    <p><strong>Next Step:</strong> Go to <a href="auth/register.php">/auth/register.php</a> to register a new student.</p>
</body>
</html>