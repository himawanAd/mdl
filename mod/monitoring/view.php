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

    $PAGE->set_url('/mod/monitoring/view.php', array('id' => $course->id));
    $PAGE->set_title(get_string('monitoring', 'mod_monitoring'));

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Bisa dipisahkan ke file CSS eksternal -->
    <title>View Report</title>
    <style>
      body {
        font-family: "Nunito", sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
      }
      h1,
      h2,
      h3 {
        font-family: "Nunito", sans-serif;
        font-weight: 700; /* Bold */
      }

      p,
      td,
      th {
        font-family: "Nunito", sans-serif;
        font-weight: 400; /* Regular */
      }

      .small-text {
        font-family: "Nunito", sans-serif;
        font-weight: 300; /* Light */
        font-size: 0.9em;
      }
      .sidebar {
        width: 20%;
        background-color: #f8f9fa;
        padding: 20px;
        height: 100vh;
        overflow-y: auto;
      }
      .student {
        margin: 15px 0;
        cursor: pointer;
        padding: 10px;
        border-radius: 5px;
        transition: background-color 0.3s;
      }
      .student:hover {
        background-color: #e9ecef;
      }
      .content {
        flex: 1;
        padding: 20px;
      }
      .box {
        display: flex;
        gap: 20px;
        border: 1px solid #ccc;
        border-radius: 10px;
        padding: 20px;
      }
      .profile {
        flex: 1;
        text-align: center;
      }
      .profile img {
        border-radius: 50%;
        width: 250px;
        height: 250px;
      }
      .profile .name {
        font-size: 1.5em;
        margin: 10px 0;
      }
      .profile .nim {
        font-size: 1em;
        color: gray;
      }
      .report {
        flex: 2;
      }
      .report table {
        width: 100%;
        border-collapse: collapse;
      }
      .report th {
        background-color: #ddd;
      }
      .report th,
      .report td {
        padding: 10px;
        text-align: left;
      }
      .dropdown table {
        margin-top: 10px;
        border: 1px solid #ddd;
        font-size: 0.9em;
      }

      .dropdown th,
      .dropdown td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: left;
      }

      .dropdown th {
        background-color: #f8f9fa;
        font-weight: bold;
      }

      .row:hover {
        background-color: #f1f1f1;
        cursor: pointer;
      }

      .dropdown {
        display: none;
      }

      .dropdown.visible {
        display: table-row;
      }

      .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-buttons button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            margin-left: 10px;
        }
        .sidebar-buttons button:hover {
            color: #007bff;
        }
    </style>
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
            <div class="student" onclick="loadReport('<?php echo $student->firstname . " " . $student->lastname; ?>', '<?php echo $student->username; ?>')">
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
        function loadReport(name, nim, activity) {
            document.getElementById("profileName").textContent = name;
            document.getElementById("profileNim").textContent = `NIM: ${nim}`;
            document.getElementById("reportBox").style.display = "flex";
            let tableBody = document.getElementById("reportTableBody");
            tableBody.innerHTML = ""; // Hapus isi tabel sebelumnya

            activity.forEach((app) => {
                // Buat baris utama (klik untuk toggle dropdown)
                let row = document.createElement("tr");
                row.classList.add("row");
                row.setAttribute("onclick", "toggleDropdown(this)");
                row.innerHTML = `
                    <td>${app.app}</td>
                    <td>${app.duration} sec</td>
                    <td>${app.percentage}</td>
                `;
                tableBody.appendChild(row);

                // Buat dropdown untuk detail penggunaan aplikasi
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

                app.details.forEach((detail) => {
                    detailsHtml += `
                        <tr>
                            <td>${detail.title}</td>
                            <td>${detail.start}</td>
                            <td>${detail.end}</td>
                            <td>${detail.duration}</td>
                        </tr>
                    `;
                });

                detailsHtml += `
                            </tbody>
                        </table>
                    </td>
                `;

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

<?php
// echo $OUTPUT->footer();