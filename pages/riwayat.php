<?php
include "koneksi.php";

$tanggal_filter = $_GET['filter_tanggal'] ?? '';

try {
    if ($tanggal_filter) {
        $stmt = $pdo->prepare("SELECT * FROM sensor_data WHERE DATE(waktu) = ? ORDER BY waktu DESC LIMIT 10");
        $stmt->execute([$tanggal_filter]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM sensor_data ORDER BY waktu DESC LIMIT 10");
        $stmt->execute();
    }
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil daftar minggu unik berdasarkan data
    $stmt_minggu = $pdo->prepare("SELECT WEEK(waktu, 1) AS minggu, MIN(DATE(waktu)) AS tanggal_awal, MAX(DATE(waktu)) AS tanggal_akhir FROM sensor_data GROUP BY WEEK(waktu, 1) ORDER BY minggu DESC LIMIT 5");
    $stmt_minggu->execute();
    $minggu_result = $stmt_minggu->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $result = [];
    $minggu_result = [];
}
?>

<div class="container mt-4">
  <h2 class="mb-4"><i class="fas fa-clock"></i> Riwayat Pemantauan Irigasi (10 Data Terakhir)</h2>

  <!-- Form filter tanggal -->
  <form method="get" class="row g-3 mb-3">
    <input type="hidden" name="page" value="riwayat">
    <div class="col-auto">
      <label for="filter_tanggal" class="col-form-label">Filter Tanggal:</label>
    </div>
    <div class="col-auto">
      <input type="date" name="filter_tanggal" id="filter_tanggal" class="form-control" value="<?= htmlspecialchars($tanggal_filter) ?>">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i> Tampilkan
      </button>
    </div>
  </form>

  <!-- Tabel riwayat -->
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
          <th>Aksi</th>
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
              <td>
                <a href="?page=riwayat_detail&tanggal=<?= date('Y-m-d', strtotime($row['waktu'])) ?>" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-calendar-day"></i> 24 Jam Data
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-muted">Belum ada data tersedia.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Riwayat mingguan -->
  <h4 class="mt-5"><i class="fas fa-calendar-week"></i> Riwayat Per Minggu</h4>
  <ul class="list-group mt-3">
    <?php if (!empty($minggu_result)): ?>
      <?php foreach ($minggu_result as $minggu): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          Minggu ke-<?= htmlspecialchars($minggu['minggu']) ?> (<?= date('d M', strtotime($minggu['tanggal_awal'])) ?> - <?= date('d M Y', strtotime($minggu['tanggal_akhir'])) ?>)
          <a href="?page=riwayat_mingguan&minggu=<?= htmlspecialchars($minggu['minggu']) ?>" class="btn btn-sm btn-outline-success">
            <i class="fas fa-calendar-week"></i> Lihat Data Mingguan
          </a>
        </li>
      <?php endforeach; ?>
    <?php else: ?>
      <li class="list-group-item text-muted">Belum ada data mingguan tersedia.</li>
    <?php endif; ?>
  </ul>

  <!-- Tombol kembali -->
  <div class="mt-4">
    <a href="?page=dashboard" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>
  </div>
</div>
