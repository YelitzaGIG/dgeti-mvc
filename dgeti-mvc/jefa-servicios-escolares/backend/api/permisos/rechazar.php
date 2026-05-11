<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? 0;
$observaciones = $data['observaciones'] ?? '';
$id_jefa_servicios = $data['id_jefa_servicios'] ?? 1;

if (!$id) {
    echo json_encode(['error' => 'ID de permiso no proporcionado']);
    exit;
}

$sql = "UPDATE permiso_personal SET 
        estado = 'rechazado',
        observaciones = '$observaciones',
        id_jefa_servicios = $id_jefa_servicios,
        fecha_resolucion = NOW()
        WHERE id_permiso = $id AND estado = 'pendiente'";

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Permiso rechazado correctamente'
        ]);
    } else {
        echo json_encode(['error' => 'El permiso ya fue procesado o no existe']);
    }
} else {
    echo json_encode(['error' => 'Error al rechazar: ' . $conn->error]);
}

$conn->close();
?>