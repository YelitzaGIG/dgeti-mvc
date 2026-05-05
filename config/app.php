<?php
// ============================================================
// config/app.php — Constantes globales de la aplicación
// ============================================================

define('APP_NAME',    'DGETI — Sistema de Justificantes');
define('APP_VERSION', '1.0.0');
define('APP_URL',     'http://localhost/dgeti-mvc');
define('BASE_PATH',   dirname(__DIR__));
define('SESSION_NAME','dgeti_session');

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Roles permitidos (coinciden con tabla `rol` en la BD)
define('ROLES', ['alumno', 'docente', 'tutor_institucional', 'orientadora', 'jefa_servicios']);

// Alias de roles para UI
define('ROLES_LABEL', [
    'alumno'              => 'Alumno',
    'docente'             => 'Docente',
    'tutor_institucional' => 'Tutor Institucional',
    'orientadora'         => 'Orientadora',
    'jefa_servicios'      => 'Jefa de Servicios',
]);

// Motivos de justificante (coinciden con ENUM tipo_motivo en tabla `justificante`)
define('MOTIVOS', ['Salud', 'Personal', 'Comision']);

// Labels legibles para motivos
define('MOTIVOS_LABEL', [
    'Salud'    => 'Salud',
    'Personal' => 'Personal',
    'Comision' => 'Comisión',
]);

// Estados de justificante (coinciden con ENUM estado en tabla `justificante`)
define('ESTADOS', ['Generado', 'Pendiente', 'Entregado', 'Aprobado', 'Rechazado']);

// Estados de asistencia (coinciden con ENUM estatus en tabla `asistencia`)
define('ESTATUS_ASISTENCIA', ['presente', 'ausente', 'retardo', 'justificada']);

// Estados de permiso personal
define('ESTADOS_PERMISO', ['pendiente', 'aprobado', 'rechazado']);
