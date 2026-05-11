<?php
// ============================================================
// app/models/JustificanteModel.php
// Adaptado al esquema sistema_justificantes
// Tablas: justificante, alumno, usuario, asistencia, comprobante
// ============================================================

class JustificanteModel {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ── Listar todos (con filtros opcionales) ──────────────
    public function getAll(array $filters = []): array {
        $sql = "
            SELECT
                j.id_justificante          AS id,
                j.tipo_motivo              AS motivo,
                j.descripcion_motivo,
                j.estado,
                j.fecha_solicitud          AS created_at,
                j.fecha_resolucion,
                j.observaciones,

                /* Datos del alumno */
                a.matricula                AS numero_control,
                a.id_grupo,
                g.nombre_grupo             AS grupo,
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_alumno,

                /* Datos de asistencia vinculada (opcional) */
                asi.fecha                  AS fecha_ausencia,
                asi.estatus                AS estatus_asistencia,
                m.nombre                   AS materia,

                /* Orientadora que resolvió */
                CONCAT(uo.nombre, ' ', uo.apellido) AS nombre_orientadora

            FROM justificante j
            JOIN alumno  a   ON a.id_alumno  = j.id_alumno
            JOIN usuario u   ON u.id_usuario = a.id_usuario
            JOIN grupo   g   ON g.id_grupo   = a.id_grupo
            LEFT JOIN asistencia asi ON asi.id_asistencia = j.id_asistencia
            LEFT JOIN materia    m   ON m.id_materia      = asi.id_materia
            LEFT JOIN usuario   uo  ON uo.id_usuario      = j.id_orientadora
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['estado'])) {
            $sql .= ' AND j.estado = :estado';
            $params[':estado'] = $filters['estado'];
        }
        if (!empty($filters['motivo'])) {
            $sql .= ' AND j.tipo_motivo = :motivo';
            $params[':motivo'] = $filters['motivo'];
        }
        if (!empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $sql .= " AND (
                CONCAT(u.nombre,' ',u.apellido) LIKE :s
                OR a.matricula LIKE :s2
                OR g.nombre_grupo LIKE :s3
            )";
            $params[':s']  = $like;
            $params[':s2'] = $like;
            $params[':s3'] = $like;
        }
        if (!empty($filters['numero_control'])) {
            $sql .= ' AND a.matricula = :nc';
            $params[':nc'] = $filters['numero_control'];
        }
        if (!empty($filters['id_alumno'])) {
            $sql .= ' AND j.id_alumno = :ida';
            $params[':ida'] = $filters['id_alumno'];
        }

        $sql .= ' ORDER BY j.fecha_solicitud DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Obtener uno por ID ─────────────────────────────────
    public function getById(int $id): ?array {
        $sql = "
            SELECT
                j.id_justificante          AS id,
                j.tipo_motivo              AS motivo,
                j.descripcion_motivo,
                j.estado,
                j.fecha_solicitud          AS created_at,
                j.fecha_resolucion,
                j.observaciones,
                j.id_asistencia,

                a.matricula                AS numero_control,
                a.id_grupo,
                g.nombre_grupo             AS grupo,
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_alumno,

                asi.fecha                  AS fecha_ausencia,
                asi.estatus                AS estatus_asistencia,
                m.nombre                   AS materia,

                CONCAT(uo.nombre, ' ', uo.apellido) AS nombre_orientadora

            FROM justificante j
            JOIN alumno  a   ON a.id_alumno  = j.id_alumno
            JOIN usuario u   ON u.id_usuario = a.id_usuario
            JOIN grupo   g   ON g.id_grupo   = a.id_grupo
            LEFT JOIN asistencia asi ON asi.id_asistencia = j.id_asistencia
            LEFT JOIN materia    m   ON m.id_materia      = asi.id_materia
            LEFT JOIN usuario   uo  ON uo.id_usuario      = j.id_orientadora

            WHERE j.id_justificante = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ── Obtener id_alumno por id_usuario ──────────────────
    public function getIdAlumnoByUsuario(int $idUsuario): ?int {
        $stmt = $this->db->prepare(
            'SELECT id_alumno FROM alumno WHERE id_usuario = :uid LIMIT 1'
        );
        $stmt->execute([':uid' => $idUsuario]);
        $row = $stmt->fetch();
        return $row ? (int) $row['id_alumno'] : null;
    }

    // ── Crear nuevo justificante ───────────────────────────
    // $data necesita: id_alumno, tipo_motivo, descripcion_motivo (opcional),
    //                 id_asistencia (opcional)
    public function create(array $data): bool|int {
        $sql = "
            INSERT INTO justificante
                (id_alumno, id_asistencia, tipo_motivo, descripcion_motivo, estado)
            VALUES
                (:id_alumno, :id_asistencia, :motivo, :desc, 'Generado')
        ";
        $stmt = $this->db->prepare($sql);
        $ok   = $stmt->execute([
            ':id_alumno'     => $data['id_alumno'],
            ':id_asistencia' => $data['id_asistencia'] ?? null,
            ':motivo'        => $data['tipo_motivo'],
            ':desc'          => $data['descripcion_motivo'] ?? null,
        ]);
        return $ok ? (int) $this->db->lastInsertId() : false;
    }

    // ── Actualizar estado (orientadora / jefa) ─────────────
    public function updateEstado(int $id, string $estado, ?int $idOrientadora = null, ?string $observaciones = null): bool {
        if (!in_array($estado, ESTADOS)) return false;
        $sql = "
            UPDATE justificante
            SET estado           = :estado,
                fecha_resolucion = IF(:estado IN ('Aprobado','Rechazado'), NOW(), fecha_resolucion),
                id_orientadora   = COALESCE(:ori, id_orientadora),
                observaciones    = COALESCE(:obs, observaciones)
            WHERE id_justificante = :id
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado' => $estado,
            ':ori'    => $idOrientadora,
            ':obs'    => $observaciones,
            ':id'     => $id,
        ]);
    }

    // ── Actualizar registro completo (admin / orientadora) ─
    public function update(int $id, array $data): bool {
        $sql = "
            UPDATE justificante SET
                tipo_motivo        = :motivo,
                descripcion_motivo = :desc,
                estado             = :estado,
                observaciones      = :obs
            WHERE id_justificante  = :id
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':motivo' => $data['tipo_motivo'],
            ':desc'   => $data['descripcion_motivo'] ?? null,
            ':estado' => $data['estado'],
            ':obs'    => $data['observaciones'] ?? null,
            ':id'     => $id,
        ]);
    }

    // ── Eliminar ───────────────────────────────────────────
    public function delete(int $id): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM justificante WHERE id_justificante = :id'
        );
        return $stmt->execute([':id' => $id]);
    }

    // ── Estadísticas para dashboard ────────────────────────
    public function getStats(?int $idAlumno = null): array {
        $where  = $idAlumno ? 'WHERE id_alumno = :ida' : '';
        $params = $idAlumno ? [':ida' => $idAlumno] : [];

        $stats = [];
        foreach (ESTADOS as $e) {
            $extra = $idAlumno ? ' AND id_alumno = :ida' : '';
            $stmt  = $this->db->prepare(
                "SELECT COUNT(*) FROM justificante WHERE estado = :e $extra"
            );
            $p = [':e' => $e];
            if ($idAlumno) $p[':ida'] = $idAlumno;
            $stmt->execute($p);
            $stats[$e] = (int) $stmt->fetchColumn();
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM justificante $where");
        $stmt->execute($params);
        $stats['total'] = (int) $stmt->fetchColumn();

        return $stats;
    }

    // ── Obtener id_alumno por matrícula ───────────────────
    public function getIdAlumnoByMatricula(string $matricula): ?int {
        $stmt = $this->db->prepare(
            'SELECT id_alumno FROM alumno WHERE matricula = :m LIMIT 1'
        );
        $stmt->execute([':m' => $matricula]);
        $row = $stmt->fetch();
        return $row ? (int) $row['id_alumno'] : null;
    }

    // ── Listar asistencias del alumno (para vincular) ──────
    public function getAsistenciasByAlumno(int $idAlumno): array {
        $sql = "
            SELECT
                asi.id_asistencia,
                asi.fecha,
                asi.estatus,
                m.nombre AS materia,
                CONCAT(u.nombre,' ',u.apellido) AS docente
            FROM asistencia asi
            JOIN materia m ON m.id_materia = asi.id_materia
            JOIN docente d ON d.id_docente = asi.id_docente
            JOIN usuario u ON u.id_usuario = d.id_usuario
            WHERE asi.id_alumno = :id
              AND asi.estatus   = 'ausente'
            ORDER BY asi.fecha DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idAlumno]);
        return $stmt->fetchAll();
    }
}
