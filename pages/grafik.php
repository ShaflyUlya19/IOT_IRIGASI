<?php
$hostname = 'db.be-mons1.bengt.wasmernet.com';
$port = '3306';
$user = 'ec41b3e979f4800030a58cae9c37';
$password = '0685ec41-b3e9-7c89-8000-d58ad09a62fe';
$database = 'iot_irigasi';

$dsn = "mysql:host=$hostname;port=$port;dbname=$database";

$options = array(
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
);

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Mengambil data dari database
$data = [];
try {
    $query = "SELECT waktu, kelembapan_tanah1, kelembapan_tanah2, ketinggian_air, status_pintu, status_servo1, status_servo2 
              FROM sensor_data 
              ORDER BY waktu DESC 
              LIMIT 100";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll();
} catch (PDOException $e) {
    // Jika terjadi error, data tetap kosong array
    $data = [];
}


$tanggalFilter = $_GET['tanggal'] ?? null;
$filtered = [];

if ($data) {
    foreach ($data as $row) {
        $tanggal = date('Y-m-d', strtotime($row['waktu']));
        if (!$tanggalFilter || $tanggal === $tanggalFilter) {
            if (!isset($filtered[$tanggal])) $filtered[$tanggal] = [];
            if (count($filtered[$tanggal]) < 4) $filtered[$tanggal][] = $row;
        }
    }
}

$finalData = [];
foreach ($filtered as $rows) {
    foreach ($rows as $row) {
        $finalData[] = $row;
    }
}
?>

<div class="container-fluid px-4 mt-4">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <h2 class="text-center mb-4">
        <i class="fas fa-chart-line text-success me-2"></i>Grafik Sensor & Status Pintu
      </h2>

      <!-- Filter Tanggal -->
      <div class="text-center mb-4">
        <form method="get" action="?page=grafik" class="d-flex flex-wrap justify-content-center align-items-center gap-3">
          <input type="hidden" name="page" value="grafik">
          <label for="tanggal" class="form-label mb-0 fw-semibold">Filter Tanggal:</label>
          <input type="date" id="tanggal" name="tanggal" class="form-control" style="max-width: 200px;" value="<?= $tanggalFilter ?>">
          <button type="submit" class="btn btn-success">
            <i class="fas fa-filter me-1"></i>Tampilkan
          </button>
        </form>
      </div>

      <!-- Tombol Kembali ke Dashboard -->
      <div class="text-center mt-2 mb-4">
        <a href="index.php?page=dashboard" class="btn btn-outline-primary">
          <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
        </a>
      </div>

      <!-- Grafik -->
      <div class="row g-4">
        <!-- Grafik Sensor -->
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header text-center fw-bold text-success">
              <i class="fas fa-tint me-1"></i>Grafik Sensor
            </div>
            <div class="card-body">
              <canvas id="sensorChart" height="300"></canvas>
            </div>
          </div>
        </div>

        <!-- Grafik Status Pintu dan Servo -->
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header text-center fw-bold text-primary">
              <i class="fas fa-door-open me-1"></i>Status Pintu & Servo
            </div>
            <div class="card-body">
              <canvas id="statusChart" height="300"></canvas>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- SCRIPT CHART -->
<script>
const data = <?= json_encode($finalData); ?>;
const labels = data.map(d => {
  const t = new Date(d.waktu);
  return `${t.getDate().toString().padStart(2, '0')}-${(t.getMonth()+1).toString().padStart(2, '0')} ${t.getHours()}:${t.getMinutes().toString().padStart(2, '0')}`;
});

const kelembapan1 = data.map(d => parseFloat(d.kelembapan_tanah1));
const kelembapan2 = data.map(d => parseFloat(d.kelembapan_tanah2));
const ketinggian = data.map(d => parseFloat(d.ketinggian_air));

const statusPintu = data.map(d => d.status_pintu === 'terbuka' ? 1 : 0);
const statusServo1 = data.map(d => d.status_servo1 === 'terbuka' ? 1 : 0);
const statusServo2 = data.map(d => d.status_servo2 === 'terbuka' ? 1 : 0);

// ================= Grafik Sensor ====================
new Chart(document.getElementById('sensorChart'), {
  type: 'line',
  data: {
    labels: labels,
    datasets: [
      {
        label: 'Tanah 1 (%)',
        borderColor: '#43a047',
        backgroundColor: 'rgba(67, 160, 71, 0.1)',
        data: kelembapan1,
        tension: 0.4
      },
      {
        label: 'Tanah 2 (%)',
        borderColor: '#1e88e5',
        backgroundColor: 'rgba(30, 136, 229, 0.1)',
        data: kelembapan2,
        tension: 0.4
      },
      {
        label: 'Ketinggian Air (cm)',
        borderColor: '#e53935',
        backgroundColor: 'rgba(229, 57, 53, 0.1)',
        data: ketinggian,
        tension: 0.4
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      tooltip: {
        mode: 'index',
        intersect: false
      },
      legend: {
        position: 'bottom'
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

// ================= Grafik Status ====================
new Chart(document.getElementById('statusChart'), {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [
      {
        label: 'Pintu Utama',
        data: statusPintu,
        backgroundColor: '#43a047'
      },
      {
        label: 'Servo 1',
        data: statusServo1,
        backgroundColor: '#1e88e5'
      },
      {
        label: 'Servo 2',
        data: statusServo2,
        backgroundColor: '#fbc02d'
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'bottom'
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            const label = context.dataset.label || '';
            const status = context.raw === 1 ? 'Terbuka' : 'Tertutup';
            return `${label}: ${status}`;
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 1,
          callback: function(value) {
            return value === 1 ? 'Terbuka' : 'Tertutup';
          }
        }
      }
    }
  }
});
</script>
