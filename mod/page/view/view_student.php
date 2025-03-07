<?php
if ($monitoringData->monitoring_enabled) {
    $currentTime = time();
    $startTime = $monitoringData->start_monitoring;
    $endTime = $monitoringData->stop_monitoring;
    if ($startTime && $endTime && $currentTime >= $startTime && $currentTime <= $endTime) {
        $startMonitoring = $monitoringData->start_monitoring ? userdate($monitoringData->start_monitoring) : 'Not set';
        $stopMonitoring = $monitoringData->stop_monitoring ? userdate($monitoringData->stop_monitoring) : 'Not set';
    
        echo "<div id='monitoring-info' style='margin: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;'>";
        echo "<strong>Monitoring Session: </strong> $startMonitoring - $stopMonitoring <br>";
        echo "<strong>Remaining Time: </strong> <span id='remaining-time'>---</span> <br>";
        echo "<strong>Monitoring Status: </strong> <span id='monitoring-status' style='color=gray;'>---</span> <br>";
        echo "<a href='#' id='toggle-monitoring-details' style='color: blue; text-decoration: underline; cursor: pointer;'>How does monitoring work?</a>"; 
        echo "<div id='monitoring-details' style='display: none; margin-top: 10px; padding: 5px; border-top: 1px solid #ddd;'>";
        echo "<p>Monitoring is only active during the session and will automatically stop when the session ends or if you leave this page.</p>";
        echo "<p>The data collected only includes a list of applications used during the session and their usage duration, without recording screen content within the applications.</p>"; 

        echo "</div>";
        echo "</div>";

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                let toggleLink = document.getElementById('toggle-monitoring-details');
                let detailsDiv = document.getElementById('monitoring-details');
                let remainingTimeSpan = document.getElementById('remaining-time');

                // Convert PHP timestamp to JavaScript timestamp
                let stopTime = " . ($monitoringData->stop_monitoring * 1000) . ";
                
                function updateRemainingTime() {
                    let now = new Date().getTime();
                    let timeLeft = stopTime - now;

                    if (timeLeft > 0) {
                        let seconds = Math.floor((timeLeft / 1000) % 60);
                        let minutes = Math.floor((timeLeft / (1000 * 60)) % 60);
                        let hours = Math.floor((timeLeft / (1000 * 60 * 60)) % 24);
                        let days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));

                        let timeString = 
                            (days > 0 ? days + 'd ' : '') + 
                            (hours > 0 ? hours + 'h ' : '') + 
                            (minutes > 0 ? minutes + 'm ' : '') + 
                            seconds + 's';
                        
                        remainingTimeSpan.textContent = timeString;
                    } else {
                        remainingTimeSpan.textContent = 'Session has ended';
                        document.getElementById('monitoring-status').style.color = '';
                        document.getElementById('monitoring-status').textContent = 'Stopped';
                        clearInterval(timer);
                    }
                }

                // Update every second
                let timer = setInterval(updateRemainingTime, 1000);
                updateRemainingTime();

                toggleLink.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (detailsDiv.style.display === 'none') {
                        detailsDiv.style.display = 'block';
                        toggleLink.textContent = 'Hide'; 
                    } else {
                        detailsDiv.style.display = 'none';
                        toggleLink.textContent = 'How does monitoring work?'; 
                    }
                });
            });
        </script>";

        // Tampilkan alert menggunakan JavaScript
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                let userConfirmed = confirm(`Hi, $USER->firstname $USER->lastname ($USER->username)!\\nThere is an ongoing session:\\nSession: $cm->name\\nTime: $startMonitoring - $stopMonitoring\\n\\nBy continuing, you agree to allow device activity monitoring during this session. Monitoring will automatically stop if the session ends or you leave this page.\\n\\nDo you agree to proceed?`);
                
                if (userConfirmed) {
                    let ws = new WebSocket('ws://localhost:8080');
                    ws.onopen = function() {
                        document.getElementById('monitoring-status').textContent = 'Connected. Request Monitoring...';
                        document.getElementById('monitoring-status').style.color = 'yellow';

                        let data = {
                            command: 'startMonitoring',
                            studentId: '$USER->id',
                            sessionId: '$cm->id',
                            stopTime: '$endTime'
                        };
                        ws.send(JSON.stringify(data));
                    };
            
                    ws.onmessage = function(event) {
                        let message = event.data;
                        console.log('Message from server:', message);
                        
                        if (message === 'running') {
                            document.getElementById('monitoring-status').textContent = 'Monitoring';
                            document.getElementById('monitoring-status').style.color = 'green';
                        } else if (message === 'stopped') {
                            document.getElementById('monitoring-status').textContent = 'Stopped';
                            document.getElementById('monitoring-status').style.color = 'red';
                        } else if (message === 'finish') {
                            document.getElementById('monitoring-status').textContent = 'Monitoring session has finished';
                            document.getElementById('monitoring-status').style.color = 'gray';
                        }
                    };

                    ws.onclose = function() {
                        document.getElementById('monitoring-status').textContent = 'Not Connected';
                        document.getElementById('monitoring-status').style.color = 'red';
                        console.log('Disconnected from WebSocket Server');
                    };
            
                    ws.onerror = function(error) {
                        document.getElementById('monitoring-status').textContent = 'Error:', error;
                        document.getElementById('monitoring-status').style.color = 'red';
                        console.error('WebSocket Error:', error);
                    };
                    
                    window.addEventListener('beforeunload', function(event) {
                        event.preventDefault();
                        event.returnValue = 'Closing this page will stop monitoring.';

                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({
                                command: 'stopMonitoring'
                            }));
                            document.getElementById('monitoring-status').textContent = 'Closing Monitoring...';
                            document.getElementById('monitoring-status').style.color = 'orange';
                        }
                        return 'Closing this page will stop monitoring.';
                    });
                } else {
                    document.getElementById('monitoring-status').textContent = 'You declined monitoring';
                    document.getElementById('monitoring-status').style.color = 'red';
                }
            });
        </script>";
    }
}

