<?php
require_once '../config/database.php';

$pendientes = $conn->query("SELECT COUNT(*) as total FROM permiso_personal WHERE estado = 'pendiente'")->fetch_assoc();
$aprobados = $conn->query("SELECT COUNT(*) as total FROM permiso_personal WHERE estado = 'aprobado' AND MONTH(fecha_resolucion) = MONTH(CURDATE()) AND YEAR(fecha_resolucion) = YEAR(CURDATE())")->fetch_assoc();
$rechazados = $conn->query("SELECT COUNT(*) as total FROM permiso_personal WHERE estado = 'rechazado' AND MONTH(fecha_resolucion) = MONTH(CURDATE()) AND YEAR(fecha_resolucion) = YEAR(CURDATE())")->fetch_assoc();
$firmados = $conn->query("SELECT COUNT(*) as total FROM permiso_personal WHERE estado = 'aprobado' AND folio IS NOT NULL")->fetch_assoc();

echo json_encode([
    'pendientes' => $pendientes['total'],
    'aprobados' => $aprobados['total'],
    'rechazados' => $rechazados['total'],
    'firmados' => $firmados['total']
]);

$conn->close();
?>