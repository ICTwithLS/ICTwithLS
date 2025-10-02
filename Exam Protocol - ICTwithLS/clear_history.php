<?php
header('Content-Type: application/json');

$attempts_file = __DIR__ . '/exam_attempts.json';

// Make sure the file exists
if (!file_exists($attempts_file)) {
    http_response_code(404);
    echo json_encode(['error' => 'exam_attempts.json not found']);
    exit;
}

// Try to clear the file
if (is_writable($attempts_file)) {
    $result = file_put_contents($attempts_file, json_encode([], JSON_PRETTY_PRINT));
    if ($result !== false) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'All attempts cleared']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to clear exam attempts']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'exam_attempts.json is not writable']);
}