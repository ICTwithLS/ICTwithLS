<?php
header('Content-Type: application/json');

$attempts_file = __DIR__ . '/exam_attempts.json';

// Check if the file exists
if (!file_exists($attempts_file)) {
    http_response_code(404);
    echo json_encode(['error' => 'exam_attempts.json not found']);
    exit;
}

// Get the POST data (array of records to delete)
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['records']) || !is_array($data['records'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing records data']);
    exit;
}

// Load current attempts
$attempts = json_decode(file_get_contents($attempts_file), true);
if ($attempts === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to read exam attempts']);
    exit;
}

// Filter out the selected records
$records_to_delete = $data['records'];
$filtered_attempts = array_filter($attempts, function ($attempt) use ($records_to_delete) {
    foreach ($records_to_delete as $record) {
        if (
            $attempt['studentId'] === $record['studentId'] &&
            $attempt['accessCode'] === $record['accessCode'] &&
            $attempt['timestamp'] === $record['timestamp']
        ) {
            return false; // Exclude this record
        }
    }
    return true; // Keep this record
});

// Write the updated attempts back to the file
if (is_writable($attempts_file)) {
    $result = file_put_contents($attempts_file, json_encode(array_values($filtered_attempts), JSON_PRETTY_PRINT));
    if ($result !== false) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Selected attempts cleared']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update exam attempts']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'exam_attempts.json is not writable']);
}
?>