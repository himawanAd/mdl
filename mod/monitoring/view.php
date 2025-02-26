<?php
    require_once(__DIR__ . '/../../config.php');
    require_once($CFG->libdir . '/accesslib.php');

    $cmid = required_param('id', PARAM_INT);
    $cm = get_coursemodule_from_id('page', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $context = context_course::instance($course->id);
    require_login($course->id);

    if (!has_capability('moodle/course:update', $context) && !has_capability('mod/monitoring:view', $context)) {
        throw new moodle_exception('accessdenied', 'error');
    }

    if (!$cm) {
        die("Monitoring schedule not found.");
    }

    $current_time = time();
    $start_time = $cm->start_monitoring;
    $stop_time = $cm->stop_monitoring;

    if ($current_time > $stop_time) {
        require_once(__DIR__ . '/view/monitoring_report.php');
    } elseif ($current_time >= $start_time && $current_time <= $stop_time) {
        require_once(__DIR__ . '/view/monitoring_live.php');
    } else {
        require_once(__DIR__ . '/view/monitoring_none.php');
    }
?>