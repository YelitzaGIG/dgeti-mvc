<?php
header("Content-Type: application/json");
require_once 'config.php';

$conn = conexionDB();

$pendientes = 0;
$aprobados = 0;
$rechazados = 0;

$r1 = $conn->query("SELECT COUNT(*) as total FROM justificante WHERE estado = 'Pendiente'");
if ($r1) $pendientes = $r1->fetch_assoc()['total'];

$r2 = $conn->query("SELECT COUNT(*) as total FROM justificante WHERE estado = 'Aprobado'");
if ($r2) $aprobados = $r2->fetch_assoc()['total'];

$r3 = $conn->query("SELECT COUNT(*) as total FROM justificante WHERE estado = 'Rechazado'");
if ($r3) $rechazados = $r3->fetch_assoc()['total'];

echo json_encode([
    'pendientes' => (int)$pendientes,
    'aprobados' => (int)$aprobados,
    'rechazados' => (int)$rechazados,
    'total' => (int)($pendientes + $aprobados + $rechazados)
]);
?>