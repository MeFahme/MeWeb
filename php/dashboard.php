<?php
session_start();


if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>
    <p>Username: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <a href="logout.php">Logout</a>
    
    <hr>

    <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>Anda berhasil login ke sistem.</p>
    
    <h3>Informasi Session</h3>
    <p>Username: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <p>Waktu Login: <?php echo date('d F Y, H:i:s'); ?></p>
</body>
</html>