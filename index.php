<?php
session_start();
if (!isset($_SESSION['login'])) {
  header("Location: from_login/login.php");
  exit;
}
include "koneksi.php";
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard | Sistem Irigasi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      background-color: #f8f9fa;
      margin: 0;
    }
  </style>
</head>
<body>

  <?php
  // Routing halaman
  $page = basename($_GET['page'] ?? 'dashboard');
  if (preg_match('/^[a-z0-9_-]+$/', $page)) {
    $file = "pages/$page.php";
    if (file_exists($file)) {
      include $file;
    } else {
      echo "<div class='alert alert-danger m-3'>Halaman <strong>$page</strong> tidak ditemukan.</div>";
    }
  } else {
    echo "<div class='alert alert-danger m-3'>Permintaan halaman tidak valid.</div>";
  }
  ?>

</body>
</html>
