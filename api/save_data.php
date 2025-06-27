<?php
include "../koneksi.php";

// Ambil data dari URL (dikirim dari ESP32 via GET)
$kelembapan1 = $_GET['kelembapan_tanah1'] ?? 0;
$kelembapan2 = $_GET['kelembapan_tanah2'] ?? 0;
$ketinggian_air = $_GET['ketinggian_air'] ?? 0;
$status_pintu = $_GET['status_pintu'] ?? 'tertutup';
$status_servo1 = $_GET['status_servo1'] ?? 'tertutup';
$status_servo2 = $_GET['status_servo2'] ?? 'tertutup';

// Query simpan ke database
$sql = "INSERT INTO sensor_data (
            kelembapan_tanah1, kelembapan_tanah2, ketinggian_air, waktu, 
            status_pintu, status_servo1, status_servo2
        ) VALUES (
            '$kelembapan1', '$kelembapan2', '$ketinggian_air', NOW(),
            '$status_pintu', '$status_servo1', '$status_servo2'
        )";

if ($koneksi->query($sql) === TRUE) {
    echo "Data berhasil disimpan";
} else {
    echo "Error: " . $koneksi->error;
}
?>
