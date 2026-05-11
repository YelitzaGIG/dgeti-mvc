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

if (!$id) {
    echo json_encode(['error' => 'ID de permiso no proporcionado']);
    exit;
}

// Solo eliminar si está pendiente
$sql = "DELETE FROM permiso_personal WHERE id_permiso = $id AND estado = 'pendiente'";

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Permiso eliminado']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar. Solo permisos pendientes pueden eliminarse.']);
    }
} else {
    echo json_encode(['error' => 'Error: ' . $conn->error]);
}

$conn->close();
?>