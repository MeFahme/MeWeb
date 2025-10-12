<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT id, name, logo FROM brands WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $brand = $result->fetch_assoc();
} else {
    header('Location: index.php?error=brand_not_found');
    exit();
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    
    $sql_update = "UPDATE brands SET name=? WHERE id=?";
    $stmt_update = $conn->prepare($sql_update);
    
    if ($stmt_update) {
        $stmt_update->bind_param("si", $name, $id);
        
        if ($stmt_update->execute()) {
            $stmt_update->close();
            header('Location: index.php?success=brand_updated');
            exit();
        } else {
            $error = "Error update data: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        $error = "Error prepare statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Brand</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Data Brand</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nama Brand:</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($brand['name']) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>