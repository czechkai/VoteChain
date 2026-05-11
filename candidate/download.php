<?php
/**
 * Candidate Document Download Handler
 * Allows candidates to view their own submitted documents
 */

require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    exit('Unauthorized');
}

$filePath = $_GET['file'] ?? '';
if (!$filePath) {
    http_response_code(400);
    exit('File not specified');
}

// Normalize and validate the file path to prevent directory traversal
$filePath = str_replace(['\\', '..', '//'], ['/', '', '/'], $filePath);
$filePath = ltrim($filePath, '/');

// Only allow files from candidate_documents directory
if (strpos($filePath, 'uploads/candidate_documents/') !== 0) {
    http_response_code(403);
    exit('Invalid file path');
}

$fullPath = __DIR__ . '/../' . $filePath;

// Verify the file exists and is within the allowed directory
if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    exit('File not found');
}

$realPath = realpath($fullPath);
$basePath = realpath(__DIR__ . '/../uploads/candidate_documents');

if (!$realPath || strpos($realPath, $basePath) !== 0) {
    http_response_code(403);
    exit('Access Denied');
}

// Verify the candidate owns this document
$profileId = $_SESSION['profile_id'] ?? null;
if (!$profileId) {
    http_response_code(401);
    exit('Unauthorized');
}

$database = $pdo;
if (!$database instanceof PDO) {
    http_response_code(500);
    exit('Database connection failed');
}

// Extract candidate ID from path (uploads/candidate_documents/{candidateId}/file)
$pathParts = explode('/', $filePath);
$candidateId = $pathParts[2] ?? null;

if (!$candidateId) {
    http_response_code(403);
    exit('Access Denied');
}

// Verify the candidate belongs to this user
try {
    $checkTable = function($table, $column) use ($database, $profileId, $candidateId) {
        $stmt = $database->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = CURRENT_SCHEMA() AND table_name = ? LIMIT 1");
        $stmt->execute([$table]);
        if ($stmt->fetchColumn() === false) return false;

        $verifyStmt = $database->prepare("SELECT 1 FROM {$table} WHERE id = ? AND {$column} = ? LIMIT 1");
        $verifyStmt->execute([$candidateId, $profileId]);
        return $verifyStmt->fetchColumn() !== false;
    };

    $hasAccess = $checkTable('candidates', 'profile_id') || $checkTable('candidates', 'user_id');
    if (!$hasAccess) {
        http_response_code(403);
        exit('Access Denied');
    }
} catch (Exception $e) {
    error_log('Candidate document access check error: ' . $e->getMessage());
    http_response_code(500);
    exit('Server Error');
}

// Serve the file
try {
    $fileName = basename($fullPath);
    $fileSize = filesize($fullPath);
    $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

    // Handle inline viewing for images and PDFs, attachment for others
    $viewableTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
    $disposition = in_array($mimeType, $viewableTypes, true) ? 'inline' : 'attachment';

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: ' . $disposition . '; filename="' . basename($fileName) . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: public, max-age=3600');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

    readfile($fullPath);
    exit;
} catch (Exception $e) {
    error_log('Candidate document download error: ' . $e->getMessage());
    http_response_code(500);
    exit('Failed to download file');
}
