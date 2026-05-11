<?php
// Modelo/HistorialModelo.php
require_once __DIR__ . '/../config/conexion.php';

class HistorialModelo {

  private $pdo;

  public function __construct() {
    global $pdo;
    $this->pdo = $pdo;
  }

  // ── GRUPOS ─────────────────────────────────────────────────
  public function getGrupos(): array {
    $stmt = $this->pdo->query(
      "SELECT id_grupo AS id, nombre_grupo AS nombre FROM grupo ORDER BY nombre_grupo"
    );
    return $stmt->fetchAll();
  }

  // ── MATERIAS ───────────────────────────────────────────────
  public function getMaterias(): array {
    $stmt = $this->pdo->query(
      "SELECT id_materia AS id, nombre FROM materia ORDER BY nombre"
    );
    return $stmt->fetchAll();
  }

  // ── HISTORIAL COMPLETO DE UN GRUPO / MATERIA / RANGO ───────
  public function getHistorial(int $grupo, int $materia, string $fecha_ini, string $fecha_fin): array {

    $sql = "
      SELECT
        al.id_alumno   AS id,
        al.matricula,
        u.nombre       AS alumno,
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
    $rows = $stmt->fetchAll();

    // Pivotear: alumno => [ fecha => estatus ]
    $pivot = [];
    $fechas = [];

    foreach ($rows as $r) {
      $pivot[$r['id']]['matricula'] = $r['matricula'];
      $pivot[$r['id']]['alumno']    = $r['alumno'];
      if ($r['fecha']) {
        $pivot[$r['id']]['asistencias'][$r['fecha']] = $r['estatus'];
        $fechas[$r['fecha']] = true;
      }
    }

    ksort($fechas);

    return [
      'alumnos' => $pivot,
      'fechas'  => array_keys($fechas),
    ];
  }

  // ── RESUMEN ESTADÍSTICO POR ALUMNO ─────────────────────────
  public function getResumenAlumno(int $alumno, int $materia, string $fecha_ini, string $fecha_fin): array {

    $sql = "
      SELECT
        estatus,
        COUNT(*) AS total
      FROM asistencia
      WHERE id_alumno  = ?
        AND id_materia = ?
        AND fecha BETWEEN ? AND ?
      GROUP BY estatus
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$alumno, $materia, $fecha_ini, $fecha_fin]);

    $resumen = ['presente' => 0, 'ausente' => 0, 'retardo' => 0, 'justificada' => 0];

    foreach ($stmt->fetchAll() as $r) {
      $resumen[$r['estatus']] = (int) $r['total'];
    }

    return $resumen;
  }

  // ── FECHAS CON REGISTRO PARA UN GRUPO/MATERIA ──────────────
  public function getFechasRegistradas(int $grupo, int $materia): array {

    $sql = "
      SELECT DISTINCT a.fecha
      FROM asistencia a
      JOIN alumno al ON al.id_alumno = a.id_alumno
      WHERE al.id_grupo   = ?
        AND a.id_materia  = ?
      ORDER BY a.fecha DESC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$grupo, $materia]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  // ── PORCENTAJE ASISTENCIA POR GRUPO ─────────────────────────
  public function getPorcentajeGrupo(int $grupo, int $materia, string $fecha_ini, string $fecha_fin): float {

    $sql = "
      SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN estatus = 'presente' THEN 1 ELSE 0 END) AS presentes
      FROM asistencia a
      JOIN alumno al ON al.id_alumno = a.id_alumno
      WHERE al.id_grupo  = ?
        AND a.id_materia = ?
        AND a.fecha BETWEEN ? AND ?
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$grupo, $materia, $fecha_ini, $fecha_fin]);
    $r = $stmt->fetch();

    if (!$r || $r['total'] == 0) return 0.0;

    return round(($r['presentes'] / $r['total']) * 100, 1);
  }
}

