<?php
session_start();

// Database configuration - updated to match koneksi.php
$hostname = 'db.be-mons1.bengt.wasmernet.com';
$port = '3306';
$user = 'ec7db74278588000182d570725d8';
$password = '0685ec7d-b742-7949-8000-17757ee943e6';
$database = 'iot_irigasi1';

$dsn = "mysql:host=$hostname;port=$port;dbname=$database;charset=utf8mb4";

$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
);

try {
    $pdo = new PDO($dsn, $user, $password, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $pass = md5($_POST['password']);

    if (empty($username) || empty($pass)) {
        $_SESSION['error'] = "Username dan Password tidak boleh kosong.";
        header("Location: login.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $stmt->execute([$username, $pass]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $_SESSION['login'] = true;
            $_SESSION['user'] = $username;
            header("Location: ../index.php");
        } else {
            $_SESSION['error'] = "Username atau Password salah.";
            header("Location: login.php");
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan pada sistem.";
        header("Location: login.php");
    }
    exit;
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login | Irigasi IoT</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap & Google Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: url('https://images.unsplash.com/photo-1524901548305-08eeddc35080?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
      background-size: cover;
    }

    .login-card {
      backdrop-filter: blur(10px);
      background-color: rgba(255, 255, 255, 0.25);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.3);
      color: #333;
    }

    .login-card h4 {
      font-weight: 600;
    }

    .login-card input {
      background-color: rgba(255, 255, 255, 0.7);
    }

    .btn-primary {
      background-color: #198754;
      border: none;
    }

    .btn-primary:hover {
      background-color: #157347;
    }
  </style>
</head>
<body>
  <div class="d-flex justify-content-center align-items-center vh-100">
    <div class="login-card w-100" style="max-width: 400px;">
      <h4 class="text-center mb-4">Login Irigasi IoT</h4>

      <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <form method="post" action="login.php">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" name="username" id="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</body>
</html>
