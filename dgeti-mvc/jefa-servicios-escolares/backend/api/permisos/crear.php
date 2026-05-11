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

// Verificar qué datos están llegando
error_log("=== POST recibido ===");
error_log(print_r($_POST, true));
error_log("=== FILES recibido ===");
error_log(print_r($_FILES, true));

$response = ['post' => $_POST, 'files' => $_FILES, 'server' => $_SERVER['REQUEST_METHOD']];

$id_usuario = $_POST['id_usuario'] ?? 0;
$tipo_personal = $_POST['tipo_personal'] ?? '';
$tipo_permiso = $_POST['tipo_permiso'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';

if (!$id_usuario || !$tipo_personal || !$tipo_permiso || !$motivo || !$fecha_inicio || !$fecha_fin) {
    echo json_encode(['error' => 'Faltan campos: ' . print_r($_POST, true)]);
    exit;
}

$archivo_url = null;

// Procesar archivo
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../../uploads/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
    $nombreArchivo = 'permiso_' . time() . '_' . uniqid() . '.' . $extension;
    $rutaArchivo = $uploadDir . $nombreArchivo;
    
    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaArchivo)) {
        $archivo_url = 'uploads/' . $nombreArchivo;
        error_log("Archivo guardado en: " . $archivo_url);
    } else {
        error_log("Error al mover archivo. Temp: " . $_FILES['archivo']['tmp_name'] . " Destino: " . $rutaArchivo);
    }
} else {
    error_log("No se recibió archivo o hubo error. Error code: " . ($_FILES['archivo']['error'] ?? 'sin archivo'));
}

$motivo = $conn->real_escape_string($motivo);
$archivo_sql = $archivo_url ? "'$archivo_url'" : "NULL";

$sql = "INSERT INTO permiso_personal (id_usuario, tipo_personal, tipo_permiso, motivo, fecha_inicio, fecha_fin, archivo_url, estado, fecha_solicitud) 
        VALUES ($id_usuario, '$tipo_personal', '$tipo_permiso', '$motivo', '$fecha_inicio', '$fecha_fin', $archivo_sql, 'pendiente', NOW())";

if ($conn->query($sql)) {
    echo json_encode([
        'success' => true,
        'message' => 'Permiso creado',
        'id_permiso' => $conn->insert_id,
        'archivo_url' => $archivo_url
    ]);
} else {
    echo json_encode(['error' => 'Error SQL: ' . $conn->error]);
}

$conn->close();
?>