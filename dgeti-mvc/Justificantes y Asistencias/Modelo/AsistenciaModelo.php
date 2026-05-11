<?php
require_once __DIR__ . '/../config/conexion.php';

class AsistenciaModelo {

  private $pdo;

  public function __construct() {
    global $pdo;
    $this->pdo = $pdo;
  }

  // ── CATÁLOGOS ───────────────────────────

  public function getMaterias(): array {
    $stmt = $this->pdo->query("SELECT id_materia AS id, nombre FROM materia ORDER BY nombre");
    return $stmt->fetchAll();
  }

  public function getGrupos(): array {
    $stmt = $this->pdo->query("SELECT id_grupo AS id, nombre_grupo AS nombre FROM grupo ORDER BY nombre_grupo");
    return $stmt->fetchAll();
  }

  public function getFechasRecientes(int $dias = 7): array {
    $fechas = [];
    for ($i = 0; $i < $dias; $i++) {
      $ts = strtotime("-$i days");
      $fechas[] = [
        'valor' => date('Y-m-d', $ts),
        'etiqueta' => $i === 0 ? 'Hoy — ' . date('d M Y', $ts) : date('d M Y', $ts)
      ];
    }
    return $fechas;
  }

  // ── ALUMNOS CON ESTATUS ─────────────────

  public function getAlumnosConEstatus(int $grupo, int $materia, string $fecha): array {

    $sql = "
      SELECT al.id_alumno AS id,
             al.matricula,
             CONCAT(u.nombre, ' ', u.apellido) AS nombre,
             COALESCE(a.estatus, 'pendiente') AS estatus
      FROM alumno al
      JOIN usuario u         ON u.id_usuario  = al.id_usuario
      JOIN alumno_materia am ON am.id_alumno  = al.id_alumno
                             AND am.id_materia = ?
      LEFT JOIN asistencia a
        ON a.id_alumno  = al.id_alumno
        AND a.id_materia = ?
        AND a.fecha      = ?
      WHERE al.id_grupo = ?
      ORDER BY u.nombre
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$materia, $materia, $fecha, $grupo]);

    return $stmt->fetchAll();
  }

    // ── ESTADÍSTICAS ────────────────────────

  public function getEstadisticas(int $grupo, int $materia, string $fecha): array {

    $alumnos = $this->getAlumnosConEstatus($grupo, $materia, $fecha);

    $stats = ['total'=>0,'presentes'=>0,'ausentes'=>0,'retardos'=>0,'justificadas'=>0];

    foreach ($alumnos as $a) {
      $stats['total']++;

      if ($a['estatus'] === 'presente') $stats['presentes']++;
      elseif ($a['estatus'] === 'ausente') $stats['ausentes']++;
      elseif ($a['estatus'] === 'retardo') $stats['retardos']++;
      elseif ($a['estatus'] === 'justificada') $stats['justificadas']++;
    }

    return $stats;
  }

  // ── GUARDAR ASISTENCIA ─────────────────

  public function guardarAsistencia(int $grupo, string $fecha, array $estatus, int $id_materia, int $id_docente): void {

    $sql = "
      INSERT INTO asistencia (id_alumno, id_materia, id_docente, fecha, estatus)
      VALUES (?, ?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE estatus = VALUES(estatus)
    ";

    $stmt = $this->pdo->prepare($sql);

    foreach ($estatus as $alumno_id => $est) {
      // Saltar pendientes — no son un valor válido en el ENUM
      if (strtolower($est) === 'pendiente') {
        continue;
      }
      $stmt->execute([$alumno_id, $id_materia, $id_docente, $fecha, $est]);
    }
  }

  // ── JUSTIFICANTES ───────────────────────

  public function getJustificantesPendientes(int $id_docente = 0, int $id_materia = 0): array {

    $where = ["j.estado = 'Pendiente'"];
    $params = [];

    if ($id_docente > 0) {
      $where[] = 'm.id_docente = ?';
      $params[] = $id_docente;
    }

    if ($id_materia > 0) {
      $where[] = 'm.id_materia = ?';
      $params[] = $id_materia;
    }

    $where_sql = implode(' AND ', $where);

    $sql = "
      SELECT j.id_justificante AS id,
             u.nombre AS alumno,
             j.fecha_solicitud AS fecha,
             j.descripcion_motivo AS motivo,
             j.estado
      FROM justificante j
      JOIN alumno al ON al.id_alumno = j.id_alumno
      JOIN usuario u ON u.id_usuario = al.id_usuario
      LEFT JOIN asistencia a ON a.id_asistencia = j.id_asistencia
      LEFT JOIN materia m ON m.id_materia = a.id_materia
      WHERE $where_sql
      ORDER BY j.fecha_solicitud ASC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public function getJustificante(int $id): ?array {

    $stmt = $this->pdo->prepare("
      SELECT j.*, u.nombre AS alumno
      FROM justificante j
      JOIN alumno al ON al.id_alumno = j.id_alumno
      JOIN usuario u ON u.id_usuario = al.id_usuario
      WHERE j.id_justificante = ?
    ");

    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
  }

  public function resolverJustificante(int $id, string $resolucion): void {

    $stmt = $this->pdo->prepare("
      UPDATE justificante
      SET estado = ?, fecha_resolucion = NOW()
      WHERE id_justificante = ?
    ");

    $stmt->execute([$resolucion, $id]);
  }

  // ── AGREGAR ALUMNO INDIVIDUAL ───────────────────────────────

  public function agregarAlumno(string $matricula, string $nombre_completo, int $grupo, int $materia): void {

    $partes   = explode(' ', $nombre_completo, 2);
    $nombre   = $partes[0];
    $apellido = $partes[1] ?? '';

    $stmt = $this->pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
    $stmt->execute([$matricula]);

    if ($stmt->rowCount() == 0) {
      $stmt = $this->pdo->prepare("
        INSERT INTO usuario (nombre, apellido, correo, contrasena, id_rol)
        VALUES (?, ?, ?, ?, 1)
      ");
      $stmt->execute([$nombre, $apellido, $matricula, password_hash($matricula, PASSWORD_DEFAULT)]);
      $id_usuario = $this->pdo->lastInsertId();

      $stmt = $this->pdo->prepare("
        INSERT INTO alumno (id_usuario, matricula, id_grupo)
        VALUES (?, ?, ?)
      ");
      $stmt->execute([$id_usuario, $matricula, $grupo]);
      $id_alumno = $this->pdo->lastInsertId();

    } else {
      $stmt = $this->pdo->prepare("
        SELECT al.id_alumno FROM alumno al
        JOIN usuario u ON u.id_usuario = al.id_usuario
        WHERE u.correo = ?
      ");
      $stmt->execute([$matricula]);
      $id_alumno = $stmt->fetchColumn();
    }

    $stmt = $this->pdo->prepare("
      INSERT IGNORE INTO alumno_materia (id_alumno, id_materia)
      VALUES (?, ?)
    ");
    $stmt->execute([$id_alumno, $materia]);
  }

  // ── PROCESAR CSV ─────────────────────────────
  // Columnas del CSV:
  //   [0] matricula
  //   [1] nombre_completo
  //   [2] grado_texto    (ej: 4°)
  //   [3] grupo_texto    (ej: A)
  //   [4] nombre_tutor
  //   [5] telefono_tutor

  public function procesarCSV($archivo, $grupo, $materia) {

    if (($handle = fopen($archivo, "r")) !== FALSE) {

      // Saltar encabezado si la primera celda no es numérica
      $primeraFila = fgetcsv($handle, 1000, ",");
      if ($primeraFila && is_numeric(trim($primeraFila[0]))) {
        rewind($handle);
      }

      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        if (count($data) < 2) continue;

        $matricula       = trim($data[0]);
        $nombre_completo = trim($data[1]);
        $grado_texto     = trim($data[2] ?? '');
        $grupo_texto     = trim($data[3] ?? '');
        $nombre_tutor    = trim($data[4] ?? '');
        $telefono_tutor  = trim($data[5] ?? '');

        if (empty($matricula) || empty($nombre_completo)) continue;

        // Separar nombre y apellido
        $partes   = explode(' ', $nombre_completo, 2);
        $nombre   = $partes[0];
        $apellido = $partes[1] ?? '';

        // Verificar si ya existe el usuario
        $stmt = $this->pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
        $stmt->execute([$matricula]);

        if ($stmt->rowCount() == 0) {

          // Crear usuario
          $stmt = $this->pdo->prepare("
            INSERT INTO usuario (nombre, apellido, correo, contrasena, id_rol)
            VALUES (?, ?, ?, ?, 1)
          ");
          $stmt->execute([$nombre, $apellido, $matricula, password_hash($matricula, PASSWORD_DEFAULT)]);
          $id_usuario = $this->pdo->lastInsertId();

          // Crear alumno con todos los campos
          $stmt = $this->pdo->prepare("
            INSERT INTO alumno
              (id_usuario, matricula, id_grupo, grado_texto, grupo_texto, nombre_tutor, telefono_tutor, origen)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'excel')
          ");
          $stmt->execute([
            $id_usuario, $matricula, $grupo,
            $grado_texto    ?: null,
            $grupo_texto    ?: null,
            $nombre_tutor   ?: null,
            $telefono_tutor ?: null,
          ]);
          $id_alumno = $this->pdo->lastInsertId();

        } else {

          // Obtener alumno existente y actualizar campos vacíos
          $stmt = $this->pdo->prepare("
            SELECT al.id_alumno FROM alumno al
            JOIN usuario u ON u.id_usuario = al.id_usuario
            WHERE u.correo = ?
          ");
          $stmt->execute([$matricula]);
          $id_alumno = $stmt->fetchColumn();

          // Solo actualizar si el CSV trae datos nuevos
          $stmt = $this->pdo->prepare("
            UPDATE alumno SET
              grado_texto    = COALESCE(NULLIF(grado_texto,''),    ?),
              grupo_texto    = COALESCE(NULLIF(grupo_texto,''),    ?),
              nombre_tutor   = COALESCE(NULLIF(nombre_tutor,''),   ?),
              telefono_tutor = COALESCE(NULLIF(telefono_tutor,''), ?)
            WHERE id_alumno = ?
          ");
          $stmt->execute([
            $grado_texto    ?: null,
            $grupo_texto    ?: null,
            $nombre_tutor   ?: null,
            $telefono_tutor ?: null,
            $id_alumno
          ]);
        }

        // Relacionar alumno con materia
        $stmt = $this->pdo->prepare("
          INSERT IGNORE INTO alumno_materia (id_alumno, id_materia)
          VALUES (?, ?)
        ");
        $stmt->execute([$id_alumno, $materia]);
      }

      fclose($handle);
    }
  }

  
  // AGREGAR ESTOS MÉTODOS al final de la clase AsistenciaModelo en Modelo/AsistenciaModelo.php
// (pegar dentro de la clase, antes del último })
 
  // ── TODOS LOS JUSTIFICANTES ────────────────────────────────
  public function getTodosJustificantes(): array {
    $sql = "
      SELECT j.id_justificante AS id,
             u.nombre AS alumno,
             j.fecha_solicitud AS fecha,
             j.descripcion_motivo AS motivo,
             j.estado
      FROM justificante j
      JOIN alumno al ON al.id_alumno = j.id_alumno
      JOIN usuario u ON u.id_usuario = al.id_usuario
      ORDER BY j.fecha_solicitud DESC
    ";
    return $this->pdo->query($sql)->fetchAll();
  }
 
  // ── JUSTIFICANTES POR ESTADO ────────────────────────────────
  public function getJustificantesPorEstado(string $estado): array {
    $sql = "
      SELECT j.id_justificante AS id,
             u.nombre AS alumno,
             j.fecha_solicitud AS fecha,
             j.descripcion_motivo AS motivo,
             j.estado
      FROM justificante j
      JOIN alumno al ON al.id_alumno = j.id_alumno
      JOIN usuario u ON u.id_usuario = al.id_usuario
      WHERE j.estado = ?
      ORDER BY j.fecha_solicitud DESC
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$estado]);
    return $stmt->fetchAll();
  }
 
  // ── MARCAR ASISTENCIA COMO JUSTIFICADA ─────────────────────
  public function marcarComoJustificada(int $id_justificante): void {
    // Obtener datos del justificante
    $stmt = $this->pdo->prepare("
      SELECT id_alumno, fecha_solicitud FROM justificante WHERE id_justificante = ?
    ");
    $stmt->execute([$id_justificante]);
    $j = $stmt->fetch();
 
    if (!$j) return;
 
    // Actualizar todas las asistencias 'ausente' de ese alumno en esa fecha
    $stmt = $this->pdo->prepare("
      UPDATE asistencia
      SET estatus = 'justificada'
      WHERE id_alumno = ?
        AND fecha = ?
        AND estatus = 'ausente'
    ");
    $stmt->execute([$j['id_alumno'], $j['fecha_solicitud']]);
  }
 

  // ── MATERIAS DEL DOCENTE ────────────────────────────────────
  public function getMateriasPorDocente(int $id_docente): array {
    $stmt = $this->pdo->prepare("
      SELECT id_materia AS id, nombre
      FROM materia
      WHERE id_docente = ?
      ORDER BY nombre
    ");
    $stmt->execute([$id_docente]);
    return $stmt->fetchAll();
  }

  // ── GRUPOS DEL DOCENTE ──────────────────────────────────────
  public function getGruposPorDocente(int $id_docente): array {
    $stmt = $this->pdo->prepare("
      SELECT DISTINCT g.id_grupo AS id, g.nombre_grupo AS nombre
      FROM grupo g
      JOIN materia m ON m.id_grupo = g.id_grupo
      WHERE m.id_docente = ?
      ORDER BY g.nombre_grupo
    ");
    $stmt->execute([$id_docente]);
    return $stmt->fetchAll();
  }

  // ── GRUPOS POR MATERIA Y DOCENTE ────────────────────────────
  public function getGruposPorMateriaDocente(int $id_materia, int $id_docente): array {
    $stmt = $this->pdo->prepare("
      SELECT DISTINCT g.id_grupo AS id, g.nombre_grupo AS nombre
      FROM grupo g
      JOIN materia m ON m.id_grupo = g.id_grupo
      WHERE m.id_materia = ?
        AND m.id_docente = ?
      ORDER BY g.nombre_grupo
    ");
    $stmt->execute([$id_materia, $id_docente]);
    return $stmt->fetchAll();
  }

  // ── JUSTIFICANTES POR DOCENTE (todas sus materias) ──────────
  public function getJustificantesPorDocente(int $id_docente, string $estado = 'todos'): array {
    $params = [$id_docente, $id_docente];
    $where_estado = '';
    if ($estado !== 'todos') {
      $where_estado = 'AND j.estado = ?';
      $params[] = $estado;
    }
    $sql = "
      SELECT
        j.id_justificante  AS id,
        u.nombre           AS alumno,
        al.matricula,
        j.fecha_solicitud  AS fecha,
        j.tipo_motivo,
        j.descripcion_motivo AS motivo,
        j.estado,
        j.fecha_resolucion,
        j.observaciones,
        COALESCE(ma.nombre, mf.nombre) AS materia,
        g.nombre_grupo     AS grupo,
        a.fecha            AS fecha_falta,
        a.estatus          AS estatus_asistencia
      FROM justificante j
      JOIN alumno      al  ON al.id_alumno  = j.id_alumno
      JOIN usuario     u   ON u.id_usuario  = al.id_usuario
      JOIN grupo       g   ON g.id_grupo    = al.id_grupo
      LEFT JOIN asistencia a   ON a.id_asistencia = j.id_asistencia
      LEFT JOIN materia    ma  ON ma.id_materia   = a.id_materia
                               AND ma.id_docente  = ?
      LEFT JOIN (
        SELECT am2.id_alumno, m2.id_materia, m2.nombre
        FROM alumno_materia am2
        JOIN materia m2 ON m2.id_materia = am2.id_materia
        WHERE m2.id_docente = ?
      ) mf ON mf.id_alumno = al.id_alumno
           AND ma.id_materia IS NULL
      WHERE (ma.id_docente IS NOT NULL OR mf.id_materia IS NOT NULL)
        $where_estado
      GROUP BY
        j.id_justificante,
        u.nombre,
        al.matricula,
        j.fecha_solicitud,
        j.tipo_motivo,
        j.descripcion_motivo,
        j.estado,
        j.fecha_resolucion,
        j.observaciones,
        ma.nombre,
        mf.nombre,
        g.nombre_grupo,
        a.fecha,
        a.estatus
      ORDER BY
        FIELD(j.estado,'Pendiente','Entregado','Generado','Aprobado','Rechazado'),
        j.fecha_solicitud DESC
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  // ── JUSTIFICANTES POR DOCENTE + MATERIA ESPECÍFICA ─────────
  public function getJustificantesPorDocenteYMateria(int $id_docente, int $id_materia, string $estado = 'todos'): array {
    $params = [$id_docente, $id_materia, $id_materia, $id_materia];
    $where_estado = '';
    if ($estado !== 'todos') {
      $where_estado = 'AND j.estado = ?';
      $params[] = $estado;
    }
    $sql = "
      SELECT
        j.id_justificante  AS id,
        u.nombre           AS alumno,
        al.matricula,
        j.fecha_solicitud  AS fecha,
        j.tipo_motivo,
        j.descripcion_motivo AS motivo,
        j.estado,
        j.fecha_resolucion,
        j.observaciones,
        COALESCE(ma.nombre, mf.nombre) AS materia,
        g.nombre_grupo     AS grupo,
        a.fecha            AS fecha_falta,
        a.estatus          AS estatus_asistencia
      FROM justificante j
      JOIN alumno      al  ON al.id_alumno  = j.id_alumno
      JOIN usuario     u   ON u.id_usuario  = al.id_usuario
      JOIN grupo       g   ON g.id_grupo    = al.id_grupo
      LEFT JOIN asistencia a   ON a.id_asistencia = j.id_asistencia
      LEFT JOIN materia    ma  ON ma.id_materia   = a.id_materia
                               AND ma.id_docente  = ?
                               AND ma.id_materia  = ?
      LEFT JOIN (
        SELECT am2.id_alumno, m2.id_materia, m2.nombre
        FROM alumno_materia am2
        JOIN materia m2 ON m2.id_materia = am2.id_materia
        WHERE m2.id_materia = ?
      ) mf ON mf.id_alumno = al.id_alumno
           AND ma.id_materia IS NULL
      WHERE (ma.id_materia IS NOT NULL OR mf.id_materia = ?)
        $where_estado
      GROUP BY
        j.id_justificante,
        u.nombre,
        al.matricula,
        j.fecha_solicitud,
        j.tipo_motivo,
        j.descripcion_motivo,
        j.estado,
        j.fecha_resolucion,
        j.observaciones,
        ma.nombre,
        mf.nombre,
        g.nombre_grupo,
        a.fecha,
        a.estatus
      ORDER BY
        FIELD(j.estado,'Pendiente','Entregado','Generado','Aprobado','Rechazado'),
        j.fecha_solicitud DESC
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  // ── DETALLE JUSTIFICANTE CON COMPROBANTES ──────────────────
  public function getJustificanteDetalle(int $id): ?array {
    $stmt = $this->pdo->prepare("
      SELECT
        j.*,
        u.nombre           AS alumno,
        al.matricula,
        g.nombre_grupo     AS grupo,
        m.nombre           AS materia,
        a.fecha            AS fecha_falta,
        a.estatus          AS estatus_asistencia
      FROM justificante j
      JOIN alumno      al ON al.id_alumno  = j.id_alumno
      JOIN usuario     u  ON u.id_usuario  = al.id_usuario
      JOIN grupo       g  ON g.id_grupo    = al.id_grupo
      LEFT JOIN asistencia a ON a.id_asistencia = j.id_asistencia
      LEFT JOIN materia    m ON m.id_materia    = a.id_materia
      WHERE j.id_justificante = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) return null;

    $stmt2 = $this->pdo->prepare("
      SELECT archivo_url, tipo_archivo, fecha_subida
      FROM comprobante
      WHERE id_justificante = ?
      ORDER BY fecha_subida ASC
    ");
    $stmt2->execute([$id]);
    $row['comprobantes'] = $stmt2->fetchAll();
    return $row;
  }

  // ── ALUMNOS CON ESTATUS (con justificante automático) ───────
  // Sobreescribe el método original para que si un alumno tiene
  // justificante Aprobado en esa fecha y materia, muestre "justificada"
  // incluso si en la tabla asistencia aún dice "ausente".
  public function getAlumnosConEstatusYJustificante(int $grupo, int $materia, string $fecha): array {
    // Solo muestra alumnos inscritos en ESA materia (via alumno_materia)
    // y que pertenezcan al grupo seleccionado.
    // Esto garantiza que cada docente solo ve sus propios alumnos.
    $sql = "
      SELECT
        al.id_alumno AS id,
        al.matricula,
        CONCAT(u.nombre, ' ', u.apellido) AS nombre,
        CASE
          WHEN j.estado IN ('Aprobado','Entregado')
               AND j.id_justificante IS NOT NULL
            THEN 'justificada'
          ELSE COALESCE(a.estatus, 'pendiente')
        END AS estatus,
        j.estado        AS estado_justificante,
        j.id_justificante
      FROM alumno al
      JOIN usuario u         ON u.id_usuario  = al.id_usuario
      JOIN alumno_materia am ON am.id_alumno  = al.id_alumno
                             AND am.id_materia = ?
      LEFT JOIN asistencia a
        ON a.id_alumno  = al.id_alumno
        AND a.id_materia = ?
        AND a.fecha      = ?
      LEFT JOIN justificante j
        ON j.id_alumno      = al.id_alumno
        AND j.id_asistencia = a.id_asistencia
        AND j.estado IN ('Aprobado','Entregado')
      WHERE al.id_grupo = ?
      ORDER BY u.nombre
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$materia, $materia, $fecha, $grupo]);
    return $stmt->fetchAll();
  }

}