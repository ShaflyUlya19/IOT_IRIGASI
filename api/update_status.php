<?php
// File: api/update_status.php
include '../koneksi.php';

$servo_utama = $_GET['servo_utama'] ?? '';
$servo1 = $_GET['servo1'] ?? '';
$servo2 = $_GET['servo2'] ?? '';
$relay1 = $_GET['relay1'] ?? '';
$relay2 = $_GET['relay2'] ?? '';

$sql = "UPDATE kontrol SET 
  servo_utama = '$servo_utama',
  servo1 = '$servo1',
  servo2 = '$servo2',
  relay1 = '$relay1',
  relay2 = '$relay2',
  waktu = NOW()
  WHERE id = 1";

if ($koneksi->query($sql)) {
  echo "OK";
} else {
  echo "Gagal update";
}
?>
