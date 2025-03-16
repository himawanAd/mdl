<?php
if ($monitoringData->monitoring_enabled) {
    $currentTime = time();
    $startTime = $monitoringData->start_monitoring;
    $endTime = $monitoringData->stop_monitoring;
    if ($startTime && $endTime && $currentTime >= $startTime && $currentTime <= $endTime) {
        $startMonitoring = $monitoringData->start_monitoring ? userdate($monitoringData->start_monitoring) : 'Not set';
        $stopMonitoring = $monitoringData->stop_monitoring ? userdate($monitoringData->stop_monitoring) : 'Not set';

        echo "<script>
            const monitoringData = {
                startMonitoring: '$startMonitoring',
                stopMonitoring: '$stopMonitoring',
                stopTime: " . ($monitoringData->stop_monitoring * 1000) . ",
                studentName: '$USER->firstname $USER->lastname',
                username: '$USER->username',
                sessionName: '$cm->name',
                sessionId: '$cm->id',
                studentId: '$USER->id',
                endTime: '$endTime'
            };
        </script>";

        include 'view_student.html';
    }
}
?>