<?php
require 'db.php';

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if ($id && in_array($status, ['pending', 'under review', 'resolved'])) {
    $stmt = $pdo->prepare("UPDATE appeals SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

header("Location: admin.php");
exit;
