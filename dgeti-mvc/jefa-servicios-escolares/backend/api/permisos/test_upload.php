<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = '../../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $nombre = time() . '_' . $_FILES['archivo']['name'];
        $ruta = $uploadDir . $nombre;
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta)) {
            echo "Archivo subido: " . $ruta;
        } else {
            echo "Error al mover el archivo";
        }
    } else {
        echo "No se recibió archivo o hubo error: " . ($_FILES['archivo']['error'] ?? 'sin archivo');
    }
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="archivo">
    <button type="submit">Subir</button>
</form>