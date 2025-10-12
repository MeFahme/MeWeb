<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$error = '';
$linked_models_ids = [];

$sql_models = "
    SELECT m.id, m.name AS model_name, b.name AS brand_name 
    FROM models m
    JOIN brands b ON m.brand_id = b.id
    ORDER BY brand_name, model_name";
$result_models = $conn->query($sql_models);
$available_models = $result_models ? $result_models->fetch_all(MYSQLI_ASSOC) : [];

if ($product_id > 0) {
    $sql_product = "SELECT id, name, price, image FROM products WHERE id = ?";
    $stmt_product = $conn->prepare($sql_product);
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();
    $product = $result_product->fetch_assoc();
    $stmt_product->close();

    $sql_linked = "SELECT model_id FROM product_models WHERE product_id = ?";
    $stmt_linked = $conn->prepare($sql_linked);
    $stmt_linked->bind_param("i", $product_id);
    $stmt_linked->execute();
    $result_linked = $stmt_linked->get_result();
    
    while ($row = $result_linked->fetch_assoc()) {
        $linked_models_ids[] = $row['model_id'];
    }
    $stmt_linked->close();

    if (!$product) {
        $error = "Produk tidak ditemukan.";
    }
} else {
    $error = "ID Produk tidak valid.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $product) {
    $new_name = trim($_POST['name']);
    $new_price = (float)$_POST['price'];
    $new_models = $_POST['models'] ?? [];
    $current_image = $product['image'];
    $new_image_path = $current_image;
    $uploadDir = "uploads/products/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['file_upload']['name']);
        $fileTmpName = $_FILES['file_upload']['tmp_name'];
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpName, $targetFile)) {
            $new_image_path = $targetFile;

            if ($current_image && $current_image !== $new_image_path && file_exists($current_image)) {
                unlink($current_image);
            }
        } else {
            $error = "Gagal mengunggah file gambar baru.";
        }
    }

    if (empty($error)) {
        $sql_update = "UPDATE products SET name=?, price=?, image=? WHERE id=?";
        $stmt_update = $conn->prepare($sql_update);
        
        if ($stmt_update) {
            $stmt_update->bind_param("sdsi", $new_name, $new_price, $new_image_path, $product_id);
            $stmt_update->execute();
            $stmt_update->close();

            $sql_delete = "DELETE FROM product_models WHERE product_id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $product_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            if (!empty($new_models)) {
                $sql_insert = "INSERT INTO product_models (product_id, model_id) VALUES (?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("ii", $product_id, $model_id);
                
                foreach ($new_models as $model_id) {
                    $stmt_insert->execute();
                }
                $stmt_insert->close();
            }

            header('Location: index.php?success=product_updated');
            exit();
            
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
    <title>Edit Produk Motor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Produk Motor</h1>
        
        <ul class="main-nav">
            <li><a href="index.php">Dashboard Utama</a></li>
            <li><a href="create.php">Tambah Brand</a></li>
            <li><a href="create_model.php">Tambah Model</a></li>
            <li><a href="create_product.php">Tambah Produk</a></li>
        </ul>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($product): ?>
            
        <form method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Harga (Rp)</label>
                <input type="number" id="price" name="price" class="form-control" value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>" required min="0">
            </div>

            <div class="form-group">
                <label>Gambar Produk Saat Ini</label>
                <?php if (!empty($product['image'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= htmlspecialchars($product['image']) ?>" width="100" alt="Gambar Produk">
                    </div>
                <?php else: ?>
                    <p class="text-muted">Tidak ada gambar.</p>
                <?php endif; ?>
                
                <label for="file_upload">Unggah Gambar Baru (Kosongkan jika tidak ingin diubah)</label>
                <input type="file" id="file_upload" name="file_upload" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label for="models">Pilih Model Motor (Tekan Ctrl/Command untuk memilih banyak):</label>
                <select id="models" name="models[]" class="form-control" multiple size="5" required>
                    <?php 
                    $selected_models = $_POST['models'] ?? $linked_models_ids;
                    
                    foreach ($available_models as $model): 
                        $is_selected = in_array($model['id'], $selected_models) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($model['id']) ?>" <?= $is_selected ?>>
                            <?= htmlspecialchars($model['brand_name']) ?> - <?= htmlspecialchars($model['model_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
        
        <?php endif; ?>
        
        <div class="footer">
            &copy; <?= date('Y') ?> - Sistem CRUD Garage
        </div>
    </div>
</body>
</html>