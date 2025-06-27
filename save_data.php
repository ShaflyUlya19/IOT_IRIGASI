<?php
include "koneksi.php"; // pastikan file koneksi ini benar

$kelembapan1 = $_GET['kelembaban1'];  // tetap gunakan nama parameter dari ESP32
$kelembapan2 = $_GET['kelembaban2'];
$ketinggian_air = $_GET['ketinggian_air'];
$status_pintu = $_GET['pintu'];
$status_servo1 = $_GET['servo1'];
$status_servo2 = $_GET['servo2'];

$sql = "INSERT INTO sensor_data (kelembapan_tanah1, kelembapan_tanah2, ketinggian_air, status_pintu, status_servo1, status_servo2)
        VALUES ('$kelembapan1', '$kelembapan2', '$ketinggian_air', '$status_pintu', '$status_servo1', '$status_servo2')";

if (mysqli_query($conn, $sql)) {
    echo "Data berhasil disimpan.";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
