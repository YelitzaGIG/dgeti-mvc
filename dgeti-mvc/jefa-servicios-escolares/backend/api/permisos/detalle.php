<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo json_encode(['error' => 'ID de permiso no proporcionado']);
    exit;
}

$sql = "SELECT p.*, 
               u.nombre, 
               u.apellido, 
               u.correo,
               COALESCE(d.especialidad, 'No especificado') as departamento,
               r.nombre_rol
        FROM permiso_personal p 
        JOIN usuario u ON p.id_usuario = u.id_usuario
        LEFT JOIN docente d ON u.id_usuario = d.id_usuario
        JOIN rol r ON u.id_rol = r.id_rol
        WHERE p.id_permiso = $id";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => $conn->error]);
    $conn->close();
    exit;
}

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Permiso no encontrado']);
    $conn->close();
    exit;
}

$permiso = $result->fetch_assoc();
echo json_encode($permiso);

$conn->close();
?>