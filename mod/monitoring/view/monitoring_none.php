<?php
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="styles.css">
        <title>Monitoring Unavailable</title>
    </head>
    <body id="mod-monitoring">
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
        </div>
        <div class="content-unavailable">
            <div class="icon">⚠️</div> 
            <h2>Monitoring Report is not available</h2>
            <p>There is no monitoring data available at the moment.</p>
        </div>
        <script>
            function goHome() {
                window.location.href = "<?php echo $CFG->wwwroot; ?>"; // Sesuaikan dengan halaman utama
            }

            function goBack() {
                window.history.back();
            }
        </script>
    </body>
</html>