<?php
// Modelo/ExportarModelo.php
require_once __DIR__ . '/../config/conexion.php';

class ExportarModelo {

  private $pdo;

  public function __construct() {
    global $pdo;
    $this->pdo = $pdo;
  }

  // ── CATÁLOGOS ───────────────────────────────────────────────
  public function getGrupos(): array {
    $stmt = $this->pdo->query(
      "SELECT id_grupo AS id, nombre_grupo AS nombre FROM grupo ORDER BY nombre_grupo"
    );
    return $stmt->fetchAll();
  }

  public function getMaterias(): array {
    $stmt = $this->pdo->query(
      "SELECT id_materia AS id, nombre FROM materia ORDER BY nombre"
    );
    return $stmt->fetchAll();
  }

  // ── DATOS PARA EXPORTAR ────────────────────────────────────
  public function getDatosExportar(int $grupo, int $materia, string $fecha_ini, string $fecha_fin): array {

    $sql = "
      SELECT
        al.matricula,
        CONCAT(u.nombre, ' ', u.apellido) AS alumno,
        a.fecha,
        a.estatus
      FROM alumno al
      JOIN usuario u ON u.id_usuario = al.id_usuario
      LEFT JOIN asistencia a
        ON a.id_alumno  = al.id_alumno
        AND a.id_materia = ?
        AND a.fecha BETWEEN ? AND ?
      WHERE al.id_grupo = ?
      ORDER BY u.nombre, a.fecha
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$materia, $fecha_ini, $fecha_fin, $grupo]);
    return $stmt->fetchAll();
  }

  // ── NOMBRE DEL GRUPO ───────────────────────────────────────
  public function getNombreGrupo(int $grupo): string {
    $stmt = $this->pdo->prepare("SELECT nombre_grupo FROM grupo WHERE id_grupo = ?");
    $stmt->execute([$grupo]);
    return $stmt->fetchColumn() ?: "Grupo$grupo";
  }

  // ── NOMBRE DE LA MATERIA ────────────────────────────────────
  public function getNombreMateria(int $materia): string {
    $stmt = $this->pdo->prepare("SELECT nombre FROM materia WHERE id_materia = ?");
    $stmt->execute([$materia]);
    return $stmt->fetchColumn() ?: "Materia$materia";
  }

  // ── GENERAR CSV EN MEMORIA (string) ───────────────────────
  public function generarCSV(array $datos, string $nombreGrupo, string $nombreMateria): string {

    // Pivotear datos
    $alumnos = [];
    $fechas  = [];

    foreach ($datos as $r) {
      $key = $r['matricula'];
      if (!isset($alumnos[$key])) {
        $alumnos[$key] = ['matricula' => $r['matricula'], 'nombre' => $r['alumno'], 'dias' => []];
      }
      if ($r['fecha']) {
        $alumnos[$key]['dias'][$r['fecha']] = $r['estatus'];
        $fechas[$r['fecha']] = true;
      }
    }

    ksort($fechas);
    $fechasCols = array_keys($fechas);

    // Construir CSV
    $out = fopen('php://temp', 'r+');

    // Encabezado
    $header = ['Matrícula', 'Alumno'];
    foreach ($fechasCols as $f) {
      $header[] = date('d/m/Y', strtotime($f));
    }
    $header[] = 'Presentes';
    $header[] = 'Ausentes';
    $header[] = 'Retardos';
    $header[] = 'Justificadas';
    $header[] = '% Asistencia';
    fputcsv($out, $header);

    // Filas
    foreach ($alumnos as $a) {
      $row = [$a['matricula'], $a['nombre']];
      $p = $au = $re = $ju = 0;

      foreach ($fechasCols as $f) {
        $est = $a['dias'][$f] ?? '-';
        $row[] = ucfirst($est);
        if ($est === 'presente')    $p++;
        elseif ($est === 'ausente') $au++;
        elseif ($est === 'retardo') $re++;
        elseif ($est === 'justificada') $ju++;
      }

      $total = $p + $au + $re + $ju;
      $pct   = $total > 0 ? round(($p / $total) * 100, 1) : 0;
      $row[] = $p;
      $row[] = $au;
      $row[] = $re;
      $row[] = $ju;
      $row[] = "$pct%";
      fputcsv($out, $row);
    }

    rewind($out);
    $csv = stream_get_contents($out);
    fclose($out);

    return $csv;
  }

  // ── GUARDAR CSV EN uploads/excel/{id_docente}/ ────────────
  public function guardarCSV(string $csv, string $nombreArchivo, int $id_docente): string {
    $dir = __DIR__ . '/../uploads/excel/' . $id_docente . '/';
    if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
    }
    $ruta = $dir . $nombreArchivo;
    file_put_contents($ruta, "\xEF\xBB\xBF" . $csv); // BOM UTF-8 para Excel
    return $ruta;
  }

  // ── LISTAR ARCHIVOS GENERADOS (solo del docente) ───────────
  public function listarArchivos(int $id_docente): array {
    $dir = __DIR__ . '/../uploads/excel/' . $id_docente . '/';
    if (!is_dir($dir)) return [];

    $archivos = glob($dir . '*.csv');
    $lista = [];

    foreach ($archivos as $ruta) {
      $lista[] = [
        'nombre' => basename($ruta),
        'tamano' => filesize($ruta),
        'fecha'  => date('d/m/Y H:i', filemtime($ruta)),
      ];
    }

    // Ordenar por fecha desc
    usort($lista, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));

    return $lista;
  }

  // ── ELIMINAR ARCHIVO ───────────────────────────────────────
  public function eliminarArchivo(string $nombre): bool {
    $ruta = __DIR__ . '/../uploads/excel/' . basename($nombre);
    if (file_exists($ruta)) {
      return unlink($ruta);
    }
    return false;
  }

  // ── ELIMINAR ARCHIVO DEL DOCENTE ───────────────────────────
  public function eliminarArchivoDocente(string $nombre, int $id_docente): bool {
    $ruta = __DIR__ . '/../uploads/excel/' . $id_docente . '/' . basename($nombre);
    if (file_exists($ruta)) {
      return unlink($ruta);
    }
    return false;
  }
}