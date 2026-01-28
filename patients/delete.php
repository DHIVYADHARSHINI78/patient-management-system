<?php
include '../config/db.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM patients WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: list.php");
exit;
