<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']); 

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

include 'koneksi.php';

$error = '';

$sql_brands = "SELECT id, name FROM brands ORDER BY name";
$result_brands = $conn->query($sql_brands);
$brands = $result_brands ? $result_brands->fetch_all(MYSQLI_ASSOC) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model_name = trim($_POST['model_name']);
    $brand_id = (int)$_POST['brand_id'];
    $model_image_path = null;
    $uploadDir = "uploads/models/";

    if (empty($model_name) || $brand_id <= 0) {
        $error = "Nama Model dan Brand Motor wajib diisi.";
    } 
    
    if (empty($error) && isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['file_upload']['name']);
        $fileTmpName = $_FILES['file_upload']['tmp_name'];
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpName, $targetFile)) {
            $model_image_path = $targetFile;
        } else {
            $error = "Gagal mengunggah file gambar.";
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO models (name, brand_id, image) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sis", $model_name, $brand_id, $model_image_path);
            
            if ($stmt->execute()) {
                $stmt->close();
                header('Location: index.php?success=model_added');
                exit();
            } else {
                $error = "Gagal menambahkan model: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Error prepare statement: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Model Motor Baru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Model Motor Baru</h1>

        <ul class="main-nav">
            <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Dashboard Utama</a></li>
            <li><a href="create.php" class="<?= ($current_page == 'create.php') ? 'active' : '' ?>">Tambah Brand</a></li>
            <li><a href="create_model.php" class="<?= ($current_page == 'create_model.php') ? 'active' : '' ?>">Tambah Model</a></li>
            <li><a href="create_product.php" class="<?= ($current_page == 'create_product.php') ? 'active' : '' ?>">Tambah Produk</a></li>
        </ul>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="create_model.php" method="POST" enctype="multipart/form-data" class="form-create">
            
            <div class="form-group">
                <label for="brand_id">Pilih Brand Motor:</label>
                <select id="brand_id" name="brand_id" class="form-control" required>
                    <option value="">-- Pilih Brand --</option>
                    <?php if (!empty($brands)): ?>
                        <?php foreach ($brands as $brand): 
                            $is_selected = ($brand['id'] == ($_POST['brand_id'] ?? '')) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($brand['id']) ?>" <?= $is_selected ?>>
                                <?= htmlspecialchars($brand['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Tidak ada Brand tersedia.</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="model_name">Nama Model (Contoh: Vario 160, NMAX 155):</label>
                <input type="text" id="model_name" name="model_name" class="form-control" value="<?= htmlspecialchars($_POST['model_name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="file_upload">Gambar Model (Opsional):</label>
                <input type="file" id="file_upload" name="file_upload" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Model</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>