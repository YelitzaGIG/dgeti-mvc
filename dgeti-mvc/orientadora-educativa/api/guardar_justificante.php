<?php
header("Content-Type: application/json");
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "No llegaron datos"]);
    exit;
}

$conn = conexionDB();

$check = $conn->query("SELECT COUNT(*) as total FROM justificante");
$row = $check->fetch_assoc();
$folio = "JUS-" . date("Y") . "-" . str_pad($row['total'] + 1, 4, "0", STR_PAD_LEFT);

$sql = "INSERT INTO justificante 
(folio, tipo_motivo, descripcion_motivo, dias_solicitados, fecha_inicio, fecha_fin, estado)
VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')";

$stmt = $conn->prepare($sql);

$tipo = $data['motivo'] ?? 'Personal';
$descripcion = $data['descripcion'] ?? '';
$dias = $data['dias'] ?? 1;
$fechaInicio = $data['fechaInicio'] ?? date('Y-m-d');
$fechaFin = $data['fechaFin'] ?? date('Y-m-d');

$stmt->bind_param("sssiss", $folio, $tipo, $descripcion, $dias, $fechaInicio, $fechaFin);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "folio" => $folio,
        "id" => $conn->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>