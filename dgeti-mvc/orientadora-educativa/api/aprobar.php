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
$dias = $data['dias'] ?? null;
$comentario = $data['comentario'] ?? '';

// Generar folio único
$folio = 'JUS-' . date('Y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);

if ($dias) {
    $sql = "UPDATE justificante 
    SET estado = 'Aprobado', 
        comentario_oficial = ?,
        dias_autorizados = ?,
        folio = COALESCE(folio, ?),
       
    WHERE id_justificante = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisi", $comentario, $dias, $folio, $id);
} else {
    $sql = "UPDATE justificante 
    SET estado = 'Aprobado', 
        comentario_oficial = ?,
        folio = COALESCE(folio, ?),
        
    WHERE id_justificante = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $comentario, $folio, $id);
}

if ($stmt->execute()) {
    echo json_encode([
        "success" => true, 
        "folio" => $folio,
        "mensaje" => "Justificante aprobado correctamente"
    ]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$stmt->close();
$conn->close();
?>