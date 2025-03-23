<?php
require_once('../../config.php');

header('Content-Type: application/json');

global $DB, $USER;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['session_id']) || !isset($_GET['student_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $course_module_id = intval($_GET['session_id']);
    $student_id = intval($_GET['student_id']);

    // Ambil status terakhir student di session tertentu
    $last_status = $DB->get_record_sql(
        "SELECT status FROM {monitoring_log} 
         WHERE course_module_id = ? AND student_id = ? 
         ORDER BY logged_at DESC LIMIT 1",
        [$course_module_id, $student_id]
    );

    if ($last_status) {
        echo json_encode(['status' => $last_status->status]);
    } else {
        echo json_encode(['status' => null]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Ambil input JSON dari request
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Validasi input
if (!isset($input['session_id']) || !isset($input['student_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Sanitasi data
$course_module_id = intval($input['session_id']);
$student_id = intval($input['student_id']);
$status = trim($input['status']);
$logged_at = time();

// Pastikan user sudah login
// if (!isloggedin() || isguestuser()) {
//     http_response_code(401);
//     echo json_encode(['error' => 'Unauthorized']);
//     exit;
// }

// Simpan ke database
$logEntry = new stdClass();
$logEntry->course_module_id = $course_module_id;
$logEntry->student_id = $student_id;
$logEntry->status = $status;
$logEntry->logged_at = $logged_at;

$inserted = $DB->insert_record('monitoring_log', $logEntry);

if ($inserted) {
    echo json_encode(['success' => 'Log saved']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save log']);
}
?>
