<?php
// Controlador/ExportarControlador.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../Modelo/ExportarModelo.php';
require_once __DIR__ . '/../Modelo/AsistenciaModelo.php';

if (empty($_SESSION['id_docente'])) {
    header('Location: ' . BASE_URL . '/index.php?error=sesion');
    exit;
}

$id_docente = (int) $_SESSION['id_docente'];

$docente = [
    'nombre'            => $_SESSION['nombre_docente'] ?? 'Docente',
    'materia_principal' => $_SESSION['especialidad']   ?? '',
    'turno'             => $_SESSION['turno']          ?? '',
];

$modelo   = new ExportarModelo();
$modeloAs = new AsistenciaModelo();
$accion   = $_GET['accion'] ?? ($_POST['accion'] ?? 'exportar');

switch ($accion) {

  case 'exportar':
    $materias    = $modeloAs->getMateriasPorDocente($id_docente);
    $materia_sel = (int) ($_GET['materia'] ?? ($materias[0]['id'] ?? 0));
    $grupos      = $modeloAs->getGruposPorMateriaDocente($materia_sel, $id_docente);
    $grupo_sel   = (int) ($_GET['grupo'] ?? ($grupos[0]['id'] ?? 0));
    $fecha_ini   = $_GET['fecha_ini'] ?? date('Y-m-01');
    $fecha_fin   = $_GET['fecha_fin'] ?? date('Y-m-d');

    $archivos_generados = $modelo->listarArchivos($id_docente);
    $justificantes_pendientes_count = count($modeloAs->getJustificantesPendientes());
    $pagina_actual = 'exportar';

    require_once __DIR__ . '/../Vista/exportar.php';
    break;

  case 'get_grupos':
    $id_materia = (int) ($_GET['materia'] ?? 0);
    $grupos = $modeloAs->getGruposPorMateriaDocente($id_materia, $id_docente);
    header('Content-Type: application/json');
    echo json_encode($grupos);
    exit;

  // Genera, guarda en disco y devuelve JSON con nombre del archivo
  case 'generar_ajax':
    $grupo_sel   = (int) ($_POST['grupo']   ?? 0);
    $materia_sel = (int) ($_POST['materia'] ?? 0);
    $fecha_ini   = $_POST['fecha_ini'] ?? date('Y-m-01');
    $fecha_fin   = $_POST['fecha_fin'] ?? date('Y-m-d');

    $datos         = $modelo->getDatosExportar($grupo_sel, $materia_sel, $fecha_ini, $fecha_fin);
    $nombreGrupo   = $modelo->getNombreGrupo($grupo_sel);
    $nombreMateria = $modelo->getNombreMateria($materia_sel);
    $csv           = $modelo->generarCSV($datos, $nombreGrupo, $nombreMateria);

    $nombreArchivo = 'Asistencias_' .
                     preg_replace('/\s+/', '_', $nombreGrupo)   . '_' .
                     preg_replace('/\s+/', '_', $nombreMateria) . '_' .
                     str_replace('-', '', $fecha_ini) . '_' .
                     str_replace('-', '', $fecha_fin) . '.csv';

    $modelo->guardarCSV($csv, $nombreArchivo, $id_docente);

    header('Content-Type: application/json');
    echo json_encode([
      'ok'       => true,
      'archivo'  => $nombreArchivo,
      'lista'    => $modelo->listarArchivos($id_docente),
    ]);
    exit;

  // Sirve el archivo para descarga
  case 'descargar_guardado':
    $nombre = basename($_GET['archivo'] ?? '');
    $ruta   = __DIR__ . '/../uploads/excel/' . $id_docente . '/' . $nombre;

    if (!$nombre || !file_exists($ruta)) {
      header('Location: ExportarControlador.php?accion=exportar&error=no_encontrado');
      exit;
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombre . '"');
    header('Content-Length: ' . filesize($ruta));
    readfile($ruta);
    exit;

  case 'eliminar':
    $nombre = basename($_GET['archivo'] ?? '');
    $modelo->eliminarArchivoDocente($nombre, $id_docente);
    header('Location: ExportarControlador.php?accion=exportar&msg=eliminado');
    exit;

  default:
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}