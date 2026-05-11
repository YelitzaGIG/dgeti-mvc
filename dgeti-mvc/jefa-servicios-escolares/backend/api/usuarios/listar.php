<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$estado = $_GET['estado'] ?? 'aprobado';
$search = $_GET['search'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$rol = $_GET['rol'] ?? '';

$sql = "SELECT p.*, 
               u.nombre, 
               u.apellido, 
               u.correo,
               CASE 
                   WHEN p.tipo_personal = 'docente' THEN 'docente'
                   ELSE 'administrativo'
               END as rol_nombre
        FROM permiso_personal p
        JOIN usuario u ON p.id_usuario = u.id_usuario
        WHERE p.estado = 'aprobado'";

if ($search) {
    $sql .= " AND (u.nombre LIKE '%$search%' OR u.apellido LIKE '%$search%' OR p.folio LIKE '%$search%')";
}
if ($fecha_desde) {
    $sql .= " AND DATE(p.fecha_resolucion) >= '$fecha_desde'";
}
if ($fecha_hasta) {
    $sql .= " AND DATE(p.fecha_resolucion) <= '$fecha_hasta'";
}
if ($rol && $rol != 'todos') {
    $sql .= " AND p.tipo_personal = '$rol'";
}

$sql .= " ORDER BY p.fecha_resolucion DESC";

$result = $conn->query($sql);
$permisos = [];

while ($row = $result->fetch_assoc()) {
    $permisos[] = $row;
}

echo json_encode($permisos);
$conn->close();
?>