<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php?error=invalid_id");
    exit();
}

$sql = "DELETE FROM brands WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: index.php?success=brand_deleted");
        exit();
    } else {
        header("Location: index.php?error=delete_failed");
        exit();
    }
    $stmt->close();
} else {
    header("Location: index.php?error=db_error");
    exit();
}
?>