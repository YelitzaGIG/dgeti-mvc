<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$correo = $data['correo'];
$password = $data['password'];

$conn = conexionDB();

$sql = "SELECT * FROM usuario WHERE correo = ?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $correo);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    // verificar contraseña encriptada
    if (password_verify($password, $row['contrasena'])) {

        echo json_encode([
            "success" => true,
            "usuario" => [
                "id" => $row['id_usuario'],
                "nombre" => $row['nombre'],
                "apellido" => $row['apellido'],
                "rol" => $row['id_rol']
            ]
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "msg" => "Contraseña incorrecta"
        ]);
    }

} else {

    echo json_encode([
        "success" => false,
        "msg" => "Usuario no encontrado"
    ]);
}

$stmt->close();
$conn->close();
?>