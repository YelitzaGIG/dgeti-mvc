<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? 0;
$tipo_personal = $data['tipo_personal'] ?? '';
$tipo_permiso = $data['tipo_permiso'] ?? '';
$motivo = $data['motivo'] ?? '';
$fecha_inicio = $data['fecha_inicio'] ?? '';
$fecha_fin = $data['fecha_fin'] ?? '';

if (!$id) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$sql = "UPDATE permiso_personal SET 
        tipo_personal = '$tipo_personal',
        tipo_permiso = '$tipo_permiso',
        motivo = '$motivo',
        fecha_inicio = '$fecha_inicio',
        fecha_fin = '$fecha_fin'
        WHERE id_permiso = $id AND estado = 'pendiente'";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}

$conn->close();
?>