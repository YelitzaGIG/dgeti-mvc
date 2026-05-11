<?php
require_once 'config.php';

$conn = conexionDB();
$result = $conn->query("SELECT j.folio, j.estado, j.dias_autorizados, j.fecha_solicitud,
        CONCAT(a.nombre, ' ', a.apellido_paterno) as alumno_nombre,
        a.matricula, a.grado, a.grupo
        FROM justificante j
        JOIN alumno a ON j.id_alumno = a.id_alumno
        WHERE j.estado = 'aprobado'
        ORDER BY j.fecha_solicitud DESC");

$historial = [];
while ($row = $result->fetch_assoc()) {
    $historial[] = $row;
}

echo json_encode($historial);
?>