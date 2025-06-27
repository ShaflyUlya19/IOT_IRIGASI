<?php
include "koneksi.php";
$s=$_POST['status']; $m=$_POST['mode'];
$koneksi->query("UPDATE kontrol SET status_manual='$s', mode='$m' WHERE id=1");
header("Location: index.php?page=kontrol");
