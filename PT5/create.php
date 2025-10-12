<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']); 

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

include 'koneksi.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $logo_path = null;
    $uploadDir = "uploads/brands/";

    if (empty($name)) {
        $error = "Nama Brand tidak boleh kosong.";
    } else {
        if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['file_upload']['name']);
            $fileTmpName = $_FILES['file_upload']['tmp_name'];
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmpName, $targetFile)) {
                $logo_path = $targetFile;
            } else {
                $error = "Gagal mengunggah file logo.";
            }
        }
        
        if (empty($error)) {
            $sql = "INSERT INTO brands (name, logo) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("ss", $name, $logo_path);
                
                if ($stmt->execute()) {
                    $stmt->close();
                    header("Location: index.php?success=brand_added");
                    exit();
                } else {
                    $error = "Error saat menyimpan data: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error prepare statement: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Brand Motor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Brand Motor</h1>

        <ul class="main-nav">
            <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Dashboard Utama</a></li>
            <li><a href="create.php" class="<?= ($current_page == 'create.php') ? 'active' : '' ?>">Tambah Brand</a></li>
            <li><a href="create_model.php" class="<?= ($current_page == 'create_model.php') ? 'active' : '' ?>">Tambah Model</a></li>
            <li><a href="create_product.php" class="<?= ($current_page == 'create_product.php') ? 'active' : '' ?>">Tambah Produk</a></li>
        </ul>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-create">
            <div class="form-group">
                <label for="name">Nama Brand</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="file_upload">Logo Brand (Opsional)</label>
                <input type="file" id="file_upload" name="file_upload" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Brand</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>