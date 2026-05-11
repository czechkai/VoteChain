<?php
// Diagnostic script to check database structure
require_once '../includes/config.php';

if (!$pdo) {
    die('Database connection failed');
}

echo '<h2>🔍 Database Diagnostic Report</h2>';

// 1. Check candidates table columns
echo '<h3>1. Candidates Table Columns:</h3>';
try {
    $stmt = $pdo->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'candidates' ORDER BY ordinal_position");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
    echo '<tr style="background: #ddd;"><th>Column Name</th><th>Data Type</th><th>Has image_url?</th></tr>';
    $hasImageUrl = false;
    foreach ($columns as $col) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($col['column_name']) . '</td>';
        echo '<td>' . htmlspecialchars($col['data_type']) . '</td>';
        echo '<td>';
        if ($col['column_name'] === 'image_url') {
            echo '✅ YES - Column exists!';
            $hasImageUrl = true;
        } else {
            echo '';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '<p><strong>Result:</strong> ';
    if ($hasImageUrl) {
        echo '✅ image_url column EXISTS in database';
    } else {
        echo '❌ image_url column MISSING - You need to run the ALTER TABLE command in Supabase';
    }
    echo '</p>';
} catch (Exception $e) {
    echo '<p style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// 2. Check if any candidates have image_url values
echo '<h3>2. Candidates with Photos:</h3>';
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM candidates");
    $totalCandidates = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM candidates WHERE image_url IS NOT NULL AND image_url != ''");
    $candidatesWithPhotos = $stmt->fetchColumn();
    
    echo '<p><strong>Total candidates:</strong> ' . $totalCandidates . '</p>';
    echo '<p><strong>Candidates with image_url stored:</strong> ' . $candidatesWithPhotos . '</p>';
    
    if ($candidatesWithPhotos > 0) {
        echo '<h4>✅ Candidates WITH photos:</h4>';
        $stmt = $pdo->query("SELECT id, image_url, created_at FROM candidates WHERE image_url IS NOT NULL AND image_url != '' ORDER BY created_at DESC LIMIT 10");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
        echo '<tr style="background: #ddd;"><th>ID</th><th>Photo URL</th><th>Submitted</th></tr>';
        foreach ($samples as $candidate) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($candidate['id']) . '</td>';
            echo '<td><code style="font-size: 11px;">' . htmlspecialchars($candidate['image_url']) . '</code></td>';
            echo '<td>' . htmlspecialchars($candidate['created_at']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    $candidatesWithoutPhotos = $totalCandidates - $candidatesWithPhotos;
    if ($candidatesWithoutPhotos > 0) {
        echo '<h4>❌ Candidates WITHOUT photos (submitted before column was added):</h4>';
        $stmt = $pdo->query("SELECT id, created_at FROM candidates WHERE image_url IS NULL OR image_url = '' ORDER BY created_at DESC LIMIT 10");
        $noPhotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
        echo '<tr style="background: #ddd;"><th>ID</th><th>Submitted</th></tr>';
        foreach ($noPhotos as $candidate) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($candidate['id']) . '</td>';
            echo '<td>' . htmlspecialchars($candidate['created_at']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<p style="color: orange; font-weight: bold;">⚠️ These candidates were submitted before the image_url column was added. They need to re-submit or upload photos manually.</p>';
    }
} catch (Exception $e) {
    echo '<p style="color: orange;">Could not check image URLs - column may not exist yet: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// 3. Check if uploaded files exist
echo '<h3>3. Uploaded Photo Files:</h3>';
$photoDir = __DIR__ . '/../uploads/candidate_images';
if (is_dir($photoDir)) {
    $files = scandir($photoDir);
    $photoFiles = array_filter($files, function($f) { return $f !== '.' && $f !== '..'; });
    echo '<p>Files in /uploads/candidate_images/: ' . count($photoFiles) . '</p>';
    if (count($photoFiles) > 0) {
        echo '<ul>';
        foreach (array_slice($photoFiles, 0, 10) as $file) {
            echo '<li>' . htmlspecialchars($file) . '</li>';
        }
        if (count($photoFiles) > 10) {
            echo '<li>... and ' . (count($photoFiles) - 10) . ' more</li>';
        }
        echo '</ul>';
    }
} else {
    echo '<p style="color: orange;">Directory /uploads/candidate_images/ does not exist yet</p>';
}

echo '<h3>📋 Summary:</h3>';
echo '<ul>';
echo '<li>If image_url column is missing: You need to run the ALTER TABLE in Supabase</li>';
echo '<li>If column exists but candidates have no photos: Previous filings didn\'t upload photos</li>';
echo '<li>If photos are stored but not displaying: Check the render logic in admin/candidate.php</li>';
echo '</ul>';
?>
