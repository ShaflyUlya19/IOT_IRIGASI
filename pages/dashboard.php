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
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Irigasi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Google Fonts & Font Awesome -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      height: 100vh;
      font-family: 'Inter', sans-serif;
      background-color: #f4f6f8;
      overflow: hidden;
    }

    .container {
      display: flex;
      height: 100%;
      width: 100%;
    }

    .sidebar {
      width: 240px;
      background-color: #004d40;
      color: white;
      display: flex;
      flex-direction: column;
      padding: 30px 20px;
    }

    .sidebar .logo {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 40px;
      text-align: center;
    }

    .menu {
      list-style: none;
      padding: 0;
      flex-grow: 1;
    }

    .menu li {
      margin-bottom: 15px;
    }

    .menu a {
      color: white;
      text-decoration: none;
      padding: 10px 15px;
      display: flex;
      align-items: center;
      border-radius: 6px;
      font-weight: 500;
      transition: background 0.2s;
    }

    .menu a i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }

    .menu a:hover,
    .menu a.active {
      background-color: #00695c;
    }

    .logout {
      margin-top: auto;
      text-align: center;
      color: #ffc107;
      font-weight: 600;
      text-decoration: none;
    }

    .main-content {
      flex-grow: 1;
      padding: 40px 50px;
      overflow-y: auto;
    }

    .main-content h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #004d40;
      font-size: 22px;
      font-weight: 600;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      overflow: hidden;
    }

    thead {
      background-color: #00796b;
      color: white;
    }

    th, td {
      padding: 14px 18px;
      text-align: center;
      font-size: 14px;
      border-bottom: 1px solid #eee;
    }

    th i {
      margin-right: 6px;
    }

    tr:nth-child(even) {
      background-color: #fafafa;
    }

    .status-terbuka {
      background-color: #c8e6c9;
      color: #2e7d32;
      font-weight: 600;
      border-radius: 5px;
      padding: 5px 10px;
      display: inline-block;
    }

    .status-tertutup {
      background-color: #ffcdd2;
      color: #c62828;
      font-weight: 600;
      border-radius: 5px;
      padding: 5px 10px;
      display: inline-block;
    }

    @media screen and (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .sidebar {
        width: 100%;
        flex-direction: row;
        justify-content: space-around;
        align-items: center;
        height: auto;
      }

      .main-content {
        padding: 20px;
      }

      table, thead, tbody, th, td, tr {
        display: block;
      }

      thead {
        display: none;
      }

      tr {
        margin-bottom: 15px;
        background: #fff;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      }

      td {
        text-align: left;
        padding-left: 50%;
        position: relative;
      }

      td::before {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 45%;
        font-weight: bold;
        white-space: nowrap;
      }

      td:nth-of-type(1)::before { content: "ID"; }
      td:nth-of-type(2)::before { content: "Tanah 1"; }
      td:nth-of-type(3)::before { content: "Tanah 2"; }
      td:nth-of-type(4)::before { content: "Air"; }
      td:nth-of-type(5)::before { content: "Pintu"; }
      td:nth-of-type(6)::before { content: "Waktu"; }
    }
  </style>
</head>
<body>

<div class="container">
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <i class="fas fa-seedling"></i> Irigasi IoT
    </div>
    <ul class="menu">
      <li><a href="?page=dashboard" class="active"><i class="fas fa-gauge"></i> Dashboard</a></li>
      <li><a href="?page=grafik"><i class="fas fa-chart-line"></i> Grafik</a></li>
      <li><a href="?page=kontrol"><i class="fas fa-sliders-h"></i> Kontrol</a></li>
      <li><a href="?page=riwayat"><i class="fas fa-clock"></i> Riwayat</a></li>
    </ul>
    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>

  <!-- Konten -->
  <div class="main-content">
    <h2>IMPLEMENTASI SISTEM IRIGASI BERBASIS IoT UNTUK OPTIMASI AIR</h2>
    <h2>Data Sensor Realtime</h2>

    <table>
      <thead>
        <tr>
          <th><i class="fas fa-hashtag"></i> ID</th>
          <th><i class="fas fa-seedling"></i> Tanah 1</th>
          <th><i class="fas fa-seedling"></i> Tanah 2</th>
          <th><i class="fas fa-tint"></i> Ketinggian Air</th>
          <th><i class="fas fa-door-open"></i> Status Pintu</th>
          <th><i class="fas fa-clock"></i> Waktu</th>
        </tr>
      </thead>
      <tbody id="tabel-body">
        <tr><td colspan="6">Memuat data...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Realtime Fetch -->
<script>
function muatData() {
    .then(response => response.json())
    .then(data => {
      let html = '';
      if (data && data.length > 0) {
        data.forEach(row => {
          const statusClass = (row.status_pintu.toLowerCase() === 'terbuka') ? 'status-terbuka' : 'status-tertutup';
          html += `
            <tr>
              <td>${row.id}</td>
              <td>${row.kelembapan_tanah1}%</td>
              <td>${row.kelembapan_tanah2}%</td>
              <td>${row.ketinggian_air} cm</td>
              <td><span class="${statusClass}">${row.status_pintu.charAt(0).toUpperCase() + row.status_pintu.slice(1)}</span></td>
              <td>${row.waktu}</td>
            </tr>
          `;
        });
      } else {
        html = '<tr><td colspan="6">Tidak ada data tersedia</td></tr>';
      }
      document.getElementById('tabel-body').innerHTML = html;
    })
    .catch(error => {
      document.getElementById('tabel-body').innerHTML = '<tr><td colspan="6">Gagal memuat data</td></tr>';
    });
}

muatData();
setInterval(muatData, 5000);
</script>

</body>
</html>
