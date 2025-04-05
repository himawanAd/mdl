<?php
require_once('../../config.php');
global $DB;

$cmid = required_param('cmid', PARAM_INT);

// Ambil data terbaru dari database
$monitoring_data = $DB->get_records_sql("
    SELECT m.id, m.student_id, m.course_module_id, m.app_name, m.detail, m.start_time, m.end_time
    FROM {monitoring} m
    WHERE m.course_module_id = ?
    ORDER BY m.id DESC",
    [$cmid]
);

// Ambil status terakhir per student
$status_data = $DB->get_records_sql("
    SELECT l.student_id, l.status
    FROM {monitoring_log} l
    INNER JOIN (
        SELECT student_id, MAX(logged_at) as max_logged
        FROM {monitoring_log}
        WHERE course_module_id = ?
        GROUP BY student_id
    ) latest ON l.student_id = latest.student_id AND l.logged_at = latest.max_logged
    WHERE l.course_module_id = ?
", [$cmid, $cmid]);

// Konversi ke JSON dan kirim sebagai respons
echo json_encode([
    'monitoring' => array_values($monitoring_data),
    'status' => $status_data
]);
exit;
