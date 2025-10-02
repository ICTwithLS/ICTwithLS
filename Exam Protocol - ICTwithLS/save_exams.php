<?php
header('Content-Type: application/json');
$attempts_file = 'exam_attempts.json';
$exams_file = 'exams.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($exams_file)) {
        $exams = json_decode(file_get_contents($exams_file), true);
        echo json_encode($exams);
    } else {
        echo json_encode([]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['action'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing action']);
        exit;
    }

    if ($data['action'] === 'check_attempt') {
        $exams = file_exists($exams_file) ? json_decode(file_get_contents($exams_file), true) : [];
        $attempts = file_exists($attempts_file) ? json_decode(file_get_contents($attempts_file), true) : [];
        $valid_access_codes = array_column($exams, 'accessCode');
        $filtered_attempts = array_filter($attempts, function ($attempt) use ($valid_access_codes) {
            return in_array($attempt['accessCode'], $valid_access_codes);
        });

        if (count($filtered_attempts) !== count($attempts)) {
            if (is_writable($attempts_file)) {
                file_put_contents($attempts_file, json_encode(array_values($filtered_attempts), JSON_PRETTY_PRINT));
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Cannot write to attempts file']);
                exit;
            }
        }

        $hasAttempted = false;
        $attemptCount = 0;
        $remainingTime = null;
        $timesUp = false;
        $latestTimestamp = 0;
        foreach ($filtered_attempts as $attempt) {
            if (
                ($attempt['studentName'] === $data['studentName'] || $attempt['studentId'] === $data['studentId']) &&
                $attempt['accessCode'] === $data['accessCode']
            ) {
                $hasAttempted = true;
                $attemptCount += 1;
                if (strtotime($attempt['timestamp']) > $latestTimestamp) {
                    $latestTimestamp = strtotime($attempt['timestamp']);
                    $remainingTime = $attempt['remainingTime'];
                    $timesUp = isset($attempt['timesUp']) ? $attempt['timesUp'] : false;
                }
            }
        }
        http_response_code(200);
        echo json_encode(['hasAttempted' => $hasAttempted, 'attemptCount' => $attemptCount, 'remainingTime' => $remainingTime, 'timesUp' => $timesUp]);
    } elseif ($data['action'] === 'log_attempt') {
        $attempts = file_exists($attempts_file) ? json_decode(file_get_contents($attempts_file), true) : [];
        $attempts[] = [
            'studentName' => $data['studentName'],
            'studentId' => $data['studentId'],
            'accessCode' => $data['accessCode'],
            'timestamp' => $data['timestamp'],
            'remainingTime' => $data['remainingTime']
        ];
        if (is_writable($attempts_file)) {
            file_put_contents($attempts_file, json_encode($attempts, JSON_PRETTY_PRINT));
            http_response_code(200);
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Cannot write to attempts file']);
        }
    } elseif ($data['action'] === 'log_activity') {
        file_put_contents('activity.log', json_encode($data) . "\n", FILE_APPEND);
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } elseif ($data['action'] === 'save_exams') {
        if (!isset($data['exams']) || !is_array($data['exams'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid exams data']);
            exit;
        }
        if (is_writable($exams_file) ) {
            $result = file_put_contents($exams_file, json_encode($data['exams'], JSON_PRETTY_PRINT));
            if ($result === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to write to exams file']);
                exit;
            }
            // Clean up attempts for deleted exams
            $attempts = file_exists($attempts_file) ? json_decode(file_get_contents($attempts_file), true) : [];
            $valid_access_codes = array_column($data['exams'], 'accessCode');
            $filtered_attempts = array_filter($attempts, function ($attempt) use ($valid_access_codes) {
                return in_array($attempt['accessCode'], $valid_access_codes);
            });
            if (count($filtered_attempts) !== count($attempts)) {
                if (is_writable($attempts_file)) {
                    file_put_contents($attempts_file, json_encode(array_values($filtered_attempts), JSON_PRETTY_PRINT));
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Cannot write to attempts file']);
                    exit;
                }
            }
            http_response_code(200);
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Exams file is not writable']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
}
?>