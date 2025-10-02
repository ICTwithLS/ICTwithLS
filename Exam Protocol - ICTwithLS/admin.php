<?php
// ===========================
// ACTIVITY LOG MODULE - Backend Functions
// ===========================

$al_sys_log_file_path_2k24 = __DIR__ . "/activity.log";

/**
 * Clear all logs from the file
 */
function al_sys_clear_all_logs_function_2k24($logFile)
{
    file_put_contents($logFile, "");
    return "All logs have been cleared successfully.";
}

/**
 * Auto-delete logs older than specified days
 */
function al_sys_auto_delete_old_logs_function_2k24($logFile, $expiryDays = 30)
{
    if (!file_exists($logFile)) {
        return [];
    }

    $now = time();
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newLines = [];

    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $logTime = strtotime($entry['timestamp']);
            if ($logTime >= strtotime("-{$expiryDays} days", $now)) {
                $newLines[] = $line;
            }
        }
    }

    if (!empty($newLines)) {
        file_put_contents($logFile, implode(PHP_EOL, $newLines) . PHP_EOL);
    } else {
        file_put_contents($logFile, "");
    }

    return $newLines;
}

/**
 * Filter logs based on search query and date range
 */
function al_sys_filter_logs_by_criteria_2k24($lines, $searchQuery = "", $startDate = "", $endDate = "")
{
    $logs = [];

    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (!$entry) continue;

        $include = true;

        if ($searchQuery !== "" && stripos($entry['student'], $searchQuery) === false) {
            $include = false;
        }

        $logTime = strtotime($entry['timestamp']);
        if ($startDate !== "" && $logTime < strtotime($startDate . " 00:00:00")) {
            $include = false;
        }
        if ($endDate !== "" && $logTime > strtotime($endDate . " 23:59:59")) {
            $include = false;
        }

        if ($include) {
            $logs[] = $entry;
        }
    }

    return $logs;
}

/**
 * Get all logs for a specific student
 */
function al_sys_get_student_specific_logs_2k24($logFile, $studentId)
{
    if (!file_exists($logFile)) {
        return [];
    }

    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $studentLogs = [];

    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry && stripos($entry['student'], $studentId) !== false) {
            $studentLogs[] = $entry;
        }
    }

    return $studentLogs;
}

// Handle AJAX request for logs data
if (isset($_GET['al_sys_ajax_get_logs_data_2k24']) && $_GET['al_sys_ajax_get_logs_data_2k24'] === 'true') {
    header('Content-Type: application/json');

    $searchQuery = isset($_GET['al_sys_search_query_filter_2k24']) ? trim($_GET['al_sys_search_query_filter_2k24']) : "";
    $startDate = isset($_GET['al_sys_start_date_filter_2k24']) ? $_GET['al_sys_start_date_filter_2k24'] : "";
    $endDate = isset($_GET['al_sys_end_date_filter_2k24']) ? $_GET['al_sys_end_date_filter_2k24'] : "";

    $newLines = al_sys_auto_delete_old_logs_function_2k24($al_sys_log_file_path_2k24, 30);
    $logs = al_sys_filter_logs_by_criteria_2k24($newLines, $searchQuery, $startDate, $endDate);

    $studentLogsData = [];
    foreach ($logs as $log) {
        $studentId = $log['student'];
        if (!isset($studentLogsData[$studentId])) {
            $studentLogsData[$studentId] = [];
        }
        $studentLogsData[$studentId][] = $log;
    }

    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'studentLogsData' => $studentLogsData
    ]);
    exit;
}

// Handle clear logs action
if (isset($_POST['al_sys_clear_logs_action_ajax_2k24'])) {
    header('Content-Type: application/json');
    $message = al_sys_clear_all_logs_function_2k24($al_sys_log_file_path_2k24);
    echo json_encode(['success' => true, 'message' => $message]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ICTwithLS Exam Details Management</title>
    <link rel="icon" type="image/x-icon" href="Photos/short_text_logo.jpg">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            color: #212529;
            min-height: 100vh;
            font-size: 14px;
        }

        header {
            background: #1a1a1a;
            color: white;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            font-weight: normal;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #dc3545;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #ffffff;
        }

        .header-subtitle {
            font-size: 12px;
            color: #cccccc;
            font-weight: normal;
        }

        .container {
            background: white;
            max-width: 1400px;
            margin: 50px auto;
            padding: 40px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .login-container {
            background: white;
            max-width: 500px;
            margin: 80px auto;
            padding: 50px 40px;
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            text-align: center;
            border-radius: 12px;
            position: relative;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #dc3545, #007bff, #28a745);
            border-radius: 12px 12px 0 0;
        }

        .container h2,
        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #212529;
            font-weight: bold;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }

        .security-notice {
            background: #dc3545;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .warning-text {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 12px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
        }

        .instructions {
            background: #e9ecef;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 12px;
            line-height: 1.4;
            border-radius: 0 4px 4px 0;
        }

        .instructions h3 {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            color: #dc3545;
        }

        .instructions ul {
            margin-left: 15px;
            margin-top: 8px;
        }

        .instructions li {
            margin-bottom: 3px;
        }

        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #495057;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        input[type="url"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ced4da;
            font-size: 14px;
            background: #ffffff;
            color: #212529;
            font-family: 'Courier New', monospace;
            font-weight: normal;
            text-transform: uppercase;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        input[type="url"],
        textarea {
            text-transform: none;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        input[type="time"]:focus,
        input[type="url"]:focus,
        input[type="password"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #dc3545;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        button {
            background: #dc3545;
            color: white;
            padding: 15px 25px;
            border: none;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: Arial, sans-serif;
            min-width: 150px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        button:active {
            background: #bd2130;
            transform: translateY(0);
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #218838;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-logout {
            background: #6c757d;
            color: white;
            padding: 8px 15px;
            font-size: 11px;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
            min-width: auto;
            white-space: nowrap;
            border-radius: 4px;
        }

        .btn-logout:hover {
            background: #5a6268;
            box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
        }

        .table-container {
            overflow-x: auto;
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        table,
        th,
        td {
            border: 1px solid #dee2e6;
        }

        th {
            background: #343a40;
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px 10px;
            font-size: 13px;
            font-family: 'Courier New', monospace;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        tr:hover {
            background: #e9ecef;
            transition: background-color 0.3s ease;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn-edit {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            font-size: 11px;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
            border-radius: 3px;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            font-size: 11px;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
            border-radius: 3px;
        }

        .btn-view-link {
            background: #007bff;
            color: white;
            padding: 5px 10px;
            font-size: 11px;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
            border-radius: 3px;
        }

        .btn-copy {
            background: #6f42c1;
            color: white;
            padding: 5px 10px;
            font-size: 11px;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
            border-radius: 3px;
        }

        .btn-whatsapp {
            background: #25d366;
            color: white;
            padding: 5px 10px;
            font-size: 11px;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
            border-radius: 3px;
        }

        .btn-edit:hover {
            background: #218838;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .btn-view-link:hover {
            background: #0056b3;
        }

        .btn-copy:hover {
            background: #5a2d91;
        }

        .btn-whatsapp:hover {
            background: #1da851;
        }

        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            margin-right: 10px;
            animation: blink 2s infinite;
        }

        @keyframes blink {

            0%,
            50% {
                opacity: 1;
            }

            51%,
            100% {
                opacity: 0.3;
            }
        }

        .info-bar {
            background: #343a40;
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: normal;
            font-size: 13px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
            position: relative;
            border-radius: 6px;
        }

        .system-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .system-label {
            font-weight: bold;
            color: #ffffff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            font-style: italic;
        }

        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            flex: 1;
            background: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            color: #dc3545;
        }

        .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }

        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
            display: none;
            border-radius: 4px;
        }

        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
            display: none;
            border-radius: 4px;
        }

        .login-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
            display: none;
            border-radius: 4px;
        }

        .login-attempts {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            margin-top: 15px;
            font-size: 12px;
            text-align: center;
            display: none;
            border-radius: 4px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border: 2px solid #dc3545;
            width: 80%;
            max-width: 700px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 8px;
        }

        .modal-header {
            background: #dc3545;
            color: white;
            padding: 15px;
            margin: -30px -30px 20px -30px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 8px 8px 0 0;
        }

        .close-modal {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            margin-top: 15px;
            border-radius: 4px;
        }

        .close-modal:hover {
            background: #5a6268;
        }

        .access-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #dc3545;
            background: #f8f9fa;
            padding: 2px 6px;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }

        .login-form {
            max-width: 400px;
            margin: 0 auto;
        }

        /* WhatsApp Modal Specific Styles */
        .whatsapp-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 20px 0;
        }

        .btn-whatsapp-student {
            background: #007bff;
            color: white;
            padding: 15px 25px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-whatsapp-instructor {
            background: #28a745;
            color: white;
            padding: 15px 25px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-whatsapp-student:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .btn-whatsapp-instructor:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .message-display {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .message-display h4 {
            text-align: center;
            margin-bottom: 15px;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .generated-message {
            background: white;
            border: 2px solid #25d366;
            border-radius: 8px;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            white-space: pre-wrap;
            font-size: 14px;
            color: #333;
            margin-bottom: 15px;
        }

        .btn-copy-message {
            background: #25d366;
            color: white;
            padding: 12px 20px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            border-radius: 6px;
            width: 100%;
            margin-top: 10px;
        }

        .btn-copy-message:hover {
            background: #1da851;
        }

        /* Hide admin panel initially */
        .admin-panel {
            display: none;
        }

        /* Show admin panel when authenticated */
        .admin-panel.authenticated {
            display: block;
        }

        /* Status indicators */
        .status-upcoming {
            color: #28a745;
            font-weight: bold;
        }

        .status-ongoing {
            color: #ffc107;
            font-weight: bold;
        }

        .status-completed {
            color: #6c757d;
            font-weight: bold;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            body {
                font-size: 13px;
            }

            .container,
            .login-container {
                margin: 10px;
                padding: 20px 15px;
                max-width: none;
            }

            .login-container {
                margin: 20px 10px;
                padding: 30px 20px;
            }

            .container h2,
            .login-container h2 {
                font-size: 16px;
                margin-bottom: 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 15px;
            }

            .form-group {
                min-width: auto;
            }

            input[type="text"],
            input[type="number"],
            input[type="date"],
            input[type="time"],
            input[type="url"],
            input[type="password"],
            select,
            textarea {
                padding: 14px 12px;
                font-size: 16px;
            }

            button {
                width: 100%;
                padding: 16px;
                margin-bottom: 10px;
            }

            .btn-logout {
                position: static;
                width: auto;
                margin-left: 10px;
                padding: 8px 12px;
                font-size: 11px;
            }

            .stats-container {
                flex-direction: column;
                gap: 15px;
            }

            .info-bar {
                flex-direction: column;
                gap: 10px;
                text-align: center;
                padding: 15px;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn-edit,
            .btn-delete,
            .btn-view-link,
            .btn-copy,
            .btn-whatsapp {
                width: 100%;
                margin-bottom: 5px;
            }

            header {
                padding: 15px;
            }

            .header-title {
                font-size: 14px;
            }

            .header-subtitle {
                font-size: 11px;
            }

            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 8px 6px;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
                padding: 20px;
            }

            .modal-header {
                margin: -20px -20px 15px -20px;
            }

            .whatsapp-buttons {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {

            .container,
            .login-container {
                margin: 5px;
                padding: 15px 10px;
            }

            .login-container {
                margin: 10px 5px;
                padding: 25px 15px;
            }

            header {
                padding: 10px;
            }

            .header-title {
                font-size: 12px;
            }

            table {
                font-size: 11px;
            }

            .modal-content {
                width: 98%;
                margin: 2% auto;
                padding: 15px;
            }
        }

        .access-code-display {
            background: #343a40;
            color: #fff;
            padding: 18px;
            font-family: 'Courier New', monospace;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 4px;
            margin: 14px 0;
            border: 3px solid #dc3545;
            border-radius: 6px;
            display: inline-block;
        }

        .modal-subtext {
            margin: 10px 0;
            color: #444;
            font-size: 13px;
        }

        .modal-note {
            color: #6c757d;
            font-size: 12px;
            margin-top: 14px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            text-transform: uppercase;
        }

        .btn-primary {
            background: #28a745;
            color: #fff;
        }

        .btn-primary:hover {
            background: #218838;
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        footer {
            background: #111827;
            color: #f3f4f6;
            text-align: center;
            padding: 1rem;
            font-size: 0.9rem;
            margin-top: 50px;
        }

        /* Completed Exams Table */
        .completed-exams {
            margin-top: 40px;
        }

        .completed-exams h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #212529;
            font-weight: bold;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #6c757d;
            /* Grey border to differentiate */
            padding-bottom: 10px;
        }

        .completed-exams table thead th {
            background: #6c757d;
            /* Grey header for completed exams */
        }
    </style>
    <!-- View log style -->
    <style>
        /* Main Button Style */
        .al_sys_view_logs_main_button_2k24 {
            background: #007BFF;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.3);
        }

        .al_sys_view_logs_main_button_2k24:hover {
            background: #0056b3;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
            transform: translateY(-1px);
        }

        /* Main Modal Overlay */
        .al_sys_main_modal_overlay_2k24 {
            display: none;
            position: fixed;
            z-index: 999999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            animation: al_sys_fade_in_animation_2k24 0.3s;
            overflow: auto;
        }

        .al_sys_main_modal_content_wrapper_2k24 {
            background-color: #fff;
            margin: 2% auto;
            padding: 0;
            border-radius: 10px;
            width: 95%;
            max-width: 1200px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.4);
            animation: al_sys_slide_down_animation_2k24 0.3s;
        }

        .al_sys_main_modal_header_2k24 {
            background: linear-gradient(135deg, #007BFF 0%, #0056b3 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .al_sys_main_modal_title_2k24 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
        }

        .al_sys_main_modal_close_btn_2k24 {
            color: white;
            font-size: 36px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: transparent;
            padding: 0;
            width: 36px;
            height: 36px;
            line-height: 36px;
            text-align: center;
            transition: transform 0.2s;
        }

        .al_sys_main_modal_close_btn_2k24:hover {
            transform: scale(1.2);
        }

        .al_sys_main_modal_body_2k24 {
            padding: 30px;
            background: #f8f9fa;
        }

        /* Filter Section */
        .al_sys_filters_container_section_2k24 {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .al_sys_filters_form_wrapper_2k24 {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .al_sys_filter_input_search_2k24 {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            width: 250px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .al_sys_filter_input_search_2k24:focus {
            outline: none;
            border-color: #007BFF;
        }

        .al_sys_filter_date_input_2k24 {
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }

        .al_sys_filter_date_label_2k24 {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #333;
        }

        .al_sys_filter_btn_apply_2k24,
        .al_sys_filter_btn_reset_2k24,
        .al_sys_action_btn_clear_2k24 {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .al_sys_filter_btn_apply_2k24 {
            background: #28a745;
            color: white;
        }

        .al_sys_filter_btn_apply_2k24:hover {
            background: #218838;
        }

        .al_sys_filter_btn_reset_2k24 {
            background: #6c757d;
            color: white;
        }

        .al_sys_filter_btn_reset_2k24:hover {
            background: #5a6268;
        }

        .al_sys_action_btn_clear_2k24 {
            background: #dc3545;
            color: white;
        }

        .al_sys_action_btn_clear_2k24:hover {
            background: #c82333;
        }

        /* Action Buttons Section */
        .al_sys_action_buttons_section_2k24 {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 15px;
        }

        /* Table Styles */
        .al_sys_logs_table_wrapper_2k24 {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .al_sys_logs_table_main_2k24 {
            width: 100%;
            border-collapse: collapse;
        }

        .al_sys_table_header_cell_2k24 {
            background: #007BFF;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .al_sys_table_data_cell_2k24 {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
            color: #333;
        }

        .al_sys_table_row_even_2k24 {
            background: #f8f9fa;
        }

        .al_sys_table_row_hover_2k24:hover {
            background: #e9ecef;
            transition: background 0.2s;
        }

        .al_sys_btn_view_student_log_2k24 {
            background: #17a2b8;
            color: white;
            padding: 6px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .al_sys_btn_view_student_log_2k24:hover {
            background: #138496;
            transform: scale(1.05);
        }

        .al_sys_no_logs_message_2k24 {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 16px;
            font-style: italic;
        }

        /* Student Detail Modal */
        .al_sys_student_detail_modal_2k24 {
            display: none;
            position: fixed;
            z-index: 9999999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            animation: al_sys_fade_in_animation_2k24 0.3s;
        }

        .al_sys_student_modal_content_2k24 {
            background-color: #fff;
            margin: 3% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 1000px;
            max-height: 85vh;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.5);
            animation: al_sys_slide_down_animation_2k24 0.3s;
            overflow: hidden;
        }

        .al_sys_student_modal_header_2k24 {
            background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .al_sys_student_modal_title_2k24 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .al_sys_student_modal_close_2k24 {
            color: white;
            font-size: 36px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: transparent;
            padding: 0;
            width: 36px;
            height: 36px;
            line-height: 36px;
            text-align: center;
            transition: transform 0.2s;
        }

        .al_sys_student_modal_close_2k24:hover {
            transform: rotate(90deg);
        }

        .al_sys_student_modal_body_2k24 {
            padding: 30px;
            max-height: calc(85vh - 100px);
            overflow-y: auto;
            background: #f8f9fa;
        }

        .al_sys_student_logs_table_2k24 {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .al_sys_student_table_header_2k24 {
            background: #f1f3f5;
        }

        .al_sys_student_logs_table_2k24 th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .al_sys_student_logs_table_2k24 td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
        }

        .al_sys_student_logs_table_2k24 tbody tr:hover {
            background: #f8f9fa;
        }

        .al_sys_student_no_data_2k24 {
            text-align: center;
            padding: 50px;
            color: #6c757d;
            font-size: 16px;
            font-style: italic;
        }

        /* Loading Spinner */
        .al_sys_loading_spinner_2k24 {
            text-align: center;
            padding: 40px;
            color: #007BFF;
            font-size: 16px;
        }

        .al_sys_spinner_icon_2k24 {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007BFF;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: al_sys_spin_animation_2k24 1s linear infinite;
            margin: 0 auto 15px;
        }

        /* Animations */
        @keyframes al_sys_fade_in_animation_2k24 {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes al_sys_slide_down_animation_2k24 {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes al_sys_spin_animation_2k24 {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Message Box */
        .al_sys_message_notification_2k24 {
            margin-bottom: 20px;
            padding: 15px 20px;
            background: #d4edda;
            color: #155724;
            border-radius: 6px;
            border-left: 4px solid #28a745;
            font-weight: 600;
        }

        .al_sys_message_error_2k24 {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
    </style>
</head>

<body oncontextmenu="return false" onselectstart="return false" ondragstart="return false">

    <header>
        <div class="header-title">ICTwithLS EXAMINATION MANAGEMENT SYSTEM</div>
        <div class="header-subtitle">Exam Details Management | Administrative Panel | Secure Environment</div>
    </header>

    <!-- Login Section -->
    <div id="loginSection">
        <div class="login-container">
            <div class="security-notice">
                🔐 ADMINISTRATIVE LOGIN REQUIRED 🔐
            </div>

            <div class="logo-container">
                <img src="Photos/Long_text_logo.jpg" alt="ICTwithLS Logo" width="200"
                    onerror="this.style.display='none'" />
            </div>

            <h2>Administrator Authentication</h2>

            <div class="warning-text">
                ⚠️ AUTHORIZED PERSONNEL ONLY ⚠️
            </div>

            <div class="login-error" id="loginError"></div>

            <div class="login-form">
                <div class="form-group">
                    <label for="adminUsername">Administrator Username</label>
                    <input type="text" id="adminUsername" placeholder="ENTER ADMIN USERNAME" required>
                </div>

                <div class="form-group">
                    <label for="adminPassword">Administrator Password</label>
                    <input type="password" id="adminPassword" placeholder="ENTER ADMIN PASSWORD" required>
                </div>

                <button onclick="authenticateAdmin()" id="loginBtn">LOGIN TO ADMIN PANEL</button>

                <div class="login-attempts" id="loginAttempts"></div>
            </div>
        </div>
    </div>

    <!-- Admin Panel Section -->
    <div id="adminPanel" class="admin-panel">

        <!-- Access Code Modal -->
        <div id="accessCodeModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    🔐 EXAMINATION ACCESS CODE GENERATED
                </div>
                <p><strong>Paper Access Code has been generated successfully!</strong></p>
                <p class="modal-subtext">Please save this code securely. Students will need this code to access the
                    examination:</p>

                <div class="access-code-display" id="generatedAccessCode">XXXXXX</div>

                <p class="modal-note">
                    ⚠️ This code is required for exam access and cannot be recovered if lost!
                </p>

                <div class="modal-actions">
                    <button class="btn btn-primary" id="copyAccessBtn">COPY CODE</button>
                    <button class="btn btn-secondary" onclick="closeAccessCodeModal()">CLOSE</button>
                </div>
            </div>
        </div>

        <!-- WhatsApp Message Modal -->
        <div id="whatsappModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    📱 WHATSAPP MESSAGE GENERATOR
                </div>
                <p><strong>Select message type:</strong></p>
                <div class="whatsapp-buttons">
                    <button onclick="generateStudentMessage()" class="btn-whatsapp-student">STUDENT MESSAGE</button>
                    <button onclick="generateInstructorMessage()" class="btn-whatsapp-instructor">INSTRUCTOR
                        MESSAGE</button>
                </div>
                <div id="messageDisplay" class="message-display" style="display: none;">
                    <h4>Generated Message:</h4>
                    <div id="generatedMessage" class="generated-message"></div>
                    <button onclick="copyMessage()" class="btn-copy-message">COPY MESSAGE</button>
                </div>
                <button class="close-modal" onclick="closeWhatsappModal()">CLOSE</button>
            </div>
        </div>

        <div class="container">
            <div class="security-notice">
                🔒 ADMINISTRATIVE PANEL - AUTHORIZED ACCESS ONLY 🔒
            </div>

            <div class="logo-container">
                <img src="Photos/Long_text_logo.jpg" alt="ICTwithLS Logo" width="250"
                    onerror="this.style.display='none'" />
            </div>

            <h2>Exam Details Management</h2>

            <div class="info-bar">
                <div class="system-info">
                    <span class="status-indicator"></span>
                    <span class="system-label">SYSTEM STATUS:</span>
                    <span>ONLINE & MONITORING</span>
                    <span class="system-label" style="margin-left: 30px;">ADMIN:</span>
                    <span id="currentAdmin">AUTHENTICATED</span>
                </div>
                <div>
                    <button class="btn-logout" onclick="logoutAdmin()">LOGOUT</button>
                    <span style="margin-right: 15px; font-weight: bold;">CURRENT TIME:</span>
                    <span id="currentTime" style="font-family: 'Courier New', monospace; font-weight: bold;"></span>
                </div>
            </div>

            <div class="warning-text">
                ⚠️ EXAMINATION CONFIGURATION PANEL - HANDLE WITH CARE ⚠️
            </div>

            <div class="instructions">
                <h3>System Instructions:</h3>
                <ul>
                    <li>All exam details must be verified before submission</li>
                    <li>Duration format: Hours (0-24) and Minutes (0-59)</li>
                    <li>Deadline is set automatically to the current time when exam is created</li>
                    <li>Access codes are generated automatically and shown after exam creation</li>
                    <li>Paper numbers should follow institutional standards</li>
                    <li>Exam dates can only be set for future dates and times</li>
                    <li>Use WhatsApp message feature to communicate with students and instructors</li>
                </ul>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number" id="totalExams">0</div>
                    <div class="stat-label">Total Exams</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="upcomingExams">0</div>
                    <div class="stat-label">On-Going Exams</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalDuration">0H 0M</div>
                    <div class="stat-label">Total Duration</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="activeAccessCodes">0</div>
                    <div class="stat-label">Active Access Codes</div>
                </div>
            </div>

            <div class="success-message" id="successMessage"></div>
            <div class="error-message" id="errorMessage"></div>

            <div class="form-section">
                <form id="examForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="subject">Subject Name</label>
                            <input type="text" id="subject" placeholder="ENTER SUBJECT NAME" required>
                        </div>
                        <div class="form-group">
                            <label for="paperNo">Paper Number</label>
                            <input type="text" id="paperNo" placeholder="ENTER PAPER NUMBER" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="durationHours">Duration Hours</label>
                            <input type="number" id="durationHours" placeholder="0-24" min="0" max="24" value="0" required>
                        </div>
                        <div class="form-group">
                            <label for="durationMinutes">Duration Minutes</label>
                            <input type="number" id="durationMinutes" placeholder="0-59" min="0" max="59" value="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="examDate">Examination End Date</label>
                            <input type="date" id="examDate" required>
                        </div>
                        <div class="form-group">
                            <label for="deadline">Deadline</label>
                            <input type="time" id="deadline" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="paperLink">Paper Link (URL)</label>
                            <input type="url" id="paperLink" placeholder="https://example.com/paper-link" required>
                        </div>
                        <div class="form-group">
                            <label for="mcqLink">MCQ/Answer Submission Link (Optional)</label>
                            <input type="url" id="mcqLink" placeholder="https://forms.gle/... (Optional)">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="examInstructions">Special Instructions (Optional)</label>
                            <textarea id="examInstructions"
                                placeholder="Any special instructions for students..."></textarea>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" id="submitBtn">ADD EXAMINATION</button>
                        <button type="button" id="clearBtn" onclick="clearForm()"
                            style="background: #6c757d; margin-left: 10px;">CLEAR FORM</button>
                        <button type="button" onclick="openWhatsappModal()"
                            style="background: #25d366; margin-left: 10px;">📱 WHATSAPP MESSAGE</button>
                        <button type="button" id="clearattemptBtn"
                            style="background: #0704b3; color: #ffffff;margin-left: 10px;">Clear student exam history</button>
                        <button class="al_sys_view_logs_main_button_2k24" id="al_sys_open_logs_modal_btn_2k24" onclick="al_sys_open_main_logs_modal_2k24()">
                            📊 View Activity Logs </button>
                    </div>
                </form>
            </div>

            <!-- Student Exam Deatils View -->
            <div id="studentsModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">Accessed Students</div>
                    <button id="clearSelectedBtn" class="btn btn-primary" style="margin-bottom: 20px;">Clear Selected Records</button>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 48px;"><input type="checkbox" id="selectAll" title="Select All"></th>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Access Code</th>
                                <th>Access Time</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody"></tbody>
                    </table>
                    <button id="closeModalBtn" class="close-modal">Close</button>
                </div>
            </div>

            <div class="table-container">
                <h2>Active Examinations</h2> <!-- Updated heading for clarity -->
                <table id="examTable">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Paper No</th>
                            <th>Duration</th>
                            <th>End Date</th>
                            <th>Deadline</th>
                            <th>Access Code</th>
                            <th>Paper Link</th>
                            <th>MCQ Link</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="examTableBody">
                        <tr>
                            <td colspan="11" class="empty-state">
                                NO ACTIVE EXAMINATIONS CONFIGURED<br>
                                <small>Add your first exam using the form above</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- New Table for Completed Exams -->
            <div class="table-container completed-exams">
                <h2>Completed Examinations</h2>
                <table id="completedExamTable">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Paper No</th>
                            <th>Duration</th>
                            <th>End Date</th>
                            <th>Deadline</th>
                            <th>Access Code</th>
                            <th>Paper Link</th>
                            <th>MCQ Link</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="completedExamTableBody">
                        <tr>
                            <td colspan="11" class="empty-state">
                                NO COMPLETED EXAMINATIONS<br>
                                <small>Completed exams will appear here</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Exam Student Log -->
        <!-- Activity Logs Modal -->
        <div id="al_sys_main_logs_modal_2k24" class="al_sys_main_modal_overlay_2k24">
            <div class="al_sys_main_modal_content_wrapper_2k24">
                <div class="al_sys_main_modal_header_2k24">
                    <h2 class="al_sys_main_modal_title_2k24">Activity Logs</h2>
                    <button class="al_sys_main_modal_close_btn_2k24" onclick="al_sys_close_main_logs_modal_2k24()">&times;</button>
                </div>

                <div class="al_sys_main_modal_body_2k24">
                    <div id="al_sys_message_box_2k24" class="al_sys_message_notification_2k24" style="display: none;"></div>
                    <!-- Filter Section -->
                    <div class="al_sys_filters_container_section_2k24">
                        <div class="al_sys_filters_form_wrapper_2k24">
                            <input type="text" id="al_sys_search_input_field_2k24" class="al_sys_filter_input_search_2k24" placeholder="Search by student ID or name">
                            <label class="al_sys_filter_date_label_2k24">
                                From:
                                <input type="date" id="al_sys_start_date_input_2k24" class="al_sys_filter_date_input_2k24">
                            </label>
                            <label class="al_sys_filter_date_label_2k24">
                                To:
                                <input type="date" id="al_sys_end_date_input_2k24" class="al_sys_filter_date_input_2k24">
                            </label>
                            <button class="al_sys_filter_btn_apply_2k24" onclick="al_sys_apply_filters_and_reload_2k24()">🔍 Apply Filter</button>
                            <button class="al_sys_filter_btn_reset_2k24" onclick="al_sys_reset_filters_and_reload_2k24()">🔄 Reset</button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="al_sys_action_buttons_section_2k24">
                        <button class="al_sys_action_btn_clear_2k24" onclick="al_sys_clear_all_logs_confirm_2k24()">🗑️ Clear All Records</button>
                    </div>

                    <!-- Logs Table -->
                    <div class="al_sys_logs_table_wrapper_2k24">
                        <div id="al_sys_loading_container_2k24" class="al_sys_loading_spinner_2k24" style="display: none;">
                            <div class="al_sys_spinner_icon_2k24"></div>
                            Loading logs...
                        </div>
                        <table class="al_sys_logs_table_main_2k24" id="al_sys_logs_table_2k24">
                            <thead>
                                <tr>
                                    <th class="al_sys_table_header_cell_2k24">Timestamp</th>
                                    <th class="al_sys_table_header_cell_2k24">Student</th>
                                    <th class="al_sys_table_header_cell_2k24">Event</th>
                                    <th class="al_sys_table_header_cell_2k24">Data</th>
                                    <th class="al_sys_table_header_cell_2k24">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="al_sys_logs_table_body_2k24">
                                <!-- Content will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Detail Modal -->
        <div id="al_sys_student_detail_modal_2k24" class="al_sys_student_detail_modal_2k24">
            <div class="al_sys_student_modal_content_2k24">
                <div class="al_sys_student_modal_header_2k24">
                    <h2 class="al_sys_student_modal_title_2k24" id="al_sys_student_name_title_2k24">Student Activity Log</h2>
                    <button class="al_sys_student_modal_close_2k24" onclick="al_sys_close_student_detail_modal_2k24()">&times;</button>
                </div>
                <div class="al_sys_student_modal_body_2k24" id="al_sys_student_modal_body_content_2k24">
                    <!-- Content will be dynamically loaded here -->
                </div>
            </div>
        </div>

    </div>

    <footer>
        <p class="copyright">© 2025 L.S.COMPUTER TECHNOLOGH. All Rights Reserved.</p>
    </footer>

    <script>
        // Login credentials (in production, this should be on server-side)
        const adminCredentials = {
            'lakindu': 'ur^#kaa9',
            'manel': 'gk$sersV'
        };

        let isAuthenticated = false;
        let loginAttempts = 0;
        const maxAttempts = 5;
        let exams = [];
        let editIndex = null;
        let selectedExamForMessage = null;

        let idleTimeout;
        const IDLE_TIMEOUT_MINUTES = 5; // 5 minutes

        function resetIdleTimer() {
            clearTimeout(idleTimeout);
            idleTimeout = setTimeout(() => {
                logoutAdminTimeOut(true); // Pass true to indicate idle timeout
            }, IDLE_TIMEOUT_MINUTES * 60 * 1000);
        }

        function startIdleTimer() {
            if (isAuthenticated) {
                resetIdleTimer();
                // Listen for user activity
                ['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart'].forEach(event => {
                    document.addEventListener(event, resetIdleTimer);
                });
            }
        }

        // Check if already logged in and load data
        document.addEventListener('DOMContentLoaded', function() {
            const savedAuth = localStorage.getItem('adminAuth');
            const savedUsername = localStorage.getItem('adminUsername');

            if (savedAuth === 'true' && savedUsername) {
                isAuthenticated = true;
                showAdminPanel(savedUsername);
                loadExams();
                startIdleTimer(); // Start idle timer on successful login
            } else {
                showLoginForm();
            }

            // Auto-capitalize input text
            const textInputs = document.querySelectorAll('input[type="text"]:not(#adminUsername)');
            textInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    const cursorPosition = e.target.selectionStart;
                    e.target.value = e.target.value.toUpperCase();
                    e.target.setSelectionRange(cursorPosition, cursorPosition);
                });
            });

            // Set minimum date and time, load existing exams
            setMinimumDateTime();
            updateCurrentTime();
            setInterval(updateCurrentTime, 1000);

            // Enter key login
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !isAuthenticated) {
                    e.preventDefault();
                    authenticateAdmin();
                }
            });

            // Update statuses every 20s
            setInterval(updateExamStatuses, 20000);
        });

        document.getElementById("clearattemptBtn").addEventListener("click", () => {
            if (confirm("⚠️ Are you sure you want to clear all student exam history?")) {
                fetch("clear_history.php", {
                        method: "POST"
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            alert("✅ " + data.message);
                        } else {
                            alert("❌ " + (data.error || "Unknown error"));
                        }
                    })
                    .catch(err => alert("Error: " + err));
            }
        });

        // Authentication functions
        function authenticateAdmin() {
            const username = document.getElementById('adminUsername').value.trim().toLowerCase();
            const password = document.getElementById('adminPassword').value.trim();
            const loginBtn = document.getElementById('loginBtn');
            const loginError = document.getElementById('loginError');
            const loginAttemptsEl = document.getElementById('loginAttempts');

            // Clear previous errors
            loginError.style.display = 'none';

            // Check attempt limit
            if (loginAttempts >= maxAttempts) {
                showLoginError('MAXIMUM LOGIN ATTEMPTS EXCEEDED. PLEASE CONTACT SYSTEM ADMINISTRATOR.');
                loginBtn.disabled = true;
                loginBtn.style.background = '#6c757d';
                return;
            }

            // Validate input
            if (!username || !password) {
                showLoginError('ERROR: BOTH USERNAME AND PASSWORD ARE REQUIRED');
                return;
            }

            // Show loading state
            loginBtn.textContent = 'AUTHENTICATING...';
            loginBtn.disabled = true;

            // Simulate authentication delay
            setTimeout(() => {
                if (adminCredentials[username] && adminCredentials[username] === password) {
                    // Successful login
                    isAuthenticated = true;
                    localStorage.setItem('adminAuth', 'true');
                    localStorage.setItem('adminUsername', username);

                    showAdminPanel(username);
                    loadExams();
                    startIdleTimer(); // Start idle timer after successful login
                } else {
                    // Failed login
                    loginAttempts++;
                    showLoginError(`INVALID CREDENTIALS. ATTEMPT ${loginAttempts}/${maxAttempts}`);

                    if (loginAttempts < maxAttempts) {
                        loginAttemptsEl.innerHTML = `⚠️ Login attempts remaining: ${maxAttempts - loginAttempts}`;
                        loginAttemptsEl.style.display = 'block';
                    }

                    // Clear password field
                    document.getElementById('adminPassword').value = '';
                }

                // Reset button
                loginBtn.textContent = 'LOGIN TO ADMIN PANEL';
                loginBtn.disabled = false;
            }, 1500);
        }

        // Show Access Code Modal
        function showAccessCodeModal(code) {
            document.getElementById('generatedAccessCode').textContent = code;
            document.getElementById('accessCodeModal').style.display = 'block';
        }

        // Close Access Code Modal
        function closeAccessCodeModal() {
            document.getElementById('accessCodeModal').style.display = 'none';
        }

        // Copy Access Code
        document.getElementById('copyAccessBtn')?.addEventListener('click', function() {
            const code = document.getElementById('generatedAccessCode').textContent;
            navigator.clipboard.writeText(code).then(() => {
                alert("Access code copied: " + code);
            });
        });

        function showLoginError(message) {
            const loginError = document.getElementById('loginError');
            loginError.textContent = message;
            loginError.style.display = 'block';
        }

        function showLoginForm() {
            document.getElementById('loginSection').style.display = 'block';
            document.getElementById('adminPanel').style.display = 'none';
        }

        function showAdminPanel(username) {
            document.getElementById('loginSection').style.display = 'none';
            document.getElementById('adminPanel').style.display = 'block';
            document.getElementById('currentAdmin').textContent = username.toUpperCase();
        }

        function logoutAdmin() {
            if (confirm('Are you sure you want to logout from the admin panel?')) {
                isAuthenticated = false;
                localStorage.removeItem('adminAuth');
                localStorage.removeItem('adminUsername');

                // Clear all data
                exams = [];
                editIndex = null;
                selectedExamForMessage = null;

                // Reset forms
                document.getElementById('examForm').reset();

                // Show login form
                showLoginForm();

                // Clear login form
                document.getElementById('adminUsername').value = '';
                document.getElementById('adminPassword').value = '';
                document.getElementById('loginError').style.display = 'none';
                document.getElementById('loginAttempts').style.display = 'none';

                // Reset login attempts
                loginAttempts = 0;
                document.getElementById('loginBtn').disabled = false;
                document.getElementById('loginBtn').style.background = '#dc3545';
            }
        }

        // call when time out
        function logoutAdminTimeOut() {
            alert('User movement didnt detect in 5min. logging out');
            isAuthenticated = false;
            localStorage.removeItem('adminAuth');
            localStorage.removeItem('adminUsername');

            // Clear all data
            exams = [];
            editIndex = null;
            selectedExamForMessage = null;

            // Clear idle timer and event listeners
            clearTimeout(idleTimeout);
            ['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart'].forEach(event => {
                document.removeEventListener(event, resetIdleTimer);
            });

            // Reset forms
            document.getElementById('examForm').reset();

            // Show login form
            showLoginForm();

            // Clear login form
            document.getElementById('adminUsername').value = '';
            document.getElementById('adminPassword').value = '';
            document.getElementById('loginError').style.display = 'none';
            document.getElementById('loginAttempts').style.display = 'none';

            // Reset login attempts
            loginAttempts = 0;
            document.getElementById('loginBtn').disabled = false;
            document.getElementById('loginBtn').style.background = '#dc3545';
        }

        // Generate random 6-digit access code
        function generateAccessCode() {
            return Math.floor(100000 + Math.random() * 900000).toString();
        }

        // Get current time as deadline
        function getCurrentTimeString() {
            const now = new Date();
            return now.toTimeString().slice(0, 5);
        }

        // Format createdAt timestamp
        function formatCreatedAt(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }

        // Check if exam date/time is in future
        function isDateTimeFuture(examDate, deadline) {
            const examDateTime = new Date(`${examDate}T${deadline}`);
            const now = new Date();
            return examDateTime > now;
        }

        // Get exam status
        function getExamStatus(exam) {
            const now = new Date();
            const examStart = new Date(`${exam.examDate}T${exam.deadline}`);
            const examEnd = new Date(examStart.getTime() + (exam.durationHours * 60 + exam.durationMinutes) * 60000);

            console.log(`Exam: ${exam.subject}, Start: ${examStart}, End: ${examEnd}, Now: ${now}`);

            if (now < examStart) {
                return {
                    status: 'ONGOING',
                    class: 'status-upcoming'
                };
            } else if (now >= examStart && now <= examEnd) {
                return {
                    status: 'ENDING',
                    class: 'status-ongoing'
                };
            } else {
                return {
                    status: 'COMPLETED',
                    class: 'status-completed'
                };
            }
        }

        // Set minimum date and time
        function setMinimumDateTime() {
            const now = new Date();
            const today = now.toISOString().split('T')[0];
            const currentTime = now.toTimeString().slice(0, 5);

            const examDateInput = document.getElementById('examDate');
            const deadlineInput = document.getElementById('deadline');

            if (examDateInput) {
                examDateInput.setAttribute('min', today);
                if (!examDateInput.value) {
                    examDateInput.value = today;
                }
            }

            if (deadlineInput) {
                if (!deadlineInput.value) {
                    deadlineInput.value = currentTime;
                }
            }
        }

        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        const API_URL = "save_exams.php"; // 🔗 Replace with your full InfinityFree URL, e.g., https://yourdomain.infinityfreeapp.com/save_exams.php

        function loadExams() {
            fetch(API_URL, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok: ' + res.statusText);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Loaded exams:', data);
                    exams = Array.isArray(data) ? data : [];
                    updateExamStatuses();
                    renderTable();
                    updateStats();
                })
                .catch(err => {
                    console.error("Error loading exams:", err);
                    showMessage('Error loading exams: ' + err.message, 'error');
                });
        }

        // Save exams to server
        function saveExams() {
            if (!isAuthenticated) return;

            fetch('save_exams.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'save_exams',
                        exams
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        renderTable();
                        updateStats();
                        showMessage('Exams saved successfully!', 'success');
                    } else {
                        showMessage(`ERROR: Failed to save exams - ${data.error || 'Unknown error'}`, 'error');
                    }
                })
                .catch(error => {
                    showMessage(`ERROR: Failed to save exams - ${error.message}`, 'error');
                });
        }

        // Update exam statuses
        function updateExamStatuses() {
            if (!isAuthenticated || exams.length === 0) return;
            renderTable();
            updateStats();
        }

        // WhatsApp message functions
        function openWhatsappModal() {
            if (!isAuthenticated) return;

            if (exams.length === 0) {
                showMessage('ERROR: No exams available. Add at least one exam first.', 'error');
                return;
            }

            document.getElementById('whatsappModal').style.display = 'block';
            document.getElementById('messageDisplay').style.display = 'none';
        }

        function closeWhatsappModal() {
            document.getElementById('whatsappModal').style.display = 'none';
            document.getElementById('messageDisplay').style.display = 'none';
            selectedExamForMessage = null;
        }

        function prepareWhatsapp(index) {
            if (!isAuthenticated) return;
            selectedExamForMessage = exams[index];
            openWhatsappModal();
        }

        function selectExamForMessage() {
            if (selectedExamForMessage) {
                return selectedExamForMessage;
            }
            const upcomingExams = exams.filter(exam => {
                const status = getExamStatus(exam);
                return status.status === 'On-Going';
            });

            if (upcomingExams.length > 0) {
                return upcomingExams[0];
            } else if (exams.length > 0) {
                return exams[exams.length - 1];
            }

            return null;
        }

        function generateStudentMessage() {
            if (!isAuthenticated) return;

            const exam = selectExamForMessage();
            if (!exam) {
                showMessage('ERROR: No exam available for message generation.', 'error');
                return;
            }

            const examDate = new Date(exam.examDate).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            const deadline = new Date(`2000-01-01T${exam.deadline}`).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            const message = `📚 EXAMINATION NOTIFICATION 📚

Dear Students,

This is to inform you about your On-Going examination:

📋 Subject: ${exam.subject}
📄 Paper: ${exam.paperNo}
📅 Examination End Date: ${examDate}
⏰ Deadline: ${deadline}
⏱️ Duration: ${exam.durationHours}H ${exam.durationMinutes}M

📝 Instructions:
${exam.instructions || 'Follow standard examination procedures'}

Be ready and well-prepared! Good luck with your examination! 🍀

Best wishes,

Paper Panel,
ICTwithLS`;

            displayGeneratedMessage(message);
        }

        function generateInstructorMessage() {
            if (!isAuthenticated) return;

            const exam = selectExamForMessage();
            if (!exam) {
                showMessage('ERROR: No exam available for message generation.', 'error');
                return;
            }

            const examDate = new Date(exam.examDate).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            const deadline = new Date(`2000-01-01T${exam.deadline}`).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            const message = `🎓 INSTRUCTOR EXAMINATION DETAILS 🎓

Dear Instructor,

Here are the complete details for the On-Going examination:

📋 Subject: ${exam.subject}
📄 Paper Number: ${exam.paperNo}
📅 Examination End Date: ${examDate}
⏰ Deadline: ${deadline}
⏱️ Duration: ${exam.durationHours}H ${exam.durationMinutes}M

🔐 Access Code: ${exam.accessCode}

🔗 Paper Link: ${exam.paperLink}

📝 MCQ/Submission Link: ${exam.mcqLink || 'Not provided'}

📋 Special Instructions:
${exam.instructions || 'Standard examination procedures apply'}

Please ensure all systems are ready and students are informed about the access requirements.

Best regards,

Paper Panel,
ICTwithLS`;

            displayGeneratedMessage(message);
        }

        function displayGeneratedMessage(message) {
            document.getElementById('generatedMessage').textContent = message;
            document.getElementById('messageDisplay').style.display = 'block';
        }

        function copyMessage() {
            const message = document.getElementById('generatedMessage').textContent;

            navigator.clipboard.writeText(message).then(() => {
                showMessage('Message copied to clipboard successfully!', 'success');
            }).catch(() => {
                const textArea = document.createElement('textarea');
                textArea.value = message;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showMessage('Message copied to clipboard successfully!', 'success');
            });
        }

        // Render table
        function renderTable() {
            if (!isAuthenticated) return;

            const tableBody = document.getElementById('examTableBody');
            const completedTableBody = document.getElementById('completedExamTableBody');

            // Filter exams into active (UPCOMING, ONGOING) and completed (COMPLETED)
            const activeExams = exams.filter(exam => {
                const statusInfo = getExamStatus(exam);
                return statusInfo.status !== 'COMPLETED';
            });
            const completedExams = exams.filter(exam => {
                const statusInfo = getExamStatus(exam);
                return statusInfo.status === 'COMPLETED';
            });

            // Render Active Exams Table
            if (activeExams.length === 0) {
                tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="empty-state">
                    NO ACTIVE EXAMINATIONS CONFIGURED<br>
                    <small>Add your first exam using the form above</small>
                </td>
            </tr>
        `;
            } else {
                tableBody.innerHTML = '';
                activeExams.forEach((exam, index) => {
                    const statusInfo = getExamStatus(exam);
                    const createdAt = formatCreatedAt(exam.createdAt);

                    const row = document.createElement('tr');
                    row.innerHTML = `
                <td>${exam.subject}</td>
                <td>${exam.paperNo}</td>
                <td>${exam.durationHours}H ${exam.durationMinutes}M</td>
                <td>${exam.examDate}</td>
                <td>${exam.deadline}</td>
                <td><span class="access-code">${exam.accessCode}</span></td>
                <td><a href="${exam.paperLink}" target="_blank" style="color: #007bff;">VIEW</a></td>
                <td>${exam.mcqLink ? `<a href="${exam.mcqLink}" target="_blank" style="color: #007bff;">OPEN</a>` : '<span style="color: #6c757d;">N/A</span>'}</td>
                <td><span class="${statusInfo.class}">${statusInfo.status}</span></td>
                <td>${createdAt}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-view-link" onclick="openPaperLink('${exam.paperLink}')">VIEW PAPER</button>
                        <button class="btn-copy" onclick="copyAccessCode('${exam.accessCode}')">COPY CODE</button>
                        <button class="btn-whatsapp" onclick="prepareWhatsapp(${exams.indexOf(exam)})">WHATSAPP</button>
                        <button class="btn-edit" onclick="editExam(${exams.indexOf(exam)})">EDIT</button>
                        <button class="btn-delete" style="color:rgb(0, 0, 0); background-color:rgb(255, 102, 0);" onclick="showStudentsModal('${exam.accessCode}')">VIEW STUDENTS</button>
                        <button class="btn-delete" onclick="deleteExam(${exams.indexOf(exam)})">DELETE</button>
                    </div>
                </td>
            `;
                    tableBody.appendChild(row);
                });
            }

            // Render Completed Exams Table
            if (completedExams.length === 0) {
                completedTableBody.innerHTML = `
            <tr>
                <td colspan="11" class="empty-state">
                    NO COMPLETED EXAMINATIONS<br>
                    <small>Completed exams will appear here</small>
                </td>
            </tr>
        `;
            } else {
                completedTableBody.innerHTML = '';
                completedExams.forEach((exam, index) => {
                    const statusInfo = getExamStatus(exam);
                    const createdAt = formatCreatedAt(exam.createdAt);

                    const row = document.createElement('tr');
                    row.innerHTML = `
                <td>${exam.subject}</td>
                <td>${exam.paperNo}</td>
                <td>${exam.durationHours}H ${exam.durationMinutes}M</td>
                <td>${exam.examDate}</td>
                <td>${exam.deadline}</td>
                <td><span class="access-code">${exam.accessCode}</span></td>
                <td><a href="${exam.paperLink}" target="_blank" style="color: #007bff;">VIEW</a></td>
                <td>${exam.mcqLink ? `<a href="${exam.mcqLink}" target="_blank" style="color: #007bff;">OPEN</a>` : '<span style="color: #6c757d;">N/A</span>'}</td>
                <td><span class="${statusInfo.class}">${statusInfo.status}</span></td>
                <td>${createdAt}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-view-link" onclick="openPaperLink('${exam.paperLink}')">VIEW PAPER</button>
                        <button class="btn-edit" onclick="editExam(${exams.indexOf(exam)})">EDIT</button>
                        <button class="btn-delete" style="color:rgb(0, 0, 0); background-color:rgb(255, 102, 0);" onclick="showStudentsModal('${exam.accessCode}')">VIEW STUDENTS</button>
                        <button class="btn-delete" onclick="deleteExam(${exams.indexOf(exam)})">DELETE</button>
                    </div>
                </td>
            `;
                    completedTableBody.appendChild(row);
                });
            }
        }

        function openPaperLink(link) {
            if (!isAuthenticated) return;
            if (link) {
                window.open(link, '_blank');
            } else {
                showMessage('ERROR: No paper link available for this exam', 'error');
            }
        }

        function copyAccessCode(code) {
            if (!isAuthenticated) return;
            navigator.clipboard.writeText(code).then(() => {
                showMessage(`Access code ${code} copied to clipboard!`, 'success');
            }).catch(() => {
                const textArea = document.createElement('textarea');
                textArea.value = code;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showMessage(`Access code ${code} copied to clipboard!`, 'success');
            });
        }

        function updateStats() {
            if (!isAuthenticated) return;

            const totalExams = exams.length;
            const ongoingExams = exams.filter(exam => {
                const status = getExamStatus(exam);
                return status.status === 'ONGOING' || status.status === 'ENDING';
            }).length;

            let totalHours = 0,
                totalMinutes = 0;
            exams.forEach(exam => {
                totalHours += parseInt(exam.durationHours);
                totalMinutes += parseInt(exam.durationMinutes);
            });

            totalHours += Math.floor(totalMinutes / 60);
            totalMinutes = totalMinutes % 60;

            document.getElementById('totalExams').textContent = totalExams;
            document.getElementById('upcomingExams').textContent = ongoingExams;
            document.getElementById('totalDuration').textContent = `${totalHours}H ${totalMinutes}M`;
            document.getElementById('activeAccessCodes').textContent = ongoingExams;
        }

        function editExam(index) {
            if (!isAuthenticated) return;

            const exam = exams[index];
            document.getElementById('subject').value = exam.subject;
            document.getElementById('paperNo').value = exam.paperNo;
            document.getElementById('durationHours').value = exam.durationHours;
            document.getElementById('durationMinutes').value = exam.durationMinutes;
            document.getElementById('examDate').value = exam.examDate;
            document.getElementById('deadline').value = exam.deadline;
            document.getElementById('paperLink').value = exam.paperLink;
            document.getElementById('mcqLink').value = exam.mcqLink;
            document.getElementById('examInstructions').value = exam.instructions || '';

            editIndex = index;
            document.getElementById('submitBtn').textContent = 'UPDATE EXAMINATION';

            document.querySelector('.form-section').scrollIntoView({
                behavior: 'smooth'
            });
            showMessage('Exam loaded for editing. Modify details and click UPDATE.', 'success');
        }

        function deleteExam(index) {
            if (!isAuthenticated) return;

            const exam = exams[index];
            if (confirm(`Are you sure you want to delete this examination?\n\nSubject: ${exam.subject}\nPaper: ${exam.paperNo}\nDate: ${exam.examDate}\nAccess Code: ${exam.accessCode}\n\nThis action cannot be undone.`)) {
                exams.splice(index, 1);
                saveExams();
                showMessage('Examination deleted successfully!', 'success');
            }
        }

        function clearForm() {
            if (!isAuthenticated) return;

            document.getElementById('examForm').reset();
            editIndex = null;
            document.getElementById('submitBtn').textContent = 'ADD EXAMINATION';
            setMinimumDateTime();
            showMessage('Form cleared successfully!', 'success');
        }

        function showMessage(message, type) {
            if (!isAuthenticated && type !== 'error') return;

            const successEl = document.getElementById('successMessage');
            const errorEl = document.getElementById('errorMessage');

            successEl.style.display = 'none';
            errorEl.style.display = 'none';

            if (type === 'success') {
                successEl.textContent = message;
                successEl.style.display = 'block';
                setTimeout(() => successEl.style.display = 'none', 5000);
            } else {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
                setTimeout(() => errorEl.style.display = 'none', 5000);
            }
        }

        // Form submission handler
        if (document.getElementById('examForm')) {
            document.getElementById('examForm').addEventListener('submit', function(e) {
                e.preventDefault();
                if (!isAuthenticated) return;

                const subject = document.getElementById('subject').value.trim();
                const paperNo = document.getElementById('paperNo').value.trim();
                const durationHours = parseInt(document.getElementById('durationHours').value);
                const durationMinutes = parseInt(document.getElementById('durationMinutes').value);
                const examDate = document.getElementById('examDate').value;
                const deadline = document.getElementById('deadline').value;
                const paperLink = document.getElementById('paperLink').value.trim();
                const mcqLink = document.getElementById('mcqLink').value.trim();
                const instructions = document.getElementById('examInstructions').value.trim();

                if (!subject || !paperNo || isNaN(durationHours) || isNaN(durationMinutes) || !examDate || !deadline || !paperLink) {
                    showMessage('ERROR: ALL MANDATORY FIELDS MUST BE COMPLETED (MCQ LINK IS OPTIONAL)', 'error');
                    return;
                }

                if (durationHours < 0 || durationHours > 24) {
                    showMessage('ERROR: DURATION HOURS MUST BE BETWEEN 0-24', 'error');
                    return;
                }

                if (durationMinutes < 0 || durationMinutes > 59) {
                    showMessage('ERROR: DURATION MINUTES MUST BE BETWEEN 0-59', 'error');
                    return;
                }

                if (durationHours === 0 && durationMinutes === 0) {
                    showMessage('ERROR: EXAMINATION DURATION CANNOT BE ZERO', 'error');
                    return;
                }

                if (editIndex === null && !isDateTimeFuture(examDate, deadline)) {
                    showMessage('ERROR: EXAMINATION DATE AND DEADLINE MUST BE IN THE FUTURE', 'error');
                    return;
                }

                try {
                    new URL(paperLink);
                    if (mcqLink.trim()) {
                        new URL(mcqLink);
                    }
                } catch {
                    showMessage('ERROR: PAPER LINK MUST BE A VALID URL. MCQ LINK MUST ALSO BE VALID IF PROVIDED', 'error');
                    return;
                }

                const examObj = {
                    subject: subject.toUpperCase(),
                    paperNo: paperNo.toUpperCase(),
                    durationHours,
                    durationMinutes,
                    examDate,
                    deadline,
                    paperLink,
                    mcqLink,
                    instructions,
                    accessCode: editIndex !== null ? exams[editIndex].accessCode : generateAccessCode(),
                    createdAt: editIndex !== null ? exams[editIndex].createdAt : new Date().toISOString()
                };

                if (editIndex !== null) {
                    exams[editIndex] = examObj;
                    editIndex = null;
                    document.getElementById('submitBtn').textContent = 'ADD EXAMINATION';
                    showMessage('Examination updated successfully!', 'success');
                    saveExams();
                } else {
                    const duplicate = exams.find(exam =>
                        exam.subject === examObj.subject &&
                        exam.paperNo === examObj.paperNo &&
                        exam.examDate === examObj.examDate
                    );

                    if (duplicate) {
                        showMessage('ERROR: DUPLICATE EXAMINATION DETECTED (SAME SUBJECT, PAPER & DATE)', 'error');
                        return;
                    }

                    exams.push(examObj);
                    saveExams();
                    showAccessCodeModal(examObj.accessCode);
                    showMessage(`New examination added successfully! Access Code: ${examObj.accessCode}`, 'success');
                }

                document.getElementById('examForm').reset();
                setMinimumDateTime();
            });
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const whatsappModal = document.getElementById('whatsappModal');
            const accessCodeModal = document.getElementById('accessCodeModal');

            if (event.target === whatsappModal) {
                closeWhatsappModal();
            } else if (event.target === accessCodeModal) {
                closeAccessCodeModal();
            }
        }

        // Prevent common shortcuts and add escape key functionality
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeWhatsappModal();
                closeAccessCodeModal();
            }
        });

        // Student Details View
        // Students Modal Functions
        function showStudentsModal(accessCode) {
            if (!isAuthenticated) return;
            console.log('Attempting to fetch exam_attempts.json for accessCode:', accessCode);
            try {
                // Fetch exam_attempts.json
                fetch('exam_attempts.json')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}, URL: ${response.url}`);
                        }
                        return response.json();
                    })
                    .then(attempts => {
                        console.log('Fetched attempts:', attempts);

                        const studentsTableBody = document.getElementById('studentsTableBody');
                        const clearSelectedBtn = document.getElementById('clearSelectedBtn');

                        // Clear existing table content
                        studentsTableBody.innerHTML = '';

                        // Filter attempts by accessCode
                        const filteredAttempts = attempts.filter(attempt => attempt.accessCode === accessCode);
                        console.log('Filtered attempts:', filteredAttempts);

                        // Check if there are any matching attempts
                        if (filteredAttempts.length === 0) {
                            studentsTableBody.innerHTML = '<tr><td colspan="5" style="border: 1px solid #dee2e6; padding: 12px; text-align: center;">No students found for this access code</td></tr>';
                            clearSelectedBtn.style.display = 'none';
                        } else {
                            // Populate table with filtered data
                            filteredAttempts.forEach(attempt => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                            <td style="border: 1px solid #dee2e6; padding: 12px;">
                                <input type="checkbox" class="select-record" 
                                    data-student-id="${attempt.studentId}" 
                                    data-access-code="${attempt.accessCode}" 
                                    data-timestamp="${attempt.timestamp}">
                            </td>
                            <td style="border: 1px solid #dee2e6; padding: 12px;">${attempt.studentName}</td>
                            <td style="border: 1px solid #dee2e6; padding: 12px;">${attempt.studentId}</td>
                            <td style="border: 1px solid #dee2e6; padding: 12px;">${attempt.accessCode}</td>
                            <td style="border: 1px solid #dee2e6; padding: 12px;">${new Date(attempt.timestamp).toLocaleString()}</td>
                        `;
                                studentsTableBody.appendChild(row);
                            });
                            clearSelectedBtn.style.display = 'block';
                        }

                        // Reset select all checkbox
                        document.getElementById('selectAll').checked = false;

                        // Show the modal
                        document.getElementById('studentsModal').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error loading exam attempts:', error.message);
                        showMessage(`Failed to load exam attempts: ${error.message}. Check the console for details.`, 'error');
                    });
            } catch (error) {
                console.error('Error:', error);
                showMessage(`Unexpected error: ${error.message}`, 'error');
            }
        }

        // Close Students Modal
        document.getElementById('closeModalBtn').addEventListener('click', function() {
            document.getElementById('studentsModal').style.display = 'none';
        });

        // Select All Checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.select-record');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Clear Selected Records
        document.getElementById('clearSelectedBtn').addEventListener('click', function() {
            if (!isAuthenticated) return;

            const selectedCheckboxes = document.querySelectorAll('.select-record:checked');
            if (selectedCheckboxes.length === 0) {
                showMessage('Please select at least one record to clear.', 'error');
                return;
            }

            const selectedRecords = Array.from(selectedCheckboxes).map(checkbox => ({
                studentId: checkbox.dataset.studentId,
                accessCode: checkbox.dataset.accessCode,
                timestamp: checkbox.dataset.timestamp
            }));

            if (confirm(`Are you sure you want to clear ${selectedRecords.length} selected record(s)? This action cannot be undone.`)) {
                fetch('clear_selected_attempts.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            records: selectedRecords
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            showMessage('Selected records cleared successfully!', 'success');
                            document.getElementById('studentsModal').style.display = 'none';
                        } else {
                            showMessage(`Failed to clear records: ${data.error || 'Unknown error'}`, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error clearing records:', error);
                        showMessage(`Failed to clear records: ${error.message}`, 'error');
                    });
            }
        });

        // Close modals when clicking outside (updated to include studentsModal)
        window.onclick = function(event) {
            const whatsappModal = document.getElementById('whatsappModal');
            const accessCodeModal = document.getElementById('accessCodeModal');
            const studentsModal = document.getElementById('studentsModal');

            if (event.target === whatsappModal) {
                closeWhatsappModal();
            } else if (event.target === accessCodeModal) {
                closeAccessCodeModal();
            } else if (event.target === studentsModal) {
                document.getElementById('studentsModal').style.display = 'none';
            }
        };
    </script>
    
    <!-- Exam Log View -->
    <script>
        // Global variables
        let al_sys_current_logs_data_2k24 = [];
        let al_sys_student_logs_mapping_2k24 = {};

        // Open main logs modal
        function al_sys_open_main_logs_modal_2k24() {
            document.getElementById('al_sys_main_logs_modal_2k24').style.display = 'block';
            al_sys_load_logs_data_ajax_2k24();
        }

        // Close main logs modal
        function al_sys_close_main_logs_modal_2k24() {
            document.getElementById('al_sys_main_logs_modal_2k24').style.display = 'none';
        }

        // Load logs data via AJAX
        function al_sys_load_logs_data_ajax_2k24() {
            const searchQuery = document.getElementById('al_sys_search_input_field_2k24').value;
            const startDate = document.getElementById('al_sys_start_date_input_2k24').value;
            const endDate = document.getElementById('al_sys_end_date_input_2k24').value;

            // Show loading
            document.getElementById('al_sys_loading_container_2k24').style.display = 'block';
            document.getElementById('al_sys_logs_table_2k24').style.opacity = '0.5';

            // Build query string
            const params = new URLSearchParams({
                al_sys_ajax_get_logs_data_2k24: 'true',
                al_sys_search_query_filter_2k24: searchQuery,
                al_sys_start_date_filter_2k24: startDate,
                al_sys_end_date_filter_2k24: endDate
            });

            fetch('?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        al_sys_current_logs_data_2k24 = data.logs;
                        al_sys_student_logs_mapping_2k24 = data.studentLogsData;
                        al_sys_render_logs_table_2k24(data.logs);
                    }
                })
                .catch(error => {
                    console.error('Error loading logs:', error);
                    al_sys_show_message_2k24('Error loading logs. Please try again.', 'error');
                })
                .finally(() => {
                    document.getElementById('al_sys_loading_container_2k24').style.display = 'none';
                    document.getElementById('al_sys_logs_table_2k24').style.opacity = '1';
                });
        }

        // Render logs table
        function al_sys_render_logs_table_2k24(logs) {
            const tbody = document.getElementById('al_sys_logs_table_body_2k24');
            tbody.innerHTML = '';

            if (logs.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="al_sys_no_logs_message_2k24">
                        No logs found matching your filters
                    </td>
                </tr>
            `;
                return;
            }

            logs.forEach((log, index) => {
                const row = document.createElement('tr');
                row.className = `al_sys_table_row_hover_2k24 ${index % 2 === 0 ? 'al_sys_table_row_even_2k24' : ''}`;

                let dataDisplay = '';
                if (typeof log.data === 'object') {
                    dataDisplay = JSON.stringify(log.data);
                } else {
                    dataDisplay = log.data || '';
                }

                row.innerHTML = `
                <td class="al_sys_table_data_cell_2k24">${al_sys_escape_html_2k24(log.timestamp)}</td>
                <td class="al_sys_table_data_cell_2k24">${al_sys_escape_html_2k24(log.student)}</td>
                <td class="al_sys_table_data_cell_2k24">${al_sys_escape_html_2k24(log.event)}</td>
                <td class="al_sys_table_data_cell_2k24">${al_sys_escape_html_2k24(dataDisplay)}</td>
                <td class="al_sys_table_data_cell_2k24">
                    <button 
                        class="al_sys_btn_view_student_log_2k24" 
                        onclick="al_sys_show_student_detail_modal_2k24('${al_sys_escape_html_2k24(log.student).replace(/'/g, "\\'")}')">
                        👤 View Student Log
                    </button>
                </td>
            `;
                tbody.appendChild(row);
            });
        }

        // Apply filters and reload
        function al_sys_apply_filters_and_reload_2k24() {
            al_sys_load_logs_data_ajax_2k24();
        }

        // Reset filters and reload
        function al_sys_reset_filters_and_reload_2k24() {
            document.getElementById('al_sys_search_input_field_2k24').value = '';
            document.getElementById('al_sys_start_date_input_2k24').value = '';
            document.getElementById('al_sys_end_date_input_2k24').value = '';
            al_sys_load_logs_data_ajax_2k24();
        }

        // Clear all logs with confirmation
        function al_sys_clear_all_logs_confirm_2k24() {
            if (!confirm('⚠️ Are you sure you want to clear all logs? This action cannot be undone!')) {
                return;
            }

            const formData = new FormData();
            formData.append('al_sys_clear_logs_action_ajax_2k24', 'true');

            fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        al_sys_show_message_2k24(data.message, 'success');
                        al_sys_load_logs_data_ajax_2k24();
                    }
                })
                .catch(error => {
                    console.error('Error clearing logs:', error);
                    al_sys_show_message_2k24('Error clearing logs. Please try again.', 'error');
                });
        }

        // Show student detail modal
        function al_sys_show_student_detail_modal_2k24(studentId) {
            const modal = document.getElementById('al_sys_student_detail_modal_2k24');
            const modalTitle = document.getElementById('al_sys_student_name_title_2k24');
            const modalBody = document.getElementById('al_sys_student_modal_body_content_2k24');

            modalTitle.textContent = 'Activity Log for: ' + studentId;

            const logs = al_sys_student_logs_mapping_2k24[studentId] || [];

            let content = '';
            if (logs.length > 0) {
                content = `
                <table class="al_sys_student_logs_table_2k24">
                    <thead class="al_sys_student_table_header_2k24">
                        <tr>
                            <th>Timestamp</th>
                            <th>Event</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

                logs.forEach(log => {
                    let dataDisplay = '';
                    if (typeof log.data === 'object') {
                        dataDisplay = JSON.stringify(log.data);
                    } else {
                        dataDisplay = log.data || '';
                    }

                    content += `
                    <tr>
                        <td>${al_sys_escape_html_2k24(log.timestamp)}</td>
                        <td>${al_sys_escape_html_2k24(log.event)}</td>
                        <td>${al_sys_escape_html_2k24(dataDisplay)}</td>
                    </tr>
                `;
                });

                content += `
                    </tbody>
                </table>
            `;
            } else {
                content = '<div class="al_sys_student_no_data_2k24">📭 No logs found for this student.</div>';
            }

            modalBody.innerHTML = content;
            modal.style.display = 'block';
        }

        // Close student detail modal
        function al_sys_close_student_detail_modal_2k24() {
            document.getElementById('al_sys_student_detail_modal_2k24').style.display = 'none';
        }

        // Show message notification
        function al_sys_show_message_2k24(message, type = 'success') {
            const messageBox = document.getElementById('al_sys_message_box_2k24');
            messageBox.className = type === 'success' ? 'al_sys_message_notification_2k24' : 'al_sys_message_notification_2k24 al_sys_message_error_2k24';
            messageBox.textContent = message;
            messageBox.style.display = 'block';

            setTimeout(() => {
                messageBox.style.display = 'none';
            }, 5000);
        }

        // Escape HTML to prevent XSS
        function al_sys_escape_html_2k24(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const mainModal = document.getElementById('al_sys_main_logs_modal_2k24');
            const studentModal = document.getElementById('al_sys_student_detail_modal_2k24');

            if (event.target === mainModal) {
                al_sys_close_main_logs_modal_2k24();
            }
            if (event.target === studentModal) {
                al_sys_close_student_detail_modal_2k24();
            }
        });

        // Close modals with ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const mainModal = document.getElementById('al_sys_main_logs_modal_2k24');
                const studentModal = document.getElementById('al_sys_student_detail_modal_2k24');

                if (mainModal.style.display === 'block') {
                    al_sys_close_main_logs_modal_2k24();
                }
                if (studentModal.style.display === 'block') {
                    al_sys_close_student_detail_modal_2k24();
                }
            }
        });

        // Allow Enter key to apply filters
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('al_sys_search_input_field_2k24');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(event) {
                    if (event.key === 'Enter') {
                        al_sys_apply_filters_and_reload_2k24();
                    }
                });
            }
        });
    </script>

    <!-- Enable Main Options -->
    <script>
        // Remove any restrictions on Ctrl + A, C, X, V
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                // Allow default behavior
                return true;
            }
        });

        // Re-enable right click, copy, paste
        document.oncopy = null;
        document.oncut = null;
        document.onpaste = null;
        document.onkeydown = null;
        document.oncontextmenu = null;

        // Allow text selection again
        document.body.style.userSelect = "auto";
        document.body.style.webkitUserSelect = "auto";
        document.body.style.msUserSelect = "auto";
    </script>


</body>

</html>