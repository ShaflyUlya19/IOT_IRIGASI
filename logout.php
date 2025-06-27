<?php
session_start();
session_unset();
session_destroy();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Logout | Sistem Irigasi</title>
  <meta http-equiv="refresh" content="3;url=from_login/login.php">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .box {
      text-align: center;
      padding: 2rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="box">
    <div class="spinner-border text-success mb-3" role="status"></div>
    <h4>Berhasil Logout</h4>
    <p>Anda akan diarahkan ke halaman login dalam 3 detik...</p>
    <a href="from_login/login.php" class="btn btn-outline-success mt-3">Klik jika tidak otomatis</a>
  </div>
</body>
</html>
