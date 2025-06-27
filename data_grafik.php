<?php
include "koneksi.php";

$query = "SELECT * FROM sensor_data ORDER BY waktu DESC LIMIT 15";
$result = mysqli_query($koneksi, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>

