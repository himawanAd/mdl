<?php
// Tampilkan status dan button mod/monitoring untuk role teacher
if ($monitoringData && $monitoringData->monitoring_enabled) {
    $startMonitoring = $monitoringData->start_monitoring ? userdate($monitoringData->start_monitoring) : 'Not set';
    $stopMonitoring = $monitoringData->stop_monitoring ? userdate($monitoringData->stop_monitoring) : 'Not set';
    $current_time = time();
    // Pastikan user memiliki role teacher untuk menampilkan informasi ini
    if (has_capability('mod/page:addinstance', $PAGE->context)) {
        // Tampilkan informasi monitoring
        echo "<div class='monitoring-info' style='margin: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;'>";
        if ($current_time > $monitoringData->stop_monitoring) {
            echo "<strong>Monitoring Status: </strong> Finished <br>";
        } elseif ($current_time >= $monitoringData->start_monitoring && $current_time <= $monitoringData->stop_monitoring) {
            echo "<strong>Monitoring Status: </strong> Is Running <br>";
        } elseif ($current_time < $monitoringData->start_monitoring) {
            echo "<strong>Monitoring Status: </strong> Not Started <br>";
        } else {
            echo "<strong>Monitoring Status: </strong> Unavailable <br>";
        }
        echo "<strong>Start Monitoring: </strong> $startMonitoring <br>";
        echo "<strong>Stop Monitoring: </strong> $stopMonitoring <br>";
        // Tampilkan tombol Monitoring Report
        echo $OUTPUT->single_button(new moodle_url('/mod/monitoring/view.php',  array('id' => $cm->id)), 'Monitoring Report', 'post');
        echo "</div>";

    }
}