<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['login'])) {
  header("Location: from_login/login.php");
  exit;
}
include "koneksi.php";

// Tangani mode kontrol
$mode = $_SESSION['mode_kontrol'] ?? 'otomatis';
if (isset($_GET['mode'])) {
  $mode = $_GET['mode'] === 'manual' ? 'manual' : 'otomatis';
  $_SESSION['mode_kontrol'] = $mode;
}

// Ambil data sensor terbaru dengan timeout
$sensor = null;
$perintah_otomatis = "";

try {
    // Set timeout untuk query database
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 3);
    $stmt = $pdo->prepare("SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $sensor = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    // Continue without sensor data
}

// Proses mode otomatis - hanya set status, jangan panggil API di sini
if ($mode === 'otomatis' && $sensor && isset($sensor['kelembapan_tanah1']) && isset($sensor['kelembapan_tanah2'])) {
  $kelembapan1 = $sensor['kelembapan_tanah1'];
  $kelembapan2 = $sensor['kelembapan_tanah2'];  
  $ketinggian_air = $sensor['ketinggian_air'];

  if ($kelembapan1 < 50 && $ketinggian_air > 25) {
    $perintah_otomatis = "Petak 1 aktif (otomatis) - Kelembapan rendah: {$kelembapan1}%";
    // Set flag untuk JavaScript handle
    $auto_action = "relay1=nyala";
  } elseif ($kelembapan2 < 50 && $ketinggian_air > 25) {
    $perintah_otomatis = "Petak 2 aktif (otomatis) - Kelembapan rendah: {$kelembapan2}%";
    $auto_action = "relay1=nyala";
  } else {
    $perintah_otomatis = "Tidak ada kebutuhan irigasi - Kondisi optimal";
    $auto_action = "relay1=mati";
  }
} else {
  $perintah_otomatis = $sensor ? "Mengevaluasi kondisi sensor..." : "Menunggu data sensor...";
  $auto_action = "";
}
?>

<div class="container mt-4">
  <h2 class="mb-4"><i class="fas fa-sliders-h"></i> Kontrol Irigasi Realtime</h2>

  <!-- Mode Switcher -->
  <div class="mb-4">
    <form method="get" class="d-flex align-items-center gap-3">
      <input type="hidden" name="page" value="kontrol">
      <label class="form-label m-0"><strong>Mode Kontrol:</strong></label>
      <select name="mode" class="form-select w-auto" onchange="this.form.submit()">
        <option value="otomatis" <?= $mode === 'otomatis' ? 'selected' : '' ?>>Otomatis</option>
        <option value="manual" <?= $mode === 'manual' ? 'selected' : '' ?>>Manual</option>
      </select>
    </form>
  </div>

  <!-- Status -->
  <div class="alert alert-<?= $mode === 'otomatis' ? 'primary' : 'warning' ?>" id="mode-status">
    <strong>Mode: <?= ucfirst($mode) ?></strong><br>
    <span id="status-text"><?= htmlspecialchars($perintah_otomatis) ?></span>
    <?php if ($mode === 'manual'): ?>
      <br><small>Gunakan tombol di bawah untuk mengatur kontrol secara manual.</small>
    <?php endif; ?>
  </div>

  <!-- Data Sensor -->
  <?php if ($sensor): ?>
  <div class="alert alert-info">
    <small>
      <strong>Data Sensor:</strong> 
      Kelembapan 1: <?= htmlspecialchars($sensor['kelembapan_tanah1'] ?? 'N/A') ?>% | 
      Kelembapan 2: <?= htmlspecialchars($sensor['kelembapan_tanah2'] ?? 'N/A') ?>% | 
      Ketinggian Air: <?= htmlspecialchars($sensor['ketinggian_air'] ?? 'N/A') ?>cm
      <span class="badge bg-secondary ms-2">
        <?= date('H:i:s', strtotime($sensor['created_at'] ?? 'now')) ?>
      </span>
    </small>
  </div>
  <?php else: ?>
  <div class="alert alert-warning">
    <small><strong>Peringatan:</strong> Data sensor tidak tersedia</small>
  </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Servo Utama -->
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Pintu Air Utama</h5>
          <div class="d-flex gap-3">
            <button onclick="updateStatus('servo_utama','buka')" class="btn btn-success w-100" <?= $mode === 'manual' ? '' : 'disabled' ?>>
              <i class="fas fa-door-open"></i> Buka Utama
            </button>
            <button onclick="updateStatus('servo_utama','tutup')" class="btn btn-danger w-100" <?= $mode === 'manual' ? '' : 'disabled' ?>>
              <i class="fas fa-door-closed"></i> Tutup Utama
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Relay 1 -->
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Pompa / Valve</h5>
          <div class="d-flex gap-3">
            <button onclick="updateStatus('relay1','nyala')" class="btn btn-warning w-100" <?= $mode === 'manual' ? '' : 'disabled' ?>>
              <i class="fas fa-power-off"></i> Relay 1 ON
            </button>
            <button onclick="updateStatus('relay1','mati')" class="btn btn-secondary w-100" <?= $mode === 'manual' ? '' : 'disabled' ?>>
              <i class="fas fa-power-off"></i> Relay 1 OFF
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4" id="status-display">
    <div class="alert alert-info">Menunggu status terbaru...</div>
  </div>
</div>

<script>
// Auto action untuk mode otomatis
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($mode === 'otomatis' && !empty($auto_action)): ?>
    // Jalankan aksi otomatis setelah halaman load
    setTimeout(() => {
        const params = '<?= $auto_action ?>';
        executeAutoAction(params);
    }, 1000);
    <?php endif; ?>
    
    // Auto refresh halaman setiap 30 detik untuk update sensor
    setInterval(() => {
        if (window.location.search.includes('page=kontrol')) {
            window.location.reload();
        }
    }, 30000);
});

function executeAutoAction(params) {
    const statusText = document.getElementById('status-text');
    const originalText = statusText.innerHTML;
    
    statusText.innerHTML = originalText + ' <i class="fas fa-spinner fa-spin"></i>';
    
    fetch(`api/update_status.php?${params}`)
        .then(res => res.text())
        .then(response => {
            statusText.innerHTML = originalText + ' ✓';
            console.log('Auto action executed:', response);
        })
        .catch(err => {
            statusText.innerHTML = originalText + ' ⚠️';
            console.error('Auto action failed:', err);
        });
}

function updateStatus(param, value) {
  const statusDisplay = document.getElementById('status-display');
  
  // Show loading
  statusDisplay.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Mengirim perintah...</div>';
  
  // Add timeout untuk mencegah hanging
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 8000); // 8 second timeout
  
  fetch(`api/update_status.php?${param}=${value}`, {
    signal: controller.signal
  })
    .then(res => {
      clearTimeout(timeoutId);
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      return res.text();
    })
    .then(response => {
      statusDisplay.innerHTML =
        `<div class="alert alert-success">
          <strong>Berhasil!</strong> ${param} diatur ke "${value}"
          <br><small>Response: ${response}</small>
          <br><small>Waktu: ${new Date().toLocaleTimeString()}</small>
        </div>`;
      
      // Auto hide success message after 5 seconds
      setTimeout(() => {
        statusDisplay.innerHTML = '<div class="alert alert-info">Menunggu status terbaru...</div>';
      }, 5000);
    })
    .catch(err => {
      clearTimeout(timeoutId);
      console.error('Error:', err);
      let errorMsg = err.name === 'AbortError' ? 'Request timeout (>8s)' : err.message;
      statusDisplay.innerHTML =
        `<div class="alert alert-danger">
          <strong>Gagal!</strong> Tidak dapat mengirim perintah.
          <br><small>Error: ${errorMsg}</small>
          <br><small>Coba lagi atau periksa koneksi API</small>
        </div>`;
    });
}
</script>
