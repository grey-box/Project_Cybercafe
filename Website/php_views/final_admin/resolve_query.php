<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['admin']);
// Set content type to JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the query ID from POST data
$queryId = isset($_POST['query_id']) ? intval($_POST['query_id']) : 0;

// Validate query ID
if ($queryId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid query ID']);
    exit;
}

// Simulate database update (in a real application,
// For now, we'll just return success

// Log the resolution (in a real application, log this in real databse
$logEntry = [
    'query_id' => $queryId,
    'resolved_at' => date('Y-m-d H:i:s'),
    'resolved_by' => 'admin', // In a real app, get from session
    'status' => 'resolved'
];

// In a real application, you would:
// 1. Update the query status in the database
// 2. Log the resolution
// 3. Send notification to the user
// 4. Update any related systems

// For demo purposes, we'll just return success
echo json_encode([
    'status' => 'success',
    'message' => 'Query resolved successfully',
    'query_id' => $queryId,
    'resolved_at' => date('Y-m-d H:i:s')
]);
?>
