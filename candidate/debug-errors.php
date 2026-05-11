<?php
// Debug page - shows last filing errors
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

$logFile = __DIR__ . '/../logs/errors.log';

if (!file_exists($logFile)) {
    die('No error log found');
}

$lines = file($logFile);
$recentLines = array_slice($lines, -30);

echo '<pre style="font-size: 12px; background: #f5f5f5; padding: 20px; max-height: 600px; overflow-y: auto;">';
echo '<strong>Last 30 error log lines (most recent first):</strong><br><br>';
echo implode('', array_reverse($recentLines));
echo '</pre>';
?>
