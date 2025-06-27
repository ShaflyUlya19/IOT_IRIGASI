<?php
session_start();

// === Koneksi ke Database (updated to match koneksi.php) ===
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

// Jika sudah login, langsung ke index
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}

// Proses login jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $pass = md5($_POST['password']);

    if (empty($username) || empty($pass)) {
        $_SESSION['error'] = "Username dan Password tidak boleh kosong.";
        header("Location: login.php");
        exit;
    }

    try {
        // Query aman dengan PDO
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $stmt->execute([$username, $pass]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $_SESSION['login'] = true;
            $_SESSION['user'] = $username;
            header("Location: ../index.php"); // Disesuaikan jika index.php ada di folder atas
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
  <title>Login Irigasi IoT</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">
  <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
    <h4 class="text-center mb-4">Login Irigasi IoT</h4>

    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="post" action="proses_login.php">
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
</body>
</html>
