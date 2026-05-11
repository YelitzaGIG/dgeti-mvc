<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
error_reporting(E_ALL);
ini_set('display_errors', 1);

function conexionDB() {

    $host = "localhost";
    $user = "root";
    $pass = ""; // en XAMPP normalmente va vacío
    $db = "NOVA_AJ199";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die(json_encode([
            "success" => false,
            "error" => "Error de conexión: " . $conn->connect_error
        ]));
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}
?>