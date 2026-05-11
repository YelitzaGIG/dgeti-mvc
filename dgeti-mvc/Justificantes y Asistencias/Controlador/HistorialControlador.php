<?php
// Controlador/HistorialControlador.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../Modelo/HistorialModelo.php';
require_once __DIR__ . '/../Modelo/AsistenciaModelo.php';

// ── VERIFICAR SESIÓN ─────────────────────────────────────────
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

$modelo   = new HistorialModelo();
$modeloAs = new AsistenciaModelo();
$accion   = $_GET['accion'] ?? 'historial';

switch ($accion) {

  // ── VISTA PRINCIPAL ──────────────────────────────────────────
  case 'historial':

    // Solo materias del docente en sesión
    $materias    = $modeloAs->getMateriasPorDocente($id_docente);
    $materia_sel = (int) ($_GET['materia'] ?? ($materias[0]['id'] ?? 0));

    // Grupos filtrados por la materia seleccionada (igual que en panel)
    $grupos    = $modeloAs->getGruposPorMateriaDocente($materia_sel, $id_docente);
    $grupo_sel = (int) ($_GET['grupo'] ?? ($grupos[0]['id'] ?? 0));

    $fecha_ini   = $_GET['fecha_ini'] ?? date('Y-m-01');
    $fecha_fin   = $_GET['fecha_fin'] ?? date('Y-m-d');

    $historial  = $modelo->getHistorial($grupo_sel, $materia_sel, $fecha_ini, $fecha_fin);
    $porcentaje = $modelo->getPorcentajeGrupo($grupo_sel, $materia_sel, $fecha_ini, $fecha_fin);

    $justificantes_pendientes_count = count($modeloAs->getJustificantesPendientes());
    $pagina_actual = 'historial';

    require_once __DIR__ . '/../Vista/historial.php';
    break;

  // ── AJAX: grupos por materia (igual que en panel) ────────────
  case 'get_grupos':
    $id_materia = (int) ($_GET['materia'] ?? 0);
    $grupos = $modeloAs->getGruposPorMateriaDocente($id_materia, $id_docente);
    header('Content-Type: application/json');
    echo json_encode($grupos);
    exit;

  // ── AJAX: datos de historial en JSON ────────────────────────
  case 'get_historial':

    $grupo_sel   = (int) ($_GET['grupo']   ?? 0);
    $materia_sel = (int) ($_GET['materia'] ?? 0);
    $fecha_ini   = $_GET['fecha_ini'] ?? date('Y-m-01');
    $fecha_fin   = $_GET['fecha_fin'] ?? date('Y-m-d');

    $historial  = $modelo->getHistorial($grupo_sel, $materia_sel, $fecha_ini, $fecha_fin);
    $porcentaje = $modelo->getPorcentajeGrupo($grupo_sel, $materia_sel, $fecha_ini, $fecha_fin);

    header('Content-Type: application/json');
    echo json_encode([
      'historial'  => $historial,
      'porcentaje' => $porcentaje,
    ]);
    exit;

  default:
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}