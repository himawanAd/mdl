<?php
    $students = $DB->get_records_sql("
        SELECT u.id, u.firstname, u.lastname, u.username 
        FROM {user} u
        JOIN {user_enrolments} ue ON u.id = ue.userid
        JOIN {enrol} e ON ue.enrolid = e.id
        JOIN {role_assignments} ra ON u.id = ra.userid
        WHERE e.courseid = ? AND ra.contextid = ? AND ra.roleid = 5
        ORDER BY u.username ASC", 
        [$course->id, $context->id]
    );

    $monitoring_data = $DB->get_records_sql("
        SELECT m.id, m.student_id, m.course_module_id, m.app_name, m.detail, m.start_time, m.end_time
        FROM {monitoring} m
        WHERE m.course_module_id = ?
        ORDER BY m.id DESC",
        [$cmid], 0, 0
    );
    $monitoring_json = json_encode(array_values($monitoring_data));
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="styles.css">
        <title>Monitoring Live Report</title>
    </head>
    <body>
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Monitoring</h2>
                <div class="sidebar-buttons">
                    <button onclick="goHome()" title="Home">
                        <i class="fas fa-home"></i>
                    </button>
                    <button onclick="goBack()" title="Back">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>
            <div class="student" onclick="loadAllReport()">
                <?php echo "All Students"; ?>
            </div>
            <?php foreach ($students as $student): ?>
                <div class="student" onclick="loadReport('<?php echo $student->firstname . " " . $student->lastname; ?>', '<?php echo $student->username; ?>','<?php echo $student->id; ?>')">
                    <?php echo $student->firstname . " " . $student->lastname; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="content">
            <div id="session-detail" class="box">
                <div class="session-row">
                    <p><strong>Course:</strong> <?php echo $course->fullname; ?></p>
                    <p><strong>Section:</strong> <?php echo $activity_title; ?></p>
                </div>
                <div class="session-row">
                    <p><strong>Start Monitoring:</strong> <?php echo date('d/m/Y H:i:s', $start_time); ?></p>
                    <p><strong>End Monitoring:</strong> <?php echo date('d/m/Y H:i:s', $stop_time); ?></p>
                </div>
            </div>
            <div id="reportBox" class="box" style="display: none">
                <div id="profile" class="profile" style="display: none">
                    <img id="profilePic" src="profile.jpg" alt="Profile Picture">
                    <div id="profileName" class="name">Name</div>
                    <div id="profileNim" class="nim">NIM</div>
                </div>
                <div class="report">
                    <h3 id="reportHeader">Device Activity Live Report</h3>
                    <table class="table">
                        <thead id="reportTableHead"></thead>
                        <tbody id="reportTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>
            let monitoringData = <?php echo $monitoring_json; ?>;
            let students = <?php echo json_encode(array_values($students)); ?>;
            let currentView = { type: "all" };

            function loadAllReport() {
                currentView = { type: "all" };
                document.getElementById("reportBox").style.display = "flex";
                document.getElementById("profile").style.display = "none";

                reportHeader.innerHTML = "Device Activity Live Report: All Students";
                let tableHead = document.getElementById("reportTableHead");
                tableHead.innerHTML = `
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>App Name</th>
                        <th>Detail</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                `;

                let tableBody = document.getElementById("reportTableBody");
                tableBody.innerHTML = ""; // Hapus isi sebelumnya

                let index = 1;
                monitoringData.forEach(item => {
                    // Temukan nama mahasiswa berdasarkan student_id
                    let student = students.find(s => s.id == item.student_id);
                    let studentName = student ? `${student.firstname} ${student.lastname}` : "Unknown";

                    let row = `
                        <tr>
                            <td>${index++}</td>
                            <td>${studentName}</td>
                            <td>${item.app_name}</td>
                            <td>${item.detail}</td>
                            <td>${new Date(item.start_time * 1000).toLocaleString()}</td>
                            <td>${item.end_time ? new Date(item.end_time * 1000).toLocaleString() : '-'}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            }
            
            function loadReport(name, nim, studentId) {
                currentView = { type: "student", name, nim, studentId };
                document.getElementById("profileName").textContent = name;
                document.getElementById("profileNim").textContent = nim;
                document.getElementById("reportBox").style.display = "flex";
                document.getElementById("profile").style.display = "";

                reportHeader.innerHTML = "Device Activity Live Report: " + name;
                let tableHead = document.getElementById("reportTableHead");
                tableHead.innerHTML = `
                    <tr>
                        <th>#</th>
                        <th>App Name</th>
                        <th>Detail</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                `;
                
                let tableBody = document.getElementById("reportTableBody");
                tableBody.innerHTML = "";

                // Filter data berdasarkan student_id yang dipilih
                let filteredData = monitoringData.filter(item => item.student_id == studentId);
                let index = 1;
                filteredData.forEach(item => {
                    let row = `
                        <tr>
                            <td>${index++}</td>
                            <td>${item.app_name}</td>
                            <td>${item.detail}</td>
                            <td>${new Date(item.start_time * 1000).toLocaleString()}</td>
                            <td>${item.end_time ? new Date(item.end_time * 1000).toLocaleString() : '-'}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            }

            function fetchMonitoringData() {
                fetch(`ajax.php?cmid=<?php echo $cmid; ?>`)
                    .then(response => response.json())
                    .then(data => {
                        monitoringData = data; // Perbarui data monitoring
                        if (currentView.type === "all") {
                            loadAllReport(); // Render ulang tabel
                        } else if (currentView.type === "student"){
                            loadReport(currentView.name, currentView.nim, currentView.studentId);
                        }
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }

            // Jalankan polling setiap 1 detik
            setInterval(fetchMonitoringData, 1000);

            function toggleDropdown(row) {
                const nextRow = row.nextElementSibling;
                if (nextRow && nextRow.classList.contains("dropdown")) {
                    nextRow.classList.toggle("visible");
                }
            }

            function goHome() {
                window.location.href = "<?php echo $CFG->wwwroot; ?>"; // Sesuaikan dengan halaman utama
            }

            function goBack() {
                window.history.back();
            }
        </script>
    </body>
</html>