<?php
// Controlador/LoginControlador.php
// Maneja el inicio y cierre de sesión del docente.

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';

$accion = $_GET['accion'] ?? ($_POST['accion'] ?? 'login');

switch ($accion) {

  // ── PROCESAR FORMULARIO DE LOGIN ────────────────────────────
  case 'login':
  default:

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: ' . BASE_URL . '/index.php');
      exit;
    }

    $correo   = trim($_POST['correo']   ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($correo) || empty($password)) {
      header('Location: ' . BASE_URL . '/index.php?error=credenciales');
      exit;
    }

    // Buscar usuario activo con rol docente
    $stmt = $pdo->prepare("
      SELECT
        u.id_usuario,
        u.nombre,
        u.apellido,
        u.contrasena,
        d.id_docente,
        d.especialidad
      FROM usuario u
      JOIN docente d ON d.id_usuario = u.id_usuario
      WHERE u.correo = ?
        AND u.activo = 1
      LIMIT 1
    ");
    $stmt->execute([$correo]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($password, $row['contrasena'])) {
      header('Location: ' . BASE_URL . '/index.php?error=credenciales');
      exit;
    }

    // Obtener el turno del grupo donde imparte (tomamos el primero que aparezca)
    $stmtTurno = $pdo->prepare("
      SELECT g.turno
      FROM grupo g
      JOIN materia m ON m.id_grupo = g.id_grupo
      WHERE m.id_docente = ?
      LIMIT 1
    ");
    $stmtTurno->execute([$row['id_docente']]);
    $turno = $stmtTurno->fetchColumn() ?: '';

    // Guardar datos en sesión
    $_SESSION['id_docente']     = $row['id_docente'];
    $_SESSION['nombre_docente'] = $row['nombre'] . ' ' . $row['apellido'];
    $_SESSION['especialidad']   = $row['especialidad'] ?? '';
    $_SESSION['turno']          = $turno ? 'Turno ' . $turno : '';

    header('Location: ' . BASE_URL . '/Controlador/DocenteControlador.php?accion=dashboard');
    exit;

  // ── CERRAR SESIÓN ────────────────────────────────────────────
  case 'logout':
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
