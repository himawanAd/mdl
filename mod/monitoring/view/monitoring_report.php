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
        WHERE m.course_module_id = ?", 
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
        <title>Monitoring Page</title>
    </head>
    <body>
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Students</h2>
                <div class="sidebar-buttons">
                    <button onclick="goHome()" title="Home">
                        <i class="fas fa-home"></i>
                    </button>
                    <button onclick="goBack()" title="Back">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>
            <?php foreach ($students as $student): ?>
                <div class="student" onclick="loadReport('<?php echo $student->firstname . " " . $student->lastname; ?>', '<?php echo $student->username; ?>','<?php echo $student->id; ?>')">
                    <?php echo $student->firstname . " " . $student->lastname; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="content">
            <div id="reportBox" class="box" style="display: none">
                <div class="profile">
                    <img id="profilePic" src="profile.jpg" alt="Profile Picture">
                    <div id="profileName" class="name">Name</div>
                    <div id="profileNim" class="nim">NIM</div>
                </div>
                <div class="report">
                    <h3>Device Activity Report</h3>
                    <table>
                        <thead>
                        <tr>
                            <th>App</th>
                            <th>Duration (sec)</th>
                            <th>Percentage</th>
                        </tr>
                        </thead>
                        <tbody id="reportTableBody">
                        <!-- Javascript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>
            let monitoringData = <?php echo $monitoring_json; ?>;
            function loadReport(name, nim, studentId) {
                document.getElementById("profileName").textContent = name;
                document.getElementById("profileNim").textContent = nim;
                document.getElementById("reportBox").style.display = "flex";

                let tableBody = document.getElementById("reportTableBody");
                tableBody.innerHTML = ""; // Hapus isi sebelumnya

                // Filter data berdasarkan student_id yang dipilih
                let filteredData = monitoringData.filter(item => item.student_id == studentId);

                // Grupkan berdasarkan app_name
                let appUsage = {};
                filteredData.forEach(item => {
                    let duration = item.end_time - item.start_time;
                    if (!appUsage[item.app_name]) {
                        appUsage[item.app_name] = { duration: 0, details: [] };
                    }
                    appUsage[item.app_name].duration += duration;
                    appUsage[item.app_name].details.push({
                        title: item.detail,
                        start: new Date(item.start_time * 1000).toLocaleTimeString(),
                        end: new Date(item.end_time * 1000).toLocaleTimeString(),
                        duration: duration
                    });
                });

                // Hitung total durasi
                let totalDuration = Object.values(appUsage).reduce((sum, app) => sum + app.duration, 0);

                // Tampilkan dalam tabel
                Object.keys(appUsage).forEach(appName => {
                    let app = appUsage[appName];

                    // Baris utama
                    let row = document.createElement("tr");
                    row.classList.add("row");
                    row.setAttribute("onclick", "toggleDropdown(this)");
                    row.innerHTML = `
                        <td>${appName}</td>
                        <td>${app.duration} sec</td>
                        <td>${((app.duration / totalDuration) * 100).toFixed(2)}%</td>
                    `;
                    tableBody.appendChild(row);

                    // Dropdown detail
                    let dropdownRow = document.createElement("tr");
                    dropdownRow.classList.add("dropdown");
                    let detailsHtml = `
                        <td colspan="3">
                            <table style="width: 100%; border-collapse: collapse">
                                <thead>
                                    <tr style="background-color: #f8f9fa">
                                        <th>Detail</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration (sec)</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    app.details.forEach(detail => {
                        detailsHtml += `
                            <tr>
                                <td>${detail.title}</td>
                                <td>${detail.start}</td>
                                <td>${detail.end}</td>
                                <td>${detail.duration}</td>
                            </tr>
                        `;
                    });

                    detailsHtml += `</tbody></table></td>`;
                    dropdownRow.innerHTML = detailsHtml;
                    tableBody.appendChild(dropdownRow);
                });
            }

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