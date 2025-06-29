document.addEventListener("DOMContentLoaded", async function () {
  const monitoringKey = `monitoringStarted-${monitoringData.sessionId}`;

  // Cek apakah monitoring untuk session ini sudah pernah dimulai
  if (sessionStorage.getItem(monitoringKey)) {
    console.log("Monitoring already active, skipping prompt and monapp.");
    return; // skip seluruh script, monitoring sedang berjalan
  }

  // Tandai sebagai sudah dimulai agar tidak diulang
  sessionStorage.setItem(monitoringKey, "true");

  try {
    // Jalankan monitoring app
    window.open("monapp:run", "_self");

    // Tunggu sebentar agar aplikasi siap
    await new Promise((res) => setTimeout(res, 1000));

    // Ambil data student & quiz dari tag HTML (diinject dari PHP)
    const monitoringData = window.monitoringData;
    if (!monitoringData) return;

    let userConfirmed = confirm(
      `Hi, ${monitoringData.studentName} (${monitoringData.username})!\n` +
        `There is an ongoing quiz attempt:\nQuiz: ${monitoringData.quizName}\n\n` +
        `By continuing, you agree to allow device activity monitoring during this attempt. ` +
        `Monitoring will automatically stop when the attempt ends or if you leave this page.\n\n` +
        `Do you agree to proceed?`
    );

    const statusEl = document.getElementById("monitoring-status");
    const stopBtnContainer = document.getElementById(
      "stop-monitoring-container"
    );

    if (userConfirmed) {
      statusEl.textContent = "Connected. Request Monitoring...";
      statusEl.style.color = "yellow";

      const ws = new WebSocket("ws://localhost:51107");
      let monitoring = false;

      ws.onmessage = function (event) {
        const message = event.data;
        console.log("Message from server:", message);

        if (message === "ready") {
          ws.send(
            JSON.stringify({
              command: "startMonitoring",
              studentId: monitoringData.studentId,
              sessionId: monitoringData.sessionId,
              stopTime: monitoringData.endTime,
            })
          );
        } else if (message === "running") {
          monitoring = true;
          stopBtnContainer.style.display = "block";

          fetch(
            `/local/monitoring/log.php?session_id=${monitoringData.sessionId}&student_id=${monitoringData.studentId}`
          )
            .then((res) => res.json())
            .then((data) => {
              const lastStatus = data.status;
              sendMonitoringLog(
                lastStatus === "left" ? "returned" : "accepted"
              );
              statusEl.textContent = "Monitoring";
              statusEl.style.color = "green";
            })
            .catch((err) => console.error("Error checking last status:", err));
        } else if (message === "stopped") {
          monitoring = false;
          sendMonitoringLog("left");
          statusEl.textContent = "Stopped";
          statusEl.style.color = "red";
        } else if (message === "finish") {
          monitoring = false;
          sendMonitoringLog("finished");
          statusEl.textContent = "Monitoring session has finished";
          statusEl.style.color = "gray";
          stopBtnContainer.style.display = "none";
        }
      };

      ws.onclose = () => {
        if (monitoring) sendMonitoringLog("left");
        statusEl.textContent = "Not Connected";
        statusEl.style.color = "red";
        stopBtnContainer.style.display = "none";
      };

      ws.onerror = (err) => {
        if (monitoring) sendMonitoringLog("left");
        statusEl.textContent = "Error";
        statusEl.style.color = "red";
        console.error("WebSocket error:", err);
        stopBtnContainer.style.display = "none";
      };
    } else {
      sendMonitoringLog("rejected");
      statusEl.textContent = "You declined monitoring";
      statusEl.style.color = "red";
    }

    function sendMonitoringLog(status) {
      navigator.sendBeacon(
        "/local/monitoring/log.php",
        new URLSearchParams({
          session_id: monitoringData.sessionId,
          student_id: monitoringData.studentId,
          status: status,
        })
      );
    }
  } catch (err) {
    console.error("Monitoring init error:", err);
  }
});
