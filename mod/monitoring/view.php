<?php
    require_once(__DIR__ . '/../../config.php');
    require_once($CFG->libdir . '/accesslib.php');

    $courseid = required_param('id', PARAM_INT);
    require_login($courseid);
    $context = context_course::instance($courseid);
    if (!has_capability('moodle/course:update', $context) && !has_capability('mod/monitoring:view', $context)) {
        throw new moodle_exception('accessdenied', 'error');
    }

    $PAGE->set_url('/mod/monitoring/view.php', array('id' => $courseid));
    $PAGE->set_title(get_string('monitoring', 'mod_monitoring'));

  $students = [
        [
            'name' => 'Himawan Addillah',
            'nim' => 'M0518022',
            'activity' => [
                ['app' => 'Microsoft Edge', 'duration' => 15, 'percentage' => '46.04%', 'details' => [
                    ['title' => 'Active Windows Tracker', 'start' => '18:24:36', 'end' => '18:24:41', 'duration' => 5],
                    ['title' => 'Implementasi Tracker Web', 'start' => '18:24:41', 'end' => '18:24:45', 'duration' => 3],
                ]],
                ['app' => 'Windows Explorer', 'duration' => 8, 'percentage' => '23.98%', 'details' => [
                    ['title' => 'Task Switching', 'start' => '18:24:47', 'end' => '18:24:50', 'duration' => 3],
                ]]
            ]
        ],
        [
            'name' => 'Mahasiswa B',
            'nim' => '789012',
            'activity' => [
                ['app' => 'Visual Studio Code', 'duration' => 7, 'percentage' => '20.99%', 'details' => [
                    ['title' => 'script.js', 'start' => '18:24:50', 'end' => '18:24:57', 'duration' => 7],
                ]],
                ['app' => 'Microsoft Notes', 'duration' => 2, 'percentage' => '8.98%', 'details' => [
                    ['title' => 'Sticky Notes', 'start' => '18:25:00', 'end' => '18:25:03', 'duration' => 2],
                ]]
            ]
        ],
        [
            'name' => 'Mahasiswa C',
            'nim' => '345678',
            'activity' => [
                ['app' => 'Microsoft Edge', 'duration' => 10, 'percentage' => '35.00%', 'details' => [
                    ['title' => 'E-Learning Page', 'start' => '18:20:30', 'end' => '18:20:40', 'duration' => 10],
                ]],
                ['app' => 'Windows Explorer', 'duration' => 5, 'percentage' => '17.50%', 'details' => [
                    ['title' => 'File Browsing', 'start' => '18:20:45', 'end' => '18:20:50', 'duration' => 5],
                ]]
            ]
        ]
    ];
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
            <div class="student" onclick="loadReport('<?php echo $student['name']; ?>', '<?php echo $student['nim']; ?>', <?php echo htmlspecialchars(json_encode($student['activity']), ENT_QUOTES, 'UTF-8'); ?>)">
                <?php echo $student['name']; ?>
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