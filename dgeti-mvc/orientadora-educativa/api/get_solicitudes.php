<?php
header("Content-Type: application/json");
require_once 'config.php';

$conn = conexionDB();

$sql = "SELECT 
    j.id_justificante,
    j.folio,
    j.tipo_motivo,
    j.descripcion_motivo,
    j.dias_solicitados,
    j.fecha_inicio,
    j.fecha_fin,
    j.estado,
    j.fecha_solicitud,
    u.nombre,
    u.apellido,
    a.matricula,
    a.nombre_tutor,
    a.correo_tutor,
    g.nombre_grupo AS grupo,
    g.grado
FROM justificante j
LEFT JOIN alumno a ON j.id_alumno = a.id_alumno
LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
LEFT JOIN grupo g ON a.id_grupo = g.id_grupo
ORDER BY j.fecha_solicitud DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>