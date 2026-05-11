<?php
header("Content-Type: application/json");
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit;
}

$conn = conexionDB();
$id = $data['id'];
$comentario = $data['comentario'] ?? '';

$sql = "UPDATE justificante 
SET estado = 'Rechazado', 
    comentario_oficial = ?, 
WHERE id_justificante = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $comentario, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "mensaje" => "Justificante rechazado"]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$stmt->close();
$conn->close();
?>