<?php
include "koneksi.php";

$tanggal_filter = $_GET['filter_tanggal'] ?? '';
$minggu_filter = $_GET['minggu'] ?? '';

try {
    if ($minggu_filter) {
        // Jika ada filter minggu, tampilkan data untuk minggu tersebut
        $stmt = $pdo->prepare("SELECT * FROM sensor_data WHERE WEEK(waktu, 1) = ? ORDER BY waktu DESC");
        $stmt->execute([$minggu_filter]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ambil informasi periode minggu
        $stmt_periode = $pdo->prepare("SELECT MIN(DATE(waktu)) AS tanggal_awal, MAX(DATE(waktu)) AS tanggal_akhir FROM sensor_data WHERE WEEK(waktu, 1) = ?");
        $stmt_periode->execute([$minggu_filter]);
        $periode = $stmt_periode->fetch(PDO::FETCH_ASSOC);
        
    } elseif ($tanggal_filter) {
        // Jika ada filter tanggal
        $stmt = $pdo->prepare("SELECT * FROM sensor_data WHERE DATE(waktu) = ? ORDER BY waktu DESC LIMIT 10");
        $stmt->execute([$tanggal_filter]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $periode = null;
    } else {
        // Default: 10 data terbaru
        $stmt = $pdo->prepare("SELECT * FROM sensor_data ORDER BY waktu DESC LIMIT 10");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $periode = null;
    }

    // Ambil daftar minggu untuk navigasi
    $stmt_minggu = $pdo->prepare("SELECT WEEK(waktu, 1) AS minggu, MIN(DATE(waktu)) AS tanggal_awal, MAX(DATE(waktu)) AS tanggal_akhir FROM sensor_data GROUP BY WEEK(waktu, 1) ORDER BY minggu DESC LIMIT 5");
    $stmt_minggu->execute();
    $minggu_result = $stmt_minggu->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $result = [];
    $minggu_result = [];
    $periode = null;
}
?>

<div class="container mt-4">
  <?php if ($minggu_filter): ?>
    <h4 class="mb-3">
      <i class="fas fa-calendar-week"></i> 
      Riwayat Minggu ke-<?= htmlspecialchars($minggu_filter) ?>
      <?php if ($periode): ?>
        (<?= date('d M', strtotime($periode['tanggal_awal'])) ?> - <?= date('d M Y', strtotime($periode['tanggal_akhir'])) ?>)
      <?php endif; ?>
    </h4>
  <?php else: ?>
    <h4 class="mb-3"><i class="fas fa-clock"></i> Riwayat 10 Data Terakhir</h4>
  <?php endif; ?>

  <?php if (!$minggu_filter): ?>
    <form method="get" class="row g-2 mb-3 align-items-center">
      <input type="hidden" name="page" value="riwayat_mingguan">
      <div class="col-auto">
        <label for="filter_tanggal" class="form-label mb-0">Pilih Tanggal:</label>
      </div>
      <div class="col-auto">
        <input type="date" name="filter_tanggal" id="filter_tanggal" class="form-control form-control-sm" value="<?= htmlspecialchars($tanggal_filter) ?>">
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-outline-primary">
          <i class="fas fa-search"></i> Tampilkan
        </button>
      </div>
    </form>
  <?php endif; ?>

  <div class="table-responsive shadow-sm bg-white p-2 rounded">
    <table class="table table-bordered table-sm text-center align-middle mb-0">
      <thead class="table-success">
        <tr>
          <th>#</th>
          <th>Soil 1</th>
          <th>Soil 2</th>
          <th>Air</th>
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
              <td><?= date('d-m H:i', strtotime($row['waktu'])) ?></td>
              <td>
                <a href="?page=riwayat_detail&tanggal=<?= date('Y-m-d', strtotime($row['waktu'])) ?>" class="btn btn-sm btn-outline-secondary">
                  <i class="fas fa-eye"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-muted">Tidak ada data.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if (!$minggu_filter): ?>
    <h5 class="mt-4"><i class="fas fa-calendar-week"></i> Riwayat Mingguan</h5>
    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle text-center mt-2">
        <thead class="table-info">
          <tr>
            <th>Minggu ke</th>
            <th>Periode</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($minggu_result)): ?>
            <?php foreach ($minggu_result as $minggu): ?>
              <tr>
                <td><?= htmlspecialchars($minggu['minggu']) ?></td>
                <td><?= date('d M', strtotime($minggu['tanggal_awal'])) ?> - <?= date('d M Y', strtotime($minggu['tanggal_akhir'])) ?></td>
                <td>
                  <a href="?page=riwayat_mingguan&minggu=<?= htmlspecialchars($minggu['minggu']) ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-calendar-week"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="3" class="text-muted">Belum ada data mingguan tersedia.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <div class="mt-3">
    <?php if ($minggu_filter): ?>
      <a href="?page=riwayat_mingguan" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Mingguan
      </a>
    <?php endif; ?>
    <a href="?page=riwayat" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-list"></i> Riwayat Utama
    </a>
    <a href="?page=dashboard" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-home"></i> Dashboard
    </a>
  </div>
</div>
