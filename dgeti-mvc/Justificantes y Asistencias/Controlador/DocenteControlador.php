<?php
// Controlador/DocenteControlador.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../Modelo/AsistenciaModelo.php';

// ── VERIFICAR SESIÓN ACTIVA ──────────────────────────────────
if (empty($_SESSION['id_docente'])) {
    header('Location: ' . BASE_URL . '/index.php?error=sesion');
    exit;
}

$id_docente = (int) $_SESSION['id_docente'];

// Datos del docente para las vistas — vienen de la sesión, no hardcodeados
$docente = [
    'nombre'            => $_SESSION['nombre_docente'] ?? 'Docente',
    'materia_principal' => $_SESSION['especialidad']   ?? '',
    'turno'             => $_SESSION['turno']          ?? '',
];

$modelo = new AsistenciaModelo();
$accion = $_GET['accion'] ?? ($_POST['accion'] ?? 'dashboard');

switch ($accion) {

  // ── DASHBOARD PRINCIPAL ────────────────────────────────────
  case 'dashboard':

    $materias = $modelo->getMateriasPorDocente($id_docente);
    $grupos   = $modelo->getGruposPorDocente($id_docente);

    $justificantes = $modelo->getJustificantesPendientes($id_docente);

    $fecha_hoy = date('Y-m-d');

    // ── STATS GLOBALES: suma de TODAS las materias del docente ──
    $stats_hoy = ['total' => 0, 'presentes' => 0, 'ausentes' => 0, 'retardos' => 0, 'justificadas' => 0];

    foreach ($materias as $mat) {
      $gruposMateria = $modelo->getGruposPorMateriaDocente($mat['id'], $id_docente);
      foreach ($gruposMateria as $grp) {
        // Intentar con fecha de hoy
        $sp = $modelo->getEstadisticas($grp['id'], $mat['id'], $fecha_hoy);
        // Si no hay datos hoy, buscar la última fecha disponible para esa materia
        if ($sp['total'] === 0) {
          $stmtUF = $pdo->prepare("
            SELECT a.fecha FROM asistencia a
            WHERE a.id_materia = ? AND a.id_alumno IN (
              SELECT am.id_alumno FROM alumno_materia am WHERE am.id_materia = ?
            )
            ORDER BY a.fecha DESC LIMIT 1
          ");
          $stmtUF->execute([$mat['id'], $mat['id']]);
          $uf = $stmtUF->fetchColumn();
          if ($uf) {
            $sp = $modelo->getEstadisticas($grp['id'], $mat['id'], $uf);
          }
        }
        $stats_hoy['total']        += $sp['total'];
        $stats_hoy['presentes']    += $sp['presentes'];
        $stats_hoy['ausentes']     += $sp['ausentes'];
        $stats_hoy['retardos']     += $sp['retardos'];
        $stats_hoy['justificadas'] += $sp['justificadas'];
      }
    }

    // Para compatibilidad con dashboard.php
    $grupo_hoy   = !empty($grupos)   ? $grupos[0]['id']   : 0;
    $materia_hoy = !empty($materias) ? $materias[0]['id'] : 0;

    $pagina_actual = 'dashboard';
    $justificantes_pendientes_count = count($justificantes);

    require_once __DIR__ . '/../Vista/dashboard.php';
    break;

  // ── PANEL / TOMAR ASISTENCIA ────────────────────────────────
  case 'panel':

    // Materias del docente en sesión
    $materias    = $modelo->getMateriasPorDocente($id_docente);
    $materia_sel = (int) ($_GET['materia'] ?? ($_POST['materia'] ?? ($materias[0]['id'] ?? 0)));

    // Grupos que corresponden a esa materia (y que son del docente)
    $grupos    = $modelo->getGruposPorMateriaDocente($materia_sel, $id_docente);
    $grupo_sel = (int) ($_GET['grupo'] ?? ($_POST['grupo'] ?? ($grupos[0]['id'] ?? 0)));

    $fecha_sel = $_GET['fecha']   ?? date('Y-m-d');
    $guardado  = $_GET['guardado'] ?? 0;

    $alumnos  = $modelo->getAlumnosConEstatusYJustificante($grupo_sel, $materia_sel, $fecha_sel);
    $stats    = $modelo->getEstadisticas($grupo_sel, $materia_sel, $fecha_sel);

    $justificantes = $modelo->getJustificantesPendientes($id_docente, $materia_sel);
    $justificantes_pendientes_count = count($modelo->getJustificantesPendientes($id_docente));

    $pagina_actual = 'panel';
    require_once __DIR__ . '/../Vista/panel_docente.php';
    break;

  // ── AJAX: grupos de la materia seleccionada (solo del docente) ─
  case 'get_grupos':
    $id_materia = (int) ($_GET['materia'] ?? 0);
    $grupos = $modelo->getGruposPorMateriaDocente($id_materia, $id_docente);
    header('Content-Type: application/json');
    echo json_encode($grupos);
    exit;

  // ── PROCESAR CSV ───────────────────────────────────────────
  case 'subir_csv':

    $materia_ret = $_POST['materia_ret'] ?? ($materias[0]['id'] ?? 1);
    $grupo_ret   = $_POST['grupo_ret']   ?? 1;
    $fecha_ret   = $_POST['fecha_ret']   ?? date('Y-m-d');

    if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] == 0) {
      $archivo = $_FILES['archivo_csv']['tmp_name'];
      $grupo   = $_POST['id_grupo']   ?? $grupo_ret;
      $materia = $_POST['id_materia'] ?? $materia_ret;
      $modelo->procesarCSV($archivo, $grupo, $materia);
    }

    header("Location: DocenteControlador.php?accion=panel&materia=$materia_ret&grupo=$grupo_ret&fecha=$fecha_ret");
    exit;

  // ── AGREGAR ALUMNO INDIVIDUAL ──────────────────────────────
  case 'agregar_alumno':

    $matricula       = trim($_POST['matricula']       ?? '');
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $id_grupo        = (int) ($_POST['id_grupo']   ?? 1);
    $id_materia      = (int) ($_POST['id_materia'] ?? 1);
    $materia_ret     = $_POST['materia_ret'] ?? $id_materia;
    $grupo_ret       = $_POST['grupo_ret']   ?? $id_grupo;
    $fecha_ret       = $_POST['fecha_ret']   ?? date('Y-m-d');

    if (!empty($matricula) && !empty($nombre_completo)) {
      $modelo->agregarAlumno($matricula, $nombre_completo, $id_grupo, $id_materia);
    }

    header("Location: DocenteControlador.php?accion=panel&materia=$materia_ret&grupo=$grupo_ret&fecha=$fecha_ret&guardado=alumno");
    exit;

  // ── GUARDAR ASISTENCIA ─────────────────────────────────────
  case 'guardar_asistencia':

    $grupo   = (int) ($_POST['grupo']   ?? 0);
    $materia = (int) ($_POST['materia'] ?? 0);
    $fecha   = $_POST['fecha']   ?? date('Y-m-d');
    $estatus = $_POST['estatus'] ?? [];

    if ($grupo && $materia && !empty($estatus)) {
      // Se pasa el id_docente real de la sesión
      $modelo->guardarAsistencia($grupo, $fecha, $estatus, $materia, $id_docente);
    }

    $cerrar = $_POST['cerrar'] ?? 0;
    $redir  = "DocenteControlador.php?accion=panel&grupo=$grupo&materia=$materia&fecha=$fecha";
    header('Location: ' . $redir . ($cerrar ? '' : '&guardado=1'));
    exit;

  default:
    header('Location: DocenteControlador.php?accion=dashboard');
    exit;
}