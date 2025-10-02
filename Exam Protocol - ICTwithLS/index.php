<?php
function logActivity($student, $event, $data = null)
{
    $logFile = __DIR__ . "/activity.log";

    $logEntry = [
        "action"    => "log_activity",
        "event"     => $event,
        "student"   => $student ?? "Unknown",
        "timestamp" => date("c"), // ISO 8601 format
        "data"      => $data
    ];

    // Append as newline-delimited JSON
    file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ICTwithLS Examination Management System</title>
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
            max-width: 450px;
            margin: 50px auto;
            padding: 40px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .container h2 {
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

        .form-group {
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
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ced4da;
            font-size: 14px;
            background: #ffffff;
            color: #212529;
            font-family: 'Courier New', monospace;
            font-weight: normal;
            text-transform: uppercase;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #dc3545;
            background: #fff;
        }

        button {
            width: 100%;
            background: #dc3545;
            color: white;
            padding: 15px;
            border: none;
            font-size: 14px;
            font-weight: bold;
            margin-top: 25px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: Arial, sans-serif;
        }

        button:hover {
            background: #c82333;
        }

        button:active {
            background: #bd2130;
        }

        .exam-area {
            display: none;
            height: 100vh;
            background: #ffffff;
            flex-direction: column;
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
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .student-label {
            font-weight: bold;
            color: #ffffff;
        }

        .timer-display {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            background: #ffffff;
            color: #212529;
            padding: 8px 15px;
            border: 2px solid #28a745;
            min-width: 110px;
            text-align: center;
        }

        .new-timer-warning {
            color: #000000 !important;
            background: #1eff00 !important;
            border-color: #137400 !important;
            animation: flash 1s infinite;
        }

        .timer-warning {
            color: #ffffff !important;
            background: #dc3545 !important;
            border-color: #dc3545 !important;
            animation: flash 1s infinite;
        }

        @keyframes flash {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .exam-paper-container {
            position: relative;
            flex: 1;
            background: white;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: white;
        }

        .final-message {
            display: none;
            text-align: center;
            font-size: 18px;
            padding: 60px 40px;
            background: #f8f9fa;
            color: #212529;
            height: 100vh;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-weight: normal;
            line-height: 1.6;
            border: 3px solid #dc3545;
        }

        .final-message.show {
            display: flex;
        }

        .exam-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #dc3545;
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
        }

        .instructions {
            background: #e9ecef;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 12px;
            line-height: 1.4;
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

        .error {
            background: #721c24 !important;
            border-color: #721c24 !important;
        }

        .success {
            background: #155724 !important;
            border-color: #155724 !important;
        }

        .loading {
            background: #495057 !important;
            opacity: 0.8;
            pointer-events: none;
        }

        input.invalid {
            border-color: #dc3545;
            background: #f8d7da;
        }

        input.valid {
            border-color: #28a745;
            background: #d4edda;
        }

        #fullscreenOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.98);
            z-index: 1000;
            flex-direction: column;
        }

        .overlay-header {
            background: #343a40;
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: normal;
            font-size: 13px;
            border-bottom: 1px solid #dee2e6;
        }

        .overlay-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 50px 40px;
            font-family: Arial, sans-serif;
        }

        .overlay-content h2 {
            color: #dc3545;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .overlay-content p {
            color: #333;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .overlay-content button {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 5px;
            width: auto;
        }

        .overlay-content button:hover {
            background: #218838;
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
        }

        .exam-area * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Annotation Toolbar Styles */
        .annotation-bar {
            background: #343a40;
            padding: 10px;
            display: none;
            justify-content: center;
            gap: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .annotation-btn {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border: none;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .annotation-btn:hover {
            background: #c82333;
        }

        .annotation-btn.active {
            background: #28a745;
        }

        .annotation-btn:active {
            background: #bd2130;
        }

        canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        canvas.active {
            pointer-events: auto;
        }

        @media (max-width: 768px) {
            body {
                font-size: 13px;
            }

            .container {
                margin: 10px;
                padding: 20px 15px;
                max-width: none;
            }

            .container h2 {
                font-size: 16px;
                margin-bottom: 20px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            input[type="text"],
            input[type="password"] {
                padding: 14px 12px;
                font-size: 16px;
                -webkit-appearance: none;
                border-radius: 0;
            }

            button {
                padding: 16px;
                font-size: 14px;
                -webkit-appearance: none;
                border-radius: 0;
            }

            .info-bar,
            .overlay-header {
                flex-direction: column;
                gap: 8px;
                text-align: center;
                padding: 12px;
                font-size: 12px;
            }

            .timer-display {
                font-size: 16px;
                min-width: 120px;
                padding: 10px 15px;
            }

            header {
                font-size: 11px;
                padding: 12px 15px;
            }

            .header-title {
                font-size: 13px;
            }

            .header-subtitle {
                font-size: 10px;
            }

            .instructions {
                padding: 12px;
                font-size: 11px;
            }

            .warning-text {
                padding: 12px;
                font-size: 11px;
            }

            .security-notice {
                padding: 8px;
                font-size: 10px;
            }

            .final-message {
                font-size: 16px;
                padding: 40px 20px;
            }

            .exam-icon {
                font-size: 36px;
            }

            .overlay-content {
                padding: 30px 20px;
            }

            .overlay-content h2 {
                font-size: 24px;
            }

            .overlay-content p {
                font-size: 14px;
            }

            .annotation-bar {
                flex-wrap: wrap;
                padding: 8px;
            }

            .annotation-btn {
                padding: 6px 12px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin: 5px;
                padding: 15px 10px;
            }

            .info-bar,
            .overlay-header {
                padding: 10px;
            }

            .timer-display {
                font-size: 14px;
                min-width: 100px;
                padding: 8px 12px;
            }

            header {
                padding: 10px;
            }

            .header-title {
                font-size: 12px;
            }

            .final-message {
                font-size: 14px;
                padding: 30px 15px;
            }
        }

        @media (min-width: 481px) and (max-width: 768px) {
            .container {
                max-width: 500px;
                margin: 20px auto;
                padding: 30px 25px;
            }

            .info-bar,
            .overlay-header {
                flex-direction: row;
                justify-content: space-between;
            }

            .timer-display {
                font-size: 15px;
                min-width: 110px;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .container {
                max-width: 550px;
                margin: 40px auto;
                padding: 35px 30px;
            }

            body {
                font-size: 14px;
            }

            .container h2 {
                font-size: 18px;
            }

            input[type="text"],
            input[type="password"] {
                padding: 13px 15px;
                font-size: 14px;
            }

            .timer-display {
                font-size: 16px;
            }
        }

        @media (min-width: 1025px) and (max-width: 1366px) {
            .container {
                max-width: 500px;
            }

            body {
                font-size: 14px;
            }
        }

        @media (min-width: 1367px) {
            .container {
                max-width: 520px;
            }

            body {
                font-size: 15px;
            }

            .container h2 {
                font-size: 19px;
            }
        }

        @media (orientation: landscape) and (max-height: 600px) {
            .container {
                margin: 10px auto;
                padding: 20px;
            }

            .container h2 {
                margin-bottom: 15px;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .instructions {
                padding: 10px;
                font-size: 11px;
            }

            .warning-text {
                padding: 10px;
            }
        }

        @media (-webkit-min-device-pixel-ratio: 2),
        (min-resolution: 192dpi) {

            input[type="text"],
            input[type="password"] {
                border-width: 1px;
            }

            .timer-display {
                border-width: 1px;
            }
        }

        @supports (-webkit-touch-callout: none) {

            input[type="text"],
            input[type="password"] {
                -webkit-appearance: none;
                -webkit-border-radius: 0;
            }

            button {
                -webkit-appearance: none;
                -webkit-border-radius: 0;
            }
        }

        @supports (-webkit-overflow-scrolling: touch) {

            input[type="text"],
            input[type="password"] {
                -webkit-appearance: none;
                border-radius: 0;
                background-clip: padding-box;
            }

            button {
                -webkit-appearance: none;
                border-radius: 0;
            }

            body {
                -webkit-text-size-adjust: 100%;
            }
        }

        @media print {

            .info-bar,
            header,
            .instructions,
            .warning-text,
            .annotation-bar {
                display: none !important;
            }

            .exam-area {
                height: auto !important;
            }

            * {
                background: white !important;
                color: black !important;
            }
        }

        footer {
            background: #111827;
            color: #f3f4f6;
            text-align: center;
            padding: 1rem;
            font-size: 0.9rem;
        }

        #mcq-submit:hover {
            background-color: #bb2d3b;
        }

        .alert {
            padding: 10px;
            background-color: #f44336;
            color: white;
            opacity: 1;
            transition: opacity 0.6s;
            border-radius: 8px;
            font-size: 16px;
        }

        .closebtn {
            margin-left: 15px;
            color: white;
            font-weight: bold;
            float: right;
            font-size: 22px;
            line-height: 20px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .closebtn:hover {
            color: black;
        }
    </style>
</head>

<body oncontextmenu="return false" onselectstart="return false"
    ondragstart="return false">
    <header>
        <div class="header-title">ICTwithLS EXAMINATION MANAGEMENT
            SYSTEM</div>
        <div class="header-subtitle">
            <div id="paperdetails">Good Luck For Your Exam!</div>
        </div>
    </header>

    <div class="container" id="setup">
        <div class="security-notice">
            ⚠ THIS IS A SECURE EXAMINATION ENVIRONMENT ⚠
        </div>
        <br>
        <center><img src="Photos/Long_text_logo.jpg" alt="logo"
                width="250" /></center>
        <br>
        <h2>Student Authentication</h2>

        <div class="warning-text">
            ⚠ WARNING: This is a monitored examination session
        </div>

        <div class="instructions">
            <h3>Instructions:</h3>
            <ul>
                <li>No books or notes can refer during the exam.</li>
                <li>Ensure stable internet connection</li>
                <li>Close all other applications</li>
                <li>Do not refresh or navigate away</li>
                <li>Contact supervisor for technical issues</li>
                <li>Only one attempt Allowed</li>
            </ul>
        </div>

        <div class="form-group">
            <label for="studentName">Student Name</label>
            <input type="text" id="studentName"
                placeholder="ENTER FULL NAME AS PER RECORDS" required>
        </div>

        <div class="form-group">
            <label for="studentId">Student ID Number (Start with "OLT" or
                "OLR")</label>
            <input type="text" id="studentId" placeholder="ENTER STUDENT ID"
                required>
        </div>

        <div class="form-group">
            <label for="examPassword">Examination Access Code (This exam is
                locked. Your invigilator will provide the start
                password).</label>
            <input type="password" id="examPassword"
                placeholder="ENTER 6-DIGIT CODE" maxlength="6" required>
        </div>

        <button onclick="startExam()" id="startExamBtn">START THE
            EXAMINATION</button>
    </div>

    <div class="exam-area" id="examArea">
        <div class="security-notice">
            🔒 EXAMINATION IN PROGRESS - SESSION MONITORED 🔒
        </div>
        <div class="info-bar" id="infoBar">
            <div class="student-info">
                <span class="status-indicator"></span>
                <span class="student-label">CANDIDATE:</span>
                <span id="studentInfo"></span>
                <span id="mcq-submit"
                    style="display: inline-block; margin-left: 60px; font-weight: bold; text-align: center; padding: 8px 16px; background-color: #dc3545; color: #ffffff; border-radius: 6px; cursor: pointer; transition: background-color 0.3s ease;">
                    MCQ Submit
                </span>
                <span id="annotate-btn"
                    style="display: inline-block; margin-left: 20px; font-weight: bold; text-align: center; padding: 8px 16px; background-color: #dc3545; color: #ffffff; border-radius: 6px; cursor: pointer; transition: background-color 0.3s ease;">
                    Annotate
                </span>
            </div>
            <div>
                <span id="reloadPaperBtn"
                    style="display: inline-block; margin-right: 40px; font-weight: bold; text-align: center; padding: 8px 16px; background-color: #dc3545; color: #ffffff; border-radius: 6px; cursor: pointer; transition: background-color 0.3s ease;">
                    🔄 Reload Paper
                </span>
                <span style="margin-right: 15px; font-weight: bold;">TIME
                    REMAINING:</span>
                <span id="timerDisplay" class="timer-display"></span>
            </div>
        </div>
        <div class="annotation-bar" id="annotationBar">
            <button class="annotation-btn" id="penBlue"
                onclick="selectTool('pen', '#0000FF')">Blue Pen</button>
            <button class="annotation-btn" id="penBlack"
                onclick="selectTool('pen', '#000000')">Black Pen</button>
            <button class="annotation-btn" id="eraser"
                onclick="selectTool('eraser')">Eraser</button>
            <button class="annotation-btn" id="clearAll"
                onclick="clearCanvas()">Clear All</button>
        </div>
        <div class="alert" id="reloadAlert">
            <span class="closebtn">&times;</span>
            ⚠️ If the paper pages didn’t load, you can refresh the paper
            using the <b>Reload Paper</b> button<br>
            💫 You can Annotate with use <b>Annotate</b> button.
        </div>
        <div class="exam-paper-container">
            <iframe id="examPaper" src
                sandbox="allow-scripts allow-same-origin"></iframe>
            <canvas id="annotationCanvas"></canvas>
        </div>
        <div id="fullscreenOverlay">
            <div class="security-notice">
                🔒 EXAMINATION IN PROGRESS - SESSION MONITORED 🔒
            </div>
            <div class="overlay-header">
                <div class="student-info">
                    <span class="status-indicator"></span>
                    <span class="student-label">CANDIDATE:</span>
                    <span id="overlayStudentInfo"></span>
                </div>
                <div>
                    <span
                        style="margin-right: 15px; font-weight: bold;">TIME
                        REMAINING:</span>
                    <span id="overlayTimerDisplay"
                        class="timer-display"></span>
                </div>
            </div>
            <div class="overlay-content">
                <h2>⚠ Fullscreen Mode Required</h2>
                <p>Please re-enter fullscreen to continue your
                    examination.</p>
                <p>Your timer is still running and your progress is being
                    monitored.</p>
                <button onclick="reEnterFullscreen()">Re-Enter
                    Fullscreen</button>
            </div>
        </div>
    </div>

    <div class="final-message" id="finalMessage">
        <div class="exam-icon">⏰</div>
        <div id="finalMessageText"></div>
        <div style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            EXAMINATION SESSION TERMINATED<br>
            PLEASE REMAIN SEATED UNTIL DISMISSED
        </div>
    </div>

    <div id="popupOverlay"
        style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:2000; justify-content:center; align-items:center;">
        <div
            style="background:#fff; width:90%; max-width:800px; height:90%; position:relative; border-radius:8px; overflow:hidden;">
            <button onclick="closePopup()"
                style="position:absolute; top:10px; right:10px; background:#dc3545; color:white; border:none; padding:6px 12px; cursor:pointer; font-weight:bold;">✖
                Close</button>
            <iframe id="popupIframe" src
                style="width:100%; height:100%; border:none;"></iframe>
        </div>
    </div>

    <footer>
        <p class="copyright">© 2025 L.S.COMPUTER TECHNOLOGH. All Rights
            Reserved.</p>
    </footer>

    <script>
        let countdownInterval;
        let currentExam = null;
        let examData = {};
        const API_URL = "save_exams.php";
        let focusLossCount = 0;
        const MAX_FOCUS_LOSS = 10;
        let idleTimeout = null;
        const IDLE_LIMIT = 2400000; // 40 minutes in ms
        let fontSize = 14; // Default font size in px

        // Annotation variables
        let isAnnotating = false;
        let currentTool = 'pen';
        let currentColor = '#0000FF'; // Default to blue pen
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        let canvas, ctx;

        // Initialize annotation canvas
        function initAnnotationCanvas() {
            canvas = document.getElementById('annotationCanvas');
            if (!canvas) {
                console.error('Annotation canvas not found');
                return;
            }
            ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error('Failed to get canvas context');
                return;
            }
            resizeCanvas();

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
            canvas.addEventListener('touchstart', handleTouchStart);
            canvas.addEventListener('touchmove', handleTouchMove);
            canvas.addEventListener('touchend', stopDrawing);

            window.addEventListener('resize', resizeCanvas);

            const annotateBtn = document.getElementById('annotate-btn');
            if (annotateBtn) {
                annotateBtn.addEventListener('click', toggleAnnotationBar);
            } else {
                console.error('Annotate button not found');
            }
            selectTool('pen', '#0000FF'); // Set default tool to blue pen
        }

        // Resize canvas to match iframe
        function resizeCanvas() {
            if (!canvas) return;
            const container = document.querySelector('.exam-paper-container');
            if (!container) {
                console.error('Exam paper container not found');
                return;
            }
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        // Toggle annotation toolbar
        function toggleAnnotationBar() {
            const annotationBar = document.getElementById('annotationBar');
            const annotateBtn = document.getElementById('annotate-btn');
            if (!annotationBar || !annotateBtn) {
                console.error('Annotation bar or button not found');
                return;
            }
            isAnnotating = !isAnnotating;
            annotationBar.style.display = isAnnotating ? 'flex' : 'none';
            annotateBtn.style.backgroundColor = isAnnotating ? '#28a745' : '#dc3545';
            canvas.classList.toggle('active', isAnnotating);
            resizeCanvas();
            logActivity('Annotation bar ' + (isAnnotating ? 'opened' : 'closed'));
        }

        // Select drawing tool
        function selectTool(tool, color = null) {
            currentTool = tool;
            if (tool === 'pen') {
                currentColor = color;
                ctx.globalCompositeOperation = 'source-over';
                ctx.strokeStyle = color;
                ctx.lineWidth = 2;
            } else if (tool === 'eraser') {
                ctx.globalCompositeOperation = 'destination-out';
                ctx.lineWidth = 10;
            }

            // Update button states
            const penBlue = document.getElementById('penBlue');
            const penBlack = document.getElementById('penBlack');
            const eraser = document.getElementById('eraser');
            if (penBlue && penBlack && eraser) {
                penBlue.classList.toggle('active', tool === 'pen' && color === '#0000FF');
                penBlack.classList.toggle('active', tool === 'pen' && color === '#000000');
                eraser.classList.toggle('active', tool === 'eraser');
            }
            logActivity('Tool selected: ' + tool + (color ? ' (' + color + ')' : ''));
        }

        // Start drawing
        function startDrawing(e) {
            if (!isAnnotating) return;
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            lastX = e.clientX - rect.left;
            lastY = e.clientY - rect.top;
            logActivity('Started drawing');
        }

        // Handle touch start
        function handleTouchStart(e) {
            if (!isAnnotating) return;
            e.preventDefault();
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            lastX = touch.clientX - rect.left;
            lastY = touch.clientY - rect.top;
            logActivity('Started touch drawing');
        }

        // Draw on canvas
        function draw(e) {
            if (!isDrawing || !isAnnotating) return;
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();
            lastX = x;
            lastY = y;
        }

        // Handle touch move
        function handleTouchMove(e) {
            if (!isDrawing || !isAnnotating) return;
            e.preventDefault();
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            const x = touch.clientX - rect.left;
            const y = touch.clientY - rect.top;

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();
            lastX = x;
            lastY = y;
        }

        // Stop drawing
        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                logActivity('Stopped drawing');
            }
        }

        // Clear canvas
        function clearCanvas() {
            if (!ctx) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            logActivity('Annotations cleared');
        }

        // Auto-hide alert after 30 seconds
        function autoHideAlert() {
            const alert = document.getElementById('reloadAlert');
            if (alert) {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 600);
                }, 15000); //15s
            }
        }

        // Load exams from JSON file
        function loadAndInitializeExam(password) {
            return fetch(API_URL, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok: ' + res.statusText);
                    return res.json();
                })
                .then(data => {
                    const exams = Array.isArray(data) ? data : [];
                    const exam = exams.find(exam => exam.accessCode === password);
                    if (!exam) {
                        console.error("No exam found for access code:", password);
                        return null;
                    }

                    const [DEADLINE_HOUR, DEADLINE_MINUTE] = exam.deadline.split(':').map(Number);
                    if (isNaN(DEADLINE_HOUR) || isNaN(DEADLINE_MINUTE)) {
                        console.error("Invalid deadline format:", exam.deadline);
                        return null;
                    }

                    if (!exam.examDate || isNaN(new Date(exam.examDate).getTime())) {
                        console.error("Invalid exam date:", exam.examDate);
                        return null;
                    }

                    return {
                        correctPassword: [exam.accessCode],
                        DEADLINE_HOUR,
                        DEADLINE_MINUTE,
                        DEADLINE_DATE: exam.examDate,
                        paperlink: exam.paperLink,
                        Submitanswers: exam.mcqLink || "",
                        Subject: exam.subject,
                        Paperno: exam.paperNo,
                        DurationHours: exam.durationHours,
                        DurationMinutes: exam.durationMinutes,
                        examDuration: exam.durationHours * 3600 + exam.durationMinutes * 60
                    };
                })
                .catch(err => {
                    console.error("Error loading exams:", err);
                    alert('Error loading exams: ' + err.message);
                    return null;
                });
        }

        // Initialize security measures
        function initializeSecurity() {
            document.addEventListener('keydown', handleSecurityKeydown);
            document.addEventListener('visibilitychange', handleVisibilityChange);
            detectUnauthorizedScripts();
            startIdleDetection();
        }

        // Start idle detection
        function startIdleDetection() {
            resetIdleTimer();
            document.addEventListener('mousemove', resetIdleTimer);
            document.addEventListener('keydown', resetIdleTimer);
            document.addEventListener('click', resetIdleTimer);
            document.addEventListener('touchstart', resetIdleTimer);
        }

        function handleVisibilityChange() {
            if (document.hidden && document.getElementById("examArea").style.display !== "none") {
                focusLossCount++;
                logActivity('Tab/Window switch detected', {
                    focusLossCount
                });
                if (focusLossCount >= MAX_FOCUS_LOSS) {
                    endExam(localStorage.getItem("studentName") || "Student", "TOO MANY TAB SWITCHES");
                } else {
                    alert(`Warning: Tab/Window switch detected. ${MAX_FOCUS_LOSS - focusLossCount} attempts remaining before termination.`);
                }
            }
        }

        // Reset idle timer
        function resetIdleTimer() {
            if (document.getElementById("examArea").style.display !== "none") {
                clearTimeout(idleTimeout);
                idleTimeout = setTimeout(() => {
                    logActivity('Idle timeout detected');
                    alert("Inactivity detected. Please stay active or your session may be terminated.");
                }, IDLE_LIMIT);
            }
        }

        // Log activity to server
        function logActivity(event, data = null) {
            if (document.getElementById("examArea").style.display !== "none") {
                fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'log_activity',
                        event,
                        student: localStorage.getItem("studentName") || "Unknown",
                        timestamp: new Date().toISOString(),
                        data
                    })
                }).catch(err => console.error('Activity logging failed:', err));
            }
        }

        // Log exam attempt to server with remaining time
        function logExamAttempt(studentName, studentId, accessCode, remainingTime) {
            const h = String(Math.floor(remainingTime / 3600)).padStart(2, '0');
            const m = String(Math.floor((remainingTime % 3600) / 60)).padStart(2, '0');
            const s = String(remainingTime % 60).padStart(2, '0');
            const formattedTime = `${h}:${m}:${s}`;
            fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'log_attempt',
                        studentName,
                        studentId,
                        accessCode,
                        timestamp: new Date().toISOString(),
                        remainingTime: formattedTime
                    })
                })
                .then(res => {
                    if (!res.ok) throw new Error('Failed to log exam attempt: ' + res.statusText);
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error("Error logging exam attempt:", data.error);
                    }
                })
                .catch(err => console.error('Exam attempt logging failed:', err));
        }

        // Check if student has already attempted the exam
        function checkExamAttempt(studentName, studentId, accessCode) {
            return fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'check_attempt',
                        studentName,
                        studentId,
                        accessCode
                    })
                })
                .then(res => {
                    if (!res.ok) throw new Error('Failed to check exam attempt: ' + res.statusText);
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error("Error checking exam attempt:", data.error);
                        return {
                            error: data.error
                        };
                    }
                    return {
                        hasAttempted: data.hasAttempted
                    };
                })
                .catch(err => {
                    console.error("Error checking exam attempt:", err);
                    alert('Error verifying exam attempt: ' + err.message);
                    return {
                        error: err.message
                    };
                });
        }

        // Check the corresponding Paper with Access Code
        function getPaperLink() {
            const password = document.getElementById("examPassword").value.trim();
            if (!currentExam) {
                console.error("No exam loaded");
                return null;
            }
            const index = currentExam.correctPassword.indexOf(password);
            if (index !== -1) {
                return currentExam.paperlink;
            } else {
                return null;
            }
        }

        // Auto-capitalize input text and initialize security
        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="text"]');
            textInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    const cursorPosition = e.target.selectionStart;
                    e.target.value = e.target.value.toUpperCase();
                    e.target.setSelectionRange(cursorPosition, cursorPosition);
                });
            });

            const mcqSubmitBtn = document.getElementById("mcq-submit");
            if (mcqSubmitBtn) {
                mcqSubmitBtn.addEventListener("click", function() {
                    if (document.getElementById("examArea").style.display !== "none" && currentExam && currentExam.Submitanswers) {
                        showPopup(currentExam.Submitanswers);
                    } else {
                        console.warn("No MCQ link available or exam not active.");
                        alert("No MCQ answer sheet available or exam not active. Please ensure the exam is in progress.");
                    }
                });
            }

            const closeButtons = document.querySelectorAll(".closebtn");
            closeButtons.forEach(btn => {
                btn.onclick = function() {
                    const div = this.parentElement;
                    div.style.opacity = "0";
                    setTimeout(() => {
                        div.style.display = "none";
                    }, 600);
                };
            });

            initializeSecurity();
            initAnnotationCanvas();
            checkSessionRecovery();
        });

        function checkSessionRecovery() {
            const storedName = localStorage.getItem("studentName") || "";
            const storedStudentId = localStorage.getItem("studentId") || "";
            const storedPassword = localStorage.getItem("examPassword") || "";
            const storedPaper = localStorage.getItem("examPaperNo") || "";
            const currentName = document.getElementById("studentName")?.value.trim().toUpperCase() || "";
            const currentStudentId = document.getElementById("studentId")?.value.trim().toUpperCase() || "";
            const currentPassword = document.getElementById("examPassword")?.value.trim() || "";

            if (!currentPassword || (!currentName && !currentStudentId)) {
                return;
            }

            checkExamAttempt(currentName, currentStudentId, currentPassword).then(response => {
                if (response.error) {
                    alert("Error checking exam attempt: " + response.error);
                    return;
                }
                if (response.hasAttempted) {
                    alert("This exam has already been attempted by " + (currentName || "N/A") + " (ID: " + (currentStudentId || "N/A") + "). Only one attempt is allowed.");
                    localStorage.removeItem("examEndTime");
                    localStorage.removeItem("studentName");
                    localStorage.removeItem("studentId");
                    localStorage.removeItem("examPassword");
                    localStorage.removeItem("examPaperNo");
                    return;
                }

                if (storedName && storedStudentId && storedPassword && storedPaper) {
                    loadAndInitializeExam(storedPassword).then(exam => {
                        if (exam && exam.Paperno === storedPaper) {
                            alert("ERROR: A previous session exists for " + storedName + " (ID: " + storedStudentId + "). Only one attempt is allowed.");
                            localStorage.removeItem("examEndTime");
                            localStorage.removeItem("studentName");
                            localStorage.removeItem("studentId");
                            localStorage.removeItem("examPassword");
                            localStorage.removeItem("examPaperNo");
                        } else {
                            alert("No valid session found. You may start the exam as no prior attempts were recorded.");
                        }
                    }).catch(err => {
                        alert("Error loading session: " + err.message);
                        localStorage.removeItem("examEndTime");
                        localStorage.removeItem("studentName");
                        localStorage.removeItem("studentId");
                        localStorage.removeItem("examPassword");
                        localStorage.removeItem("examPaperNo");
                    });
                } else {
                    alert("No previous session found. You may start the exam as no prior attempts were recorded.");
                }
            }).catch(err => {
                alert("Error verifying exam attempt: " + err.message);
            });
        }

        // Calculate deadline timestamp
        function getDeadlineTimestamp() {
            if (!currentExam) {
                console.error("No exam loaded");
                return Date.now();
            }

            const deadline = new Date(currentExam.DEADLINE_DATE);
            deadline.setHours(currentExam.DEADLINE_HOUR, currentExam.DEADLINE_MINUTE, 0, 0);
            return deadline.getTime();
        }

        // Get time remaining until deadline
        function getTimeUntilDeadline() {
            const currentTime = Date.now();
            const deadlineTime = getDeadlineTimestamp();
            const remaining = Math.floor((deadlineTime - currentTime) / 1000);

            if (remaining <= 0) {
                return {
                    total_seconds: 0,
                    hours: 0,
                    minutes: 0,
                    seconds: 0,
                    formatted: "00:00:00",
                    is_expired: true,
                    is_warning: false,
                    deadline_passed: true
                };
            }

            const hours = Math.floor(remaining / 3600);
            const minutes = Math.floor((remaining % 3600) / 60);
            const seconds = remaining % 60;

            const formatted = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            return {
                total_seconds: remaining,
                hours: hours,
                minutes: minutes,
                seconds: seconds,
                formatted: formatted,
                is_expired: false,
                is_warning: remaining <= 600,
                deadline_passed: false
            };
        }

        // Get readable deadline info
        function getDeadlineInfo() {
            if (!currentExam) {
                console.error("No exam loaded");
                return {
                    deadline_time: "N/A",
                    deadline_24h: "N/A",
                    time_remaining: {
                        formatted: "00:00:00",
                        is_expired: true
                    },
                    status: "NO EXAM"
                };
            }
            const deadlineTime = new Date(getDeadlineTimestamp());
            const timeInfo = getTimeUntilDeadline();

            return {
                deadline_time: deadlineTime.toLocaleString(),
                deadline_24h: `${String(currentExam.DEADLINE_HOUR).padStart(2, '0')}:${String(currentExam.DEADLINE_MINUTE).padStart(2, '0')}`,
                time_remaining: timeInfo,
                status: timeInfo.is_expired ? "EXPIRED" : timeInfo.is_warning ? "WARNING" : "ACTIVE"
            };
        }

        function startExam() {
            const name = document.getElementById("studentName")?.value.trim().toUpperCase() || "";
            const studentId = document.getElementById("studentId")?.value.trim().toUpperCase() || "";
            const password = document.getElementById("examPassword").value.trim();
            const button = document.getElementById('startExamBtn');

            document.querySelectorAll('input').forEach(input => input.classList.remove('invalid', 'valid'));

            if (!password || (!name && !studentId)) {
                if (!name) document.getElementById("studentName")?.classList.add('invalid');
                if (!studentId) document.getElementById("studentId")?.classList.add('invalid');
                if (!password) document.getElementById("examPassword").classList.add('invalid');
                alert("ERROR: Exam password and either student name or student ID are mandatory.");
                return;
            }

            button.classList.add('loading');
            button.textContent = 'AUTHENTICATING...';

            loadAndInitializeExam(password).then(exam => {
                if (!exam) {
                    document.getElementById("examPassword").classList.add('invalid');
                    button.classList.remove('loading');
                    button.classList.add('error');
                    button.textContent = 'AUTHENTICATION FAILED';
                    setTimeout(() => {
                        button.classList.remove('error');
                        button.textContent = 'START THE EXAMINATION';
                    }, 3000);
                    return;
                }

                currentExam = exam;

                checkExamAttempt(name, studentId, password).then(response => {
                    if (response.error) {
                        alert("Error checking exam attempt: " + response.error);
                        button.classList.remove('loading');
                        button.classList.add('error');
                        button.textContent = 'AUTHENTICATION FAILED';
                        setTimeout(() => {
                            button.classList.remove('error');
                            button.textContent = 'START THE EXAMINATION';
                        }, 3000);
                        return;
                    }
                    if (response.hasAttempted) {
                        alert("ERROR: You have already attempted this exam. Only one attempt is allowed.");
                        button.classList.remove('loading');
                        button.classList.add('error');
                        button.textContent = 'EXAM ALREADY ATTEMPTED';
                        setTimeout(() => {
                            button.classList.remove('error');
                            button.textContent = 'START THE EXAMINATION';
                        }, 3000);
                        localStorage.removeItem("examEndTime");
                        localStorage.removeItem("studentName");
                        localStorage.removeItem("studentId");
                        localStorage.removeItem("examPassword");
                        localStorage.removeItem("examPaperNo");
                        return;
                    }

                    const storedName = localStorage.getItem("studentName") || "";
                    const storedStudentId = localStorage.getItem("studentId") || "";
                    const storedPassword = localStorage.getItem("examPassword") || "";
                    const storedPaper = localStorage.getItem("examPaperNo") || "";

                    if (storedName === name && storedStudentId === studentId && storedPassword === password && storedPaper === exam.Paperno) {
                        alert("ERROR: A previous session exists for this student and exam. Only one attempt is allowed.");
                        button.classList.remove('loading');
                        button.classList.add('error');
                        button.textContent = 'SESSION ALREADY EXISTS';
                        setTimeout(() => {
                            button.classList.remove('error');
                            button.textContent = 'START THE EXAMINATION';
                        }, 3000);
                        localStorage.removeItem("examEndTime");
                        localStorage.removeItem("studentName");
                        localStorage.removeItem("studentId");
                        localStorage.removeItem("examPassword");
                        localStorage.removeItem("examPaperNo");
                        return;
                    }

                    document.getElementById("examPassword").classList.add('valid');
                    button.classList.remove('loading');
                    button.classList.add('success');
                    button.textContent = 'ACCESS GRANTED - LOADING...';
                    document.getElementById('paperdetails').innerHTML = `Subject: ${currentExam.Subject} | Paper No: ${currentExam.Paperno} | Duration: ${currentExam.DurationHours}H ${currentExam.DurationMinutes}M | End Date: ${currentExam.DEADLINE_DATE}`;

                    const deadlineTime = getDeadlineTimestamp();
                    const maxExamTime = Date.now() + currentExam.examDuration * 1000;
                    const endTime = Math.min(deadlineTime, maxExamTime);

                    localStorage.setItem("examEndTime", endTime);
                    localStorage.setItem("studentName", name);
                    localStorage.setItem("studentId", studentId);
                    localStorage.setItem("examPassword", password);
                    localStorage.setItem("examPaperNo", currentExam.Paperno);

                    DeadlineCheck() //check the deadline

                    const remainingTime = Math.floor((endTime - Date.now()) / 1000);
                    if (remainingTime > 0) {
                        logExamAttempt(name, studentId, password, remainingTime);
                    }

                    setTimeout(() => {
                        document.getElementById("setup").style.display = "none";
                        document.getElementById("examArea").style.display = "flex";

                        const studentInfoText = `${name || "N/A"} (ID: ${studentId || "N/A"})`;
                        document.getElementById("studentInfo").textContent = studentInfoText;
                        document.getElementById("overlayStudentInfo").textContent = studentInfoText;

                        const iframe = document.getElementById("examPaper");
                        iframe.src = getPaperLink();

                        reEnterFullscreen();
                        autoHideAlert();

                        examData = {
                            endTime: endTime,
                            deadlineTime: deadlineTime,
                            studentName: name,
                            studentId: studentId,
                            startTime: Date.now()
                        };

                        startCountdown();
                    }, 2000);
                }).catch(err => {
                    alert("Error verifying exam attempt: " + err.message);
                    button.classList.remove('loading');
                    button.classList.add('error');
                    button.textContent = 'AUTHENTICATION FAILED';
                    setTimeout(() => {
                        button.classList.remove('error');
                        button.textContent = 'START THE EXAMINATION';
                    }, 3000);
                });
            }).catch(err => {
                alert("Error loading exam: " + err.message);
                button.classList.remove('loading');
                button.classList.add('error');
                button.textContent = 'AUTHENTICATION FAILED';
                setTimeout(() => {
                    button.classList.remove('error');
                    button.textContent = 'START THE EXAMINATION';
                }, 3000);
            });
        }

        function startCountdown() {
            const storedEndTime = localStorage.getItem("examEndTime");
            const storedName = localStorage.getItem("studentName") || "Student";
            if (!storedEndTime || !currentExam) {
                endExam(storedName, "DEADLINE REACHED");
                return;
            }
            const savedEndTime = parseInt(storedEndTime);

            function updateTimer() {
                DeadlineCheck(); // Check deadline on each tick
                const now = Date.now();
                const deadlineTime = getDeadlineTimestamp();
                const effectiveEndTime = Math.min(savedEndTime, deadlineTime);
                let remaining = Math.floor((effectiveEndTime - now) / 1000);

                if (remaining <= 0) {
                    clearInterval(countdownInterval);
                    localStorage.removeItem("examEndTime");
                    localStorage.removeItem("studentName");
                    localStorage.removeItem("studentId");
                    localStorage.removeItem("examPassword");
                    localStorage.removeItem("examPaperNo");
                    endExam(storedName, "TIME EXPIRED");
                    return;
                }

                updateTimerDisplay(remaining);
                if (remaining <= 900) { // 15 minutes warning
                    document.getElementById("timerDisplay").classList.add("new-timer-warning");
                    document.getElementById("overlayTimerDisplay").classList.add("new-timer-warning");
                }
                if (remaining <= 600) { // 10 minutes warning
                    document.getElementById("timerDisplay").classList.add("timer-warning");
                    document.getElementById("overlayTimerDisplay").classList.add("timer-warning");
                }
            }

            updateTimer();
            countdownInterval = setInterval(updateTimer, 1000);
        }

        function updateTimerDisplay(seconds) {
            const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            const timeString = `${h}:${m}:${s}`;

            document.getElementById("timerDisplay").textContent = timeString;
            document.getElementById("overlayTimerDisplay").textContent = timeString;
        }

        function endExam(name, reason = "TIME EXPIRED") {
            clearInterval(countdownInterval);
            exitFullScreen();
            localStorage.removeItem("examEndTime");
            localStorage.removeItem("studentName");
            localStorage.removeItem("studentId");
            localStorage.removeItem("examPassword");
            localStorage.removeItem("examPaperNo");

            const deadlineInfo = getDeadlineInfo();
            examData = {};

            document.getElementById("examArea").style.display = "none";
            const msg = document.getElementById("finalMessage");
            msg.querySelector("#finalMessageText").innerHTML =
                `<strong style="color: #dc3545; font-size: 24px;">EXAMINATION ${reason}</strong><br><br>
                CANDIDATE: <strong>${name.toUpperCase()}</strong><br>
                DEADLINE TIME WAS: <strong>${deadlineInfo.deadline_24h}</strong><br><br>
                DEADLINE DATE WAS: <strong>${currentExam ? currentExam.DEADLINE_DATE : "N/A"}</strong><br><br>
                <span style="color: #856404;">SUBMIT YOUR ANSWER SHEET TO THE SUPERVISOR IMMEDIATELY</span>`;
            msg.classList.add("show");

            if (currentExam && currentExam.Submitanswers) {
                showPopup(currentExam.Submitanswers);
            } else {
                console.warn("No MCQ link available for this exam.");
            }
        }

        function exitFullScreen() {
            if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(err => console.error('Exit fullscreen failed:', err));
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        }

        document.addEventListener("fullscreenchange", handleFullscreenChange);
        document.addEventListener("webkitfullscreenchange", handleFullscreenChange);
        document.addEventListener("mozfullscreenchange", handleFullscreenChange);
        document.addEventListener("MSFullscreenChange", handleFullscreenChange);

        function handleFullscreenChange() {
            const isFullscreen = document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.mozFullScreenElement ||
                document.msFullscreenElement;

            if (!isFullscreen && document.getElementById("examArea").style.display !== "none") {
                showFullscreenOverlay();
            } else {
                hideFullscreenOverlay();
                resizeCanvas();
            }
        }

        function showFullscreenOverlay() {
            document.getElementById("fullscreenOverlay").style.display = "flex";
        }

        function hideFullscreenOverlay() {
            document.getElementById("fullscreenOverlay").style.display = "none";
        }

        function reEnterFullscreen() {
            const element = document.documentElement;
            if (element.requestFullscreen) {
                element.requestFullscreen().then(() => {
                    hideFullscreenOverlay();
                    resizeCanvas();
                }).catch(err => {
                    console.error('Fullscreen request failed:', err);
                    alert("Failed to enter fullscreen. Please try again or use browser controls (e.g., F11).");
                });
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen().then(() => {
                    hideFullscreenOverlay();
                    resizeCanvas();
                }).catch(err => {
                    console.error('Fullscreen request failed:', err);
                    alert("Failed to enter fullscreen. Please try again or use browser controls.");
                });
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen().then(() => {
                    hideFullscreenOverlay();
                    resizeCanvas();
                }).catch(err => {
                    console.error('Fullscreen request failed:', err);
                    alert("Failed to enter fullscreen. Please try again or use browser controls.");
                });
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen().then(() => {
                    hideFullscreenOverlay();
                    resizeCanvas();
                }).catch(err => {
                    console.error('Fullscreen request failed:', err);
                    alert("Failed to enter fullscreen. Please try again or use browser controls.");
                });
            } else {
                alert("Fullscreen is not supported by your browser. Please use browser controls (e.g., F11).");
            }
        }

        document.addEventListener("keydown", function(e) {
            if (e.key === "Enter" && document.getElementById("setup").style.display !== "none") {
                e.preventDefault();
                const startBtn = document.getElementById("startExamBtn");
                if (startBtn) startBtn.click();
            }
        });

        function handleSecurityKeydown(e) {
            if (e.altKey || e.ctrlKey || e.metaKey) {
                e.preventDefault();
                return false;
            }

            if (e.key.startsWith('F') && e.key !== 'F11') {
                e.preventDefault();
                return false;
            }

            if (e.key === "Escape" || e.key === "F11" || e.key === "Start" || e.key === "F12") {
                setTimeout(() => {
                    if (!document.fullscreenElement &&
                        !document.webkitFullscreenElement &&
                        !document.mozFullScreenElement &&
                        !document.msFullscreenElement &&
                        document.getElementById("examArea").style.display !== "none") {
                        showFullscreenOverlay();
                    }
                }, 100);
            }
        }

        function DeadlineCheck() {
            const deadlineTimeEnd = getDeadlineTimestamp();
            const currentTime = Date.now();
            const storedName = localStorage.getItem("studentName") || "Student";
            if (currentTime >= deadlineTimeEnd) {
                clearInterval(countdownInterval);
                localStorage.removeItem("examEndTime");
                localStorage.removeItem("studentName");
                localStorage.removeItem("studentId");
                localStorage.removeItem("examPassword");
                localStorage.removeItem("examPaperNo");
                document.getElementById("examArea").style.display = "none";
                document.getElementById("fullscreenOverlay").style.display = "none";
                const msg = document.getElementById("finalMessage");
                msg.querySelector("#finalMessageText").innerHTML =
                    `<strong style="color: #dc3545; font-size: 24px;"> DEADLINE REACHED</strong><br><br>
            CANDIDATE: <strong>${storedName.toUpperCase()}</strong><br>
            DEADLINE TIME WAS: <strong>${currentExam.deadline}</strong><br>
            DEADLINE DATE WAS: <strong>${currentExam.DEADLINE_DATE}</strong><br><br>
            <span style="color: #856404;">SUBMIT YOUR ANSWER SHEET TO THE SUPERVISOR IMMEDIATELY</span>`;
                msg.classList.add("show");
                logActivity('Exam terminated due to deadline', {
                    deadline: currentExam.deadline
                });
                if (currentExam.Submitanswers) {
                    showPopup(currentExam.Submitanswers);
                }
            }
        }

        function getCurrentTimeStatus() {
            return getDeadlineInfo();
        }

        function getRemainingTimeText() {
            const info = getDeadlineInfo();
            return `Time until deadline (${info.deadline_24h}): ${info.time_remaining.formatted}`;
        }

        window.examTimer = {
            getStatus: getCurrentTimeStatus,
            getRemaining: getRemainingTimeText,
            getDeadline: () => {
                if (!currentExam) return "N/A";
                return `${String(currentExam.DEADLINE_HOUR).padStart(2, '0')}:${String(currentExam.DEADLINE_MINUTE).padStart(2, '0')}`;
            },
            setDeadline: (hour, minute) => {
                if (!Number.isInteger(hour) || !Number.isInteger(minute)) {
                    console.error('Hour and minute must be integers.');
                    return;
                }
                if (hour >= 0 && hour <= 23 && minute >= 0 && minute <= 59) {
                    if (!currentExam) {
                        console.error("No exam loaded");
                        return;
                    }
                    currentExam.DEADLINE_HOUR = hour;
                    currentExam.DEADLINE_MINUTE = minute;

                    const detailsEl = document.getElementById('paperdetails');
                    if (detailsEl) {
                        detailsEl.innerHTML = `Subject: ${currentExam.Subject} | Paper No: ${currentExam.Paperno} | Duration: ${currentExam.DurationHours}H ${currentExam.DurationMinutes}M | Date: ${currentExam.DEADLINE_DATE} ${String(currentExam.DEADLINE_HOUR).padStart(2,'0')}:${String(currentExam.DEADLINE_MINUTE).padStart(2,'0')}`;
                    }

                    console.log(`Deadline updated to ${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`);

                    if (typeof countdownInterval !== 'undefined') {
                        clearInterval(countdownInterval);
                        startCountdown();
                    }
                } else {
                    console.error('Invalid time format. Use 24-hour format: hour (0-23), minute (0-59)');
                }
            }
        };

        function showPopup(url) {
            if (!url) {
                console.warn("No URL provided for popup.");
                alert("No MCQ answer sheet available.");
                return;
            }
            const popup = document.getElementById("popupOverlay");
            const iframe = document.getElementById("popupIframe");
            iframe.src = url;
            popup.style.display = "flex";
        }

        function closePopup() {
            const popup = document.getElementById("popupOverlay");
            const iframe = document.getElementById("popupIframe");
            iframe.src = "";
            popup.style.display = "none";
        }

        document.addEventListener("contextmenu", (e) => e.preventDefault());

        document.addEventListener("keydown", (e) => {
            if (e.ctrlKey && e.key.toLowerCase() === "p") {
                e.preventDefault();
                alert("Printing is disabled during the exam.");
            }

            if (e.ctrlKey && ["c", "v", "x"].includes(e.key.toLowerCase())) {
                e.preventDefault();
                alert("Copy, paste, and cut are disabled during the exam.");
            }

            if ((e.ctrlKey && ["R"].includes(e.key.toLowerCase())) || (e.key === "F5")) {
                e.preventDefault();
                alert("Reload is disabled during the exam.");
            }

            // Prevent Alt+Tab, Ctrl+Alt+Del, etc.
            if (e.altKey || e.ctrlKey || e.metaKey) {
                e.preventDefault();
                return false;
            }

            // Prevent F-keys that might cause issues
            if (e.key.startsWith('F') && e.key !== 'F11') {
                e.preventDefault();
                return false;
            }

            if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === "i") {
                e.preventDefault();
                alert("Developer tools are disabled during the exam.");
            }

            if (e.key === "F12") {
                e.preventDefault();
                alert("Developer tools are disabled during the exam.");
            }
        });

        document.addEventListener("paste", (e) => {
            e.preventDefault();
            alert("Pasting is disabled during the exam.");
        });

        document.addEventListener("copy", (e) => {
            e.preventDefault();
            alert("Copying is disabled during the exam.");
        });

        document.addEventListener("cut", (e) => {
            e.preventDefault();
            alert("Cut is disabled during the exam.");
        });

        function detectUnauthorizedScripts() {
            //modify pointers here to add more scripts to detect
        }

        document.getElementById("reloadPaperBtn").addEventListener("click", function() {
            const iframe = document.getElementById("examPaper");
            if (iframe && iframe.src) {
                const oldSrc = iframe.src;
                iframe.src = "";
                setTimeout(() => {
                    iframe.src = oldSrc;
                    resizeCanvas();
                }, 200);
            }
        });
    </script>
</body>

</html>