<?php
header("Content-Type: application/json");
require_once 'config.php';

$id = $_GET['id'] ?? 0;
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
    j.comentario_oficial,
    u.nombre,
    u.apellido,
    a.matricula,
    a.nombre_tutor,
    a.correo_tutor,
    a.telefono_tutor,
    g.nombre_grupo as grupo,
    g.grado
FROM justificante j
JOIN alumno a ON j.id_alumno = a.id_alumno
JOIN usuario u ON a.id_usuario = u.id_usuario
JOIN grupo g ON a.id_grupo = g.id_grupo
WHERE j.id_justificante = $id";

$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "No encontrado"]);
    exit;
}

$row = $result->fetch_assoc();

// Calcular días si no está en la base de datos
$dias = $row['dias_solicitados'];
if (!$dias && $row['fecha_inicio_ausencia'] && $row['fecha_fin_ausencia']) {
    $inicio = new DateTime($row['fecha_inicio_ausencia']);
    $fin = new DateTime($row['fecha_fin_ausencia']);
    $dias = $inicio->diff($fin)->days + 1;
}

$response = [
    "success" => true,
    "id" => $row['id_justificante'],
    "folio" => $row['folio'],
    "alumno" => [
        "nombre" => $row['nombre'] . ' ' . $row['apellido'],
        "matricula" => $row['matricula'],
        "grado" => $row['grado'],
        "grupo" => $row['grupo'],
        "tutor" => $row['nombre_tutor'] ?? 'No especificado',
        "email" => $row['correo_tutor'] ?? ''
    ],
    "tipo" => $row['tipo_motivo'],
    "fechaSolicitudFormateada" => date('d/m/Y', strtotime($row['fecha_solicitud'])),
    "fecha_inicio" => $row['fecha_inicio_ausencia'],
    "fecha_fin" => $row['fecha_fin_ausencia'],
    "periodoAusencia" => [
        "inicio" => $row['fecha_inicio_ausencia'],
        "fin" => $row['fecha_fin_ausencia']
    ],
    "diasSolicitados" => $dias,
    "dias" => $dias,
    "motivo" => $row['descripcion_motivo'],
    "descripcion" => $row['descripcion_motivo'],
    "estado" => $row['estado'],
    "comentario_oficial" => $row['comentario_oficial']
];

echo json_encode($response);
?>