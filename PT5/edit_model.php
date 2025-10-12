<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

$model_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$model = null;
$error = '';

$sql_brands = "SELECT id, name FROM brands ORDER BY name";
$result_brands = $conn->query($sql_brands);
$brands = $result_brands ? $result_brands->fetch_all(MYSQLI_ASSOC) : [];

if ($model_id > 0) {
    $sql_model = "SELECT id, name, brand_id FROM models WHERE id = ?";
    $stmt_model = $conn->prepare($sql_model);
    $stmt_model->bind_param("i", $model_id);
    $stmt_model->execute();
    $result_model = $stmt_model->get_result();
    $model = $result_model->fetch_assoc();
    $stmt_model->close();

    if (!$model) {
        $error = "Model tidak ditemukan.";
    }
} else {
    $error = "ID Model tidak valid.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $model) {
    $new_model_name = trim($_POST['model_name']);
    $new_brand_id = (int)$_POST['brand_id'];
    
    if (empty($new_model_name) || $new_brand_id <= 0) {
        $error = "Nama Model dan Brand wajib diisi.";
    } else {
        $sql_update = "UPDATE models SET name = ?, brand_id = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        
        if ($stmt_update) {
            $stmt_update->bind_param("sii", $new_model_name, $new_brand_id, $model_id);
            
            if ($stmt_update->execute()) {
                $stmt_update->close();
                header('Location: index.php?success=model_updated');
                exit();
            } else {
                $error = "Gagal memperbarui model: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $error = "Error prepare statement: " . $conn->error;
        }
    }
    
    if (!empty($error)) {
        $model['name'] = $new_model_name;
        $model['brand_id'] = $new_brand_id;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Model Motor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Model Motor</h1>
        
        <ul class="main-nav">
            <li><a href="index.php">Dashboard Utama</a></li>
            <li><a href="create.php">Tambah Brand</a></li>
            <li><a href="create_model.php">Tambah Model</a></li>
            <li><a href="create_product.php">Tambah Produk</a></li>
        </ul>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($model): ?>
            
        <form method="POST">
            
            <div class="form-group">
                <label for="model_name">Nama Model (Contoh: Vario 160, NMAX 155):</label>
                <input type="text" id="model_name" name="model_name" class="form-control" value="<?= htmlspecialchars($model['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="brand_id">Pilih Brand Motor:</label>
                <select id="brand_id" name="brand_id" class="form-control" required>
                    <option value="">-- Pilih Brand --</option>
                    <?php 
                    $selected_brand_id = $_POST['brand_id'] ?? $model['brand_id'];
                    
                    foreach ($brands as $brand): 
                        $is_selected = ($brand['id'] == $selected_brand_id) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($brand['id']) ?>" <?= $is_selected ?>>
                            <?= htmlspecialchars($brand['name']) ?>
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