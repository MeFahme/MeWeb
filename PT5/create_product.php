<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']); 

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

include 'koneksi.php';

$error = '';

$sql_models = "
    SELECT m.id, m.name AS model_name, b.name AS brand_name 
    FROM models m
    JOIN brands b ON m.brand_id = b.id
    ORDER BY brand_name, model_name";
$result_models = $conn->query($sql_models);
$models = $result_models ? $result_models->fetch_all(MYSQLI_ASSOC) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $price = $_POST['price'];
    $model_ids = $_POST['model_ids'] ?? [];
    $upload_dir = 'uploads/products/';

    if (empty($product_name) || empty($price) || empty($model_ids)) {
        $error = "Semua kolom wajib diisi, termasuk minimal satu Model Motor.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Harga harus berupa angka positif.";
    } else {
        $product_image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($file_tmp, $file_path)) {
                $product_image = $file_path;
            } else {
                $error = "Gagal mengunggah file gambar.";
            }
        }
        
        if (empty($error)) {
            $conn->begin_transaction();
            try {
                $stmt_product = $conn->prepare("INSERT INTO products (name, image, price) VALUES (?, ?, ?)");
                $stmt_product->bind_param("ssd", $product_name, $product_image, $price);
                $stmt_product->execute();
                $product_id = $conn->insert_id;
                $stmt_product->close();
                
                $stmt_relasi = $conn->prepare("INSERT INTO product_models (product_id, model_id) VALUES (?, ?)");
                $stmt_relasi->bind_param("ii", $product_id, $model_id);
                
                foreach ($model_ids as $model_id) {
                    $stmt_relasi->execute();
                }
                $stmt_relasi->close();
                
                $conn->commit();
                header('Location: index.php?success=product_added');
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Gagal menambahkan produk: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk Baru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Produk Motor Baru</h1>
        <ul class="main-nav">
            <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Dashboard Utama</a></li>
            <li><a href="create.php" class="<?= ($current_page == 'create.php') ? 'active' : '' ?>">Tambah Brand</a></li>
            <li><a href="create_model.php" class="<?= ($current_page == 'create_model.php') ? 'active' : '' ?>">Tambah Model</a></li>
            <li><a href="create_product.php" class="<?= ($current_page == 'create_product.php') ? 'active' : '' ?>">Tambah Produk</a></li>
        </ul>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="create_product.php" method="POST" enctype="multipart/form-data" class="form-create">
            
            <div class="form-group">
                <label for="product_name">Nama Produk:</label>
                <input type="text" id="product_name" name="product_name" class="form-control" value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price">Harga (Rp):</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="image">Gambar Produk:</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label for="model_ids">Pilih Model Motor (Bisa lebih dari satu):</label>
                <select id="model_ids" name="model_ids[]" multiple required size="5" class="form-control">
                    <?php if (!empty($models)): ?>
                        <?php 
                        $selected_models = $_POST['model_ids'] ?? [];
                        foreach ($models as $model): 
                            $is_selected = in_array($model['id'], $selected_models) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($model['id']) ?>" <?= $is_selected ?>>
                                <?= htmlspecialchars($model['brand_name']) ?> - <?= htmlspecialchars($model['model_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Tidak ada Model tersedia.</option>
                    <?php endif; ?>
                </select>
                <small>Tekan Ctrl (Windows) atau Command (Mac) untuk memilih beberapa model.</small>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Produk</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>