<?php
// Controlador/JustificanteControlador.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
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

$modelo = new AsistenciaModelo();
$accion = $_GET['accion'] ?? ($_POST['accion'] ?? 'justificantes');

switch ($accion) {

  // ── LISTA DE JUSTIFICANTES ───────────────────────────────────
  case 'justificantes':

    $filtro_estado  = $_GET['estado']   ?? 'todos';
    $filtro_materia = (int) ($_GET['materia'] ?? 0);

    // Materias del docente para el selector de filtro
    $mis_materias = $modelo->getMateriasPorDocente($id_docente);

    // Justificantes filtrados según la materia seleccionada
    if ($filtro_materia > 0) {
      $justificantes = $modelo->getJustificantesPorDocenteYMateria(
        $id_docente,
        $filtro_materia,
        $filtro_estado
      );
    } else {
      $justificantes = $modelo->getJustificantesPorDocente(
        $id_docente,
        $filtro_estado
      );
    }

    $pendientes_count = count(array_filter($justificantes, fn($j) => $j['estado'] === 'Pendiente'));
    $justificantes_pendientes_count = count($modelo->getJustificantesPendientes($id_docente));
    $pagina_actual = 'justificantes';

    require_once __DIR__ . '/../Vista/justificantes.php';
    break;

  // ── VER DETALLE ──────────────────────────────────────────────
  case 'ver':

    $id = (int) ($_GET['id'] ?? 0);
    $justificante = $modelo->getJustificanteDetalle($id);

    if (!$justificante) {
      header('Location: JustificanteControlador.php?accion=justificantes');
      exit;
    }

    $justificantes_pendientes_count = count($modelo->getJustificantesPendientes($id_docente));
    $pagina_actual = 'justificantes';

    require_once __DIR__ . '/../Vista/detalle_justificante.php';
    break;

  default:
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}