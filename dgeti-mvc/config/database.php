<?php
// ============================================================
// config/database.php — Configuración de base de datos
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ── Datos de la base de datos ──────────────────────────────
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '8080');           // Puerto MySQL estándar
define('DB_NAME',    'sistema_justificantes');
define('DB_USER',    'desarrollo');
define('DB_PASS',    'desarrollo');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST
                 . ";port="   . DB_PORT
                 . ";dbname=" . DB_NAME
                 . ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log("Error de conexión: " . $e->getMessage());

                if (
                    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
                ) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status'  => 'error',
                        'mensaje' => 'Error de conexión a la base de datos',
                    ]);
                    exit;
                }

                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone()    {}
}
