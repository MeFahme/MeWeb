<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php?message=unauthorized');
    exit();
}

$model_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($model_id <= 0) {
    header('Location: index.php?error=invalid_model_id');
    exit();
}

$sql = "DELETE FROM models WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $model_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $stmt->close();
            header('Location: index.php?success=model_deleted');
            exit();
        } else {
            $stmt->close();
            header('Location: index.php?error=model_not_found');
            exit();
        }
    } else {
        header('Location: index.php?error=delete_model_failed');
        exit();
    }
    $stmt->close();
} else {
    header('Location: index.php?error=db_error');
    exit();
}
?>