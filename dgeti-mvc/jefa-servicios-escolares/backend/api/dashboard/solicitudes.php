<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$estado = $_GET['estado'] ?? '';
$search = $_GET['search'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

$sql = "SELECT p.*, 
               u.nombre, 
               u.apellido, 
               u.correo
        FROM permiso_personal p 
        JOIN usuario u ON p.id_usuario = u.id_usuario 
        WHERE 1=1";

if ($estado && $estado != 'todos') {
    $sql .= " AND p.estado = '$estado'";
}
if ($search) {
    $sql .= " AND (u.nombre LIKE '%$search%' OR u.apellido LIKE '%$search%' OR p.folio LIKE '%$search%')";
}
if ($fecha_desde) {
    $sql .= " AND DATE(p.fecha_solicitud) >= '$fecha_desde'";
}
if ($fecha_hasta) {
    $sql .= " AND DATE(p.fecha_solicitud) <= '$fecha_hasta'";
}

$sql .= " ORDER BY p.fecha_solicitud DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => $conn->error]);
    $conn->close();
    exit;
}

$solicitudes = [];
while ($row = $result->fetch_assoc()) {
    $solicitudes[] = $row;
}

echo json_encode($solicitudes);
$conn->close();
?>