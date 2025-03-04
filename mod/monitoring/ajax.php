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

// Konversi ke JSON dan kirim sebagai respons
echo json_encode(array_values($monitoring_data));
exit;
