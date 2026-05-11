<?php
// Controlador/UsuarioControlador.php
// Registra nuevos docentes en la BD NOVA_AJ199.
//
// Tablas que toca:
//   usuario  → id_rol = 2 (docente), contrasena_cambiada = 0
//   docente  → vincula id_usuario + especialidad

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';

$accion = $_POST['accion'] ?? '';

if ($accion === 'registrar') {

    $nombre       = trim($_POST['nombre']       ?? '');
    $apellido     = trim($_POST['apellido']     ?? '');
    $correo       = trim($_POST['correo']       ?? '');
    $password     = trim($_POST['password']     ?? '');
    $password2    = trim($_POST['password2']    ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');

    // ── Validaciones básicas ──────────────────────────────
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($password)) {
        header('Location: ' . BASE_URL . '/index.php?error=campos_vacios');
        exit;
    }

    if ($password !== $password2) {
        header('Location: ' . BASE_URL . '/index.php?error=passwords_no_coinciden');
        exit;
    }

    // ── Verificar correo duplicado ────────────────────────
    $check = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ? LIMIT 1");
    $check->execute([$correo]);
    if ($check->fetch()) {
        header('Location: ' . BASE_URL . '/index.php?error=correo_duplicado');
        exit;
    }

    // ── Obtener id_rol del rol 'docente' ──────────────────
    // (En NOVA_AJ199 el rol docente tiene id_rol = 2, pero lo
    //  consultamos dinámicamente por si el admin lo cambió)
    $stmtRol = $pdo->prepare("SELECT id_rol FROM rol WHERE nombre_rol = 'docente' LIMIT 1");
    $stmtRol->execute();
    $rolRow  = $stmtRol->fetch();
    $id_rol  = $rolRow ? (int) $rolRow['id_rol'] : 2;

    // ── Insertar en tabla usuario ─────────────────────────
    $hash  = password_hash($password, PASSWORD_DEFAULT);
    $stmtU = $pdo->prepare("
        INSERT INTO usuario
            (nombre, apellido, correo, contrasena, id_rol, contrasena_cambiada, activo)
        VALUES (?, ?, ?, ?, ?, 0, 1)
    ");
    $stmtU->execute([$nombre, $apellido, $correo, $hash, $id_rol]);
    $id_usuario = (int) $pdo->lastInsertId();

    // ── Insertar en tabla docente ─────────────────────────
    $stmtD = $pdo->prepare("
        INSERT INTO docente (id_usuario, especialidad)
        VALUES (?, ?)
    ");
    $stmtD->execute([$id_usuario, $especialidad]);

    header('Location: ' . BASE_URL . '/index.php?ok=registrado');
    exit;
}

// Sin POST válido → regresar al inicio
header('Location: ' . BASE_URL . '/index.php');
exit;