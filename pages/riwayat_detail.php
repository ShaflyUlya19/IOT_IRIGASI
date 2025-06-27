<?php
include "koneksi.php";

$tanggal = $_GET['tanggal'] ?? '';

try {
    if ($tanggal) {
        // Ambil data untuk tanggal tertentu (24 jam data)
        $stmt = $pdo->prepare("SELECT * FROM sensor_data WHERE DATE(waktu) = ? ORDER BY waktu DESC");
        $stmt->execute([$tanggal]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $title = "Riwayat Detail Tanggal " . date('d M Y', strtotime($tanggal));
    } else {
        // Ambil 10 data terbaru
        $stmt = $pdo->prepare("SELECT * FROM sensor_data ORDER BY waktu DESC LIMIT 10");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $title = "Riwayat Pemantauan Irigasi (10 Data Terakhir)";
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $result = [];
    $title = "Error - Tidak dapat mengambil data";
}
?>

<div class="container mt-4">
  <h2 class="mb-4"><i class="fas fa-clock"></i> <?= htmlspecialchars($title) ?></h2>

  <?php if ($tanggal): ?>
    <div class="alert alert-info mb-3">
      <i class="fas fa-info-circle"></i> 
      Menampilkan semua data untuk tanggal <strong><?= date('d M Y', strtotime($tanggal)) ?></strong>
      <?php if (!empty($result)): ?>
        (Total: <?= count($result) ?> data)
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="table-responsive shadow-sm bg-white p-3 rounded">
    <table class="table table-bordered table-striped table-hover align-middle text-center">
      <thead class="table-success">
        <tr>
          <th>ID</th>
          <th>Kelembapan 1 (%)</th>
          <th>Kelembapan 2 (%)</th>
          <th>Air (cm)</th>
          <th>Servo 1</th>
          <th>Servo 2</th>
          <th>Tanggal</th>
          <?php if (!$tanggal): ?>
            <th>Aksi</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($result)): ?>
          <?php foreach ($result as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['id']) ?></td>
              <td><?= htmlspecialchars($row['kelembapan_tanah1']) ?></td>
              <td><?= htmlspecialchars($row['kelembapan_tanah2']) ?></td>
              <td><?= htmlspecialchars($row['ketinggian_air']) ?></td>
              <td class="<?= strtolower($row['status_servo1']) === 'terbuka' ? 'text-success fw-bold' : 'text-danger fw-bold' ?>">
                <?= ucfirst(htmlspecialchars($row['status_servo1'])) ?>
              </td>
              <td class="<?= strtolower($row['status_servo2']) === 'terbuka' ? 'text-success fw-bold' : 'text-danger fw-bold' ?>">
                <?= ucfirst(htmlspecialchars($row['status_servo2'])) ?>
              </td>
              <td><?= date('d-m-Y H:i', strtotime($row['waktu'])) ?></td>
              <?php if (!$tanggal): ?>
                <td>
                  <a href="?page=riwayat_detail&tanggal=<?= date('Y-m-d', strtotime($row['waktu'])) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-calendar-day"></i> 24 Jam Data
                  </a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="<?= $tanggal ? '7' : '8' ?>" class="text-muted">Belum ada data tersedia.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    <a href="?page=riwayat" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
    </a>
    <a href="?page=dashboard" class="btn btn-outline-secondary">
      <i class="fas fa-home"></i> Dashboard
    </a>
  </div>
</div>
