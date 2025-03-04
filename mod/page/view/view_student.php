<?php
if ($monitoringData->monitoring_enabled) {
    $currentTime = time();
    $startTime = $monitoringData->start_monitoring;
    $endTime = $monitoringData->stop_monitoring;
    if ($startTime && $endTime && $currentTime >= $startTime && $currentTime <= $endTime) {
        $startMonitoring = $monitoringData->start_monitoring ? userdate($monitoringData->start_monitoring) : 'Not set';
        $stopMonitoring = $monitoringData->stop_monitoring ? userdate($monitoringData->stop_monitoring) : 'Not set';
    
        // Tampilkan alert menggunakan JavaScript
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                let userConfirmed = confirm(`Hi, $USER->firstname $USER->lastname\\nThere is an ongoing session:\\n\\nSession: $cm->name\\nTime: $startMonitoring - $stopMonitoring\\n\\nBy continuing, you agree to allow device activity monitoring during this session. Monitoring will automatically stop if the session ends or you leave this page.\\n\\nDo you agree to proceed?`);
                
                // if (!userConfirmed) {
                //     window.location.href = '/moodle/course/view.php?id=YOUR_COURSE_ID'; // Arahkan ke halaman lain jika user tidak setuju
                // }
            });
        </script>";

        // Menghubungkan ke websocket
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                let ws = new WebSocket('ws://localhost:8080');
        
                ws.onopen = function() {
                    console.log('Connected to WebSocket Server');
                    let data = {
                        command: 'startMonitoring',
                        studentId: '$USER->id',
                        sessionId: '$cm->id'
                    };
                    
                    ws.send(JSON.stringify(data));
                    // ws.send(JSON.stringify({ event: 'student_connected', user: '$USER->id', course: '$course->id', activity: '$cm->id' }));
                };
        
                // ws.onmessage = function(event) {
                //     let data = JSON.parse(event.data);
                //     console.log('Message from server:', data);
                // };
        
                ws.onclose = function() {
                    console.log('Disconnected from WebSocket Server');
                };
        
                ws.onerror = function(error) {
                    console.error('WebSocket Error:', error);
                };
                
                // Menangani unload / keluar dari halaman
                window.addEventListener('beforeunload', function() {
                    let stopData = {
                        command: 'stopMonitoring'
                    };
                    ws.send(JSON.stringify(stopData));
                    ws.close();
                });
            });
        </script>";
    }
}

