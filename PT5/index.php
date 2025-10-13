<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

include 'koneksi.php';

// Query untuk Brands
$sql_brands = "SELECT id, name, logo FROM brands"; 
$result_brands = $conn->query($sql_brands);
$brands_data = []; 

if ($result_brands && $result_brands->num_rows > 0) {
    while ($row = $result_brands->fetch_assoc()) {
        $brands_data[] = $row;
    }
}

// Query untuk Models
$sql_models = "
    SELECT 
        m.id AS model_id, 
        m.name AS model_name,
        m.image AS model_image,
        b.name AS brand_name 
    FROM models m
    JOIN brands b ON m.brand_id = b.id
    ORDER BY m.id ASC";

$result_models = $conn->query($sql_models);
$models_data = $result_models ? $result_models->fetch_all(MYSQLI_ASSOC) : [];

// Query untuk Products
$sql_products = "
    SELECT 
        p.id AS product_id,          
        p.name AS product_name, 
        p.price, 
        p.image AS product_image,
        b.name AS brand_name,
        GROUP_CONCAT(m.name ORDER BY m.name SEPARATOR ', ') AS model_names_list 
    FROM products p
    JOIN product_models pm ON p.id = pm.product_id
    JOIN models m ON pm.model_id = m.id
    JOIN brands b ON m.brand_id = b.id
    GROUP BY p.id, p.name, p.price, p.image, b.name 
    ORDER BY p.id ASC";

$result_products = $conn->query($sql_products);
$products_data = $result_products ? $result_products->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Garage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1>Dashboard Admin Garage</h1>
                <p>Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>
            </div>
            <a href="logout.php" class="btn btn-danger" style="width: auto;">Logout</a>
        </div>
        
        <ul class="main-nav">
            <li><a href="index.php" class="active">Dashboard Utama</a></li>
            <li><a href="create.php">Tambah Brand</a></li>
            <li><a href="create_model.php">Tambah Model</a></li>
            <li><a href="create_product.php">Tambah Produk</a></li>
        </ul>

        <h2>Daftar Brand Motor</h2>

        <?php if (!empty($brands_data)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Brand</th>
                        <th>Logo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($brands_data as $brand): ?>
                        <tr>
                            <td><?= htmlspecialchars($brand['id']) ?></td>
                            <td><?= htmlspecialchars($brand['name']) ?></td>
                            <td>
                                <?php if (!empty($brand['logo'])): ?>
                                    <img src="<?= htmlspecialchars($brand['logo']) ?>" width="50" alt="<?= htmlspecialchars($brand['name']) ?> Logo">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?= $brand['id'] ?>" class="btn-edit">Edit</a>
                                    <a href="hapus.php?id=<?= $brand['id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-error">Belum ada data brand.</div>
        <?php endif; ?>
            
        <h2 style="margin-top: 3rem;">Daftar Model Motor</h2>
        
        <?php if (!empty($models_data)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand</th>
                        <th>Nama Model</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models_data as $model): ?>
                        <tr>
                            <td><?= htmlspecialchars($model['model_id']) ?></td>
                            <td><?= htmlspecialchars($model['brand_name']) ?></td>
                            <td><?= htmlspecialchars($model['model_name']) ?></td>
                            <td>
                                <?php if (!empty($model['model_image'])): ?>
                                    <img src="<?= htmlspecialchars($model['model_image']) ?>" width="50" alt="<?= htmlspecialchars($model['model_name']) ?>">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_model.php?id=<?= $model['model_id'] ?>" class="btn-edit">Edit</a>
                                    <a href="hapus_model.php?id=<?= $model['model_id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus model ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-error">Belum ada data model.</div>
        <?php endif; ?>

        <h2 style="margin-top: 3rem;">Daftar Produk Motor</h2>

        <?php if (!empty($products_data)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products_data as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product_id']) ?></td>
                            <td><?= htmlspecialchars($product['brand_name']) ?></td>
                            <td><?= htmlspecialchars($product['model_names_list']) ?></td>
                            <td><?= htmlspecialchars($product['product_name']) ?></td>
                            <td>Rp<?= number_format($product['price'], 0, ',', '.') ?></td>
                            <td>
                                <?php if (!empty($product['product_image'])): ?>
                                    <img src="<?= htmlspecialchars($product['product_image']) ?>" width="50" alt="Produk">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="btn-edit">Edit</a>
                                    <a href="hapus_product.php?id=<?= $product['product_id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus produk ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-error">Belum ada data produk.</div>
        <?php endif; ?>

        <div class="footer">
            &copy; <?= date('Y') ?> - Sistem CRUD Garage
        </div>
    </div>
</body>
</html>