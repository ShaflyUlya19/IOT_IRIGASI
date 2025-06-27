<?php
include '../koneksi.php';

$query = mysqli_query($koneksi, "SELECT * FROM kontrol ORDER BY id DESC LIMIT 1");
if ($data = mysqli_fetch_assoc($query)) {
    $output = '';

    if ($data['servo_utama'] == 'nyala') $output .= 'buka_utama ';
    else if ($data['servo_utama'] == 'mati') $output .= 'tutup_utama ';

    if ($data['servo1'] == 'nyala') $output .= 'buka_servo1 ';
    else if ($data['servo1'] == 'mati') $output .= 'tutup_servo1 ';

    if ($data['servo2'] == 'nyala') $output .= 'buka_servo2 ';
    else if ($data['servo2'] == 'mati') $output .= 'tutup_servo2 ';

    echo trim($output);
} else {
    echo "no_status";
}
?>
