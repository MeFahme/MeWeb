<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit();
}

$sql_select = "SELECT image FROM products WHERE id = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $product_id);
$stmt_select->execute();
$result = $stmt_select->get_result();
$product = $result->fetch_assoc();
$stmt_select->close();

$image_path = $product['image'] ?? null;

$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }
        
        $stmt->close();
        header('Location: index.php?success=product_deleted');
        exit();
    } else {
        header('Location: index.php?error=delete_failed');
        exit();
    }
    $stmt->close();
} else {
    header('Location: index.php?error=db_error');
    exit();
}
?>