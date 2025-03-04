<?php
require_once('../../config.php');
global $DB;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['student_id'], $data['session_id'], $data['app_name'], $data['detail'], $data['start_time'])) {
        $record = new stdClass();
        $record->student_id = $data['student_id'];
        $record->course_module_id = $data['session_id'];
        $record->app_name = $data['app_name'];
        $record->detail = $data['detail'];
        $record->start_time = strtotime($data['start_time']);
        $record->end_time = null;

        $inserted_id = $DB->insert_record('monitoring', $record);
        echo json_encode(['status' => 'success', 'id' => $inserted_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
} elseif ($method === 'PUT') {
    // Update end_time
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['tracking_id'], $data['end_time'])) {
        $update_data = new stdClass();
        $update_data->id = $data['tracking_id'];
        $update_data->end_time = strtotime($data['end_time']);

        $DB->update_record('monitoring', $update_data);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
