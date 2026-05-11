<?php
// ============================================================
// app/models/UserModel.php
// Consulta las tablas: usuario, rol, alumno, docente
// Compatible con hash SHA2 (BD original) y bcrypt (MVC)
// ============================================================

class UserModel {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ── Buscar usuario por correo ──────────────────────────
    // Devuelve un array con los campos que usa la sesión,
    // o null si no existe.
    public function findByEmail(string $email): ?array {
        $sql = "
            SELECT
                u.id_usuario    AS id,
                u.nombre,
                u.apellido,
                u.correo        AS email,
                u.contrasena    AS password,
                u.identificador,
                u.telefono,
                u.activo,
                r.nombre_rol    AS rol,

                /* datos de alumno (NULL si no es alumno) */
                a.id_alumno,
                a.matricula,
                a.id_grupo,
                g.nombre_grupo  AS grupo,
                a.es_jefe_grupo,

                /* datos de docente (NULL si no es docente) */
                d.id_docente,
                d.especialidad

            FROM usuario u
            JOIN rol     r ON r.id_rol    = u.id_rol
            LEFT JOIN alumno  a ON a.id_usuario = u.id_usuario
            LEFT JOIN grupo   g ON g.id_grupo   = a.id_grupo
            LEFT JOIN docente d ON d.id_usuario = u.id_usuario

            WHERE u.correo = :correo
              AND u.activo  = 1
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => $email]);
        $row = $stmt->fetch();

        if (!$row) return null;

        // Construir nombre completo para la sesión
        $row['nombre_completo'] = trim($row['nombre'] . ' ' . $row['apellido']);

        // Matrícula: si no es alumno usamos un placeholder legible
        if (empty($row['matricula'])) {
            $row['matricula'] = strtoupper(substr($row['rol'], 0, 3)) . '-' . str_pad($row['id'], 4, '0', STR_PAD_LEFT);
        }

        // Grupo: si no aplica
        if (empty($row['grupo'])) {
            $row['grupo'] = 'N/A';
        }

        return $row;
    }

    // ── Verificar contraseña ───────────────────────────────
    // Soporta dos esquemas:
    //   1) bcrypt  → password_hash (generado por el MVC)
    //   2) SHA2    → SHA2(password,256) hex en minúsculas (BD original)
    public function verifyPassword(string $password, string $hash): bool {
        // bcrypt siempre empieza con '$2y$' o '$2b$'
        if (str_starts_with($hash, '$2')) {
            return password_verify($password, $hash);
        }

        // SHA-256 hex de 64 caracteres (BD original / sp_login)
        if (strlen($hash) === 64) {
            return hash_equals($hash, hash('sha256', $password));
        }

        return false;
    }

    // ── Buscar por ID ──────────────────────────────────────
    public function findById(int $id): ?array {
        $sql = "
            SELECT
                u.id_usuario    AS id,
                u.nombre,
                u.apellido,
                u.correo        AS email,
                u.identificador,
                u.telefono,
                u.activo,
                r.nombre_rol    AS rol,
                a.id_alumno,
                a.matricula,
                g.nombre_grupo  AS grupo,
                d.id_docente,
                d.especialidad
            FROM usuario u
            JOIN rol     r ON r.id_rol    = u.id_rol
            LEFT JOIN alumno  a ON a.id_usuario = u.id_usuario
            LEFT JOIN grupo   g ON g.id_grupo   = a.id_grupo
            LEFT JOIN docente d ON d.id_usuario = u.id_usuario
            WHERE u.id_usuario = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $row['nombre_completo'] = trim($row['nombre'] . ' ' . $row['apellido']);
        $row['matricula'] = $row['matricula'] ?? strtoupper(substr($row['rol'], 0, 3)) . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
        $row['grupo']     = $row['grupo'] ?? 'N/A';
        return $row;
    }

    // ── Actualizar nombre (perfil) ─────────────────────────
    public function updateNombre(int $id, string $nombre, string $apellido): bool {
        $stmt = $this->db->prepare(
            'UPDATE usuario SET nombre = :n, apellido = :a WHERE id_usuario = :id'
        );
        return $stmt->execute([':n' => $nombre, ':a' => $apellido, ':id' => $id]);
    }

    // ── Actualizar contraseña ──────────────────────────────
    // Guarda con bcrypt para el MVC; si quieres mantener SHA2
    // para compatibilidad con sp_login, usa updatePasswordSha2().
    public function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'UPDATE usuario SET contrasena = :h WHERE id_usuario = :id'
        );
        return $stmt->execute([':h' => $hash, ':id' => $id]);
    }

    // ── Actualizar contraseña con SHA2 (compatible BD original) ──
    public function updatePasswordSha2(int $id, string $newPassword): bool {
        $stmt = $this->db->prepare(
            "UPDATE usuario SET contrasena = SHA2(:p, 256) WHERE id_usuario = :id"
        );
        return $stmt->execute([':p' => $newPassword, ':id' => $id]);
    }

    // ── Verificar contraseña actual desde BD ───────────────
    public function getPasswordHash(int $id): ?string {
        $stmt = $this->db->prepare(
            'SELECT contrasena FROM usuario WHERE id_usuario = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $row['contrasena'] : null;
    }

    // ── Registrar usuario usando sp_registrar_usuario ──────
    // Devuelve el resultado del SP: 'OK', 'EXISTE', 'CORREO_INVALIDO', etc.
    public function registrar(array $data): string {
        try {
            $stmt = $this->db->prepare(
                'CALL sp_registrar_usuario(:nombre, :apellido, :identificador, :telefono, :correo, :contrasena, :id_rol)'
            );
            $stmt->execute([
                ':nombre'        => $data['nombre'],
                ':apellido'      => $data['apellido'],
                ':identificador' => strtoupper($data['identificador']),
                ':telefono'      => $data['telefono'],
                ':correo'        => $data['correo'],
                ':contrasena'    => $data['contrasena'],
                ':id_rol'        => (int) $data['id_rol'],
            ]);
            $row = $stmt->fetch();
            return $row['resultado'] ?? 'ERROR_SP';
        } catch (PDOException $e) {
            error_log('UserModel::registrar — ' . $e->getMessage());
            return 'ERROR_DB';
        }
    }

    // ── Obtener ID de rol por nombre ───────────────────────
    public function getRolId(string $nombreRol): ?int {
        $stmt = $this->db->prepare(
            'SELECT id_rol FROM rol WHERE nombre_rol = :n LIMIT 1'
        );
        $stmt->execute([':n' => $nombreRol]);
        $row = $stmt->fetch();
        return $row ? (int) $row['id_rol'] : null;
    }

    // ── Insertar usuario demo (para sembrar datos de prueba) ─
    public static function seedDemoUsers(PDO $db): void {
        $hash = password_hash('password', PASSWORD_BCRYPT);

        // Obtener IDs de roles
        $roles = [];
        $stmt  = $db->query('SELECT id_rol, nombre_rol FROM rol');
        foreach ($stmt->fetchAll() as $r) {
            $roles[$r['nombre_rol']] = $r['id_rol'];
        }

        $usuarios = [
            ['nombre' => 'Administrador', 'apellido' => 'DGETI',         'correo' => 'admin@cetis.edu.mx',        'identificador' => 'ADMX000000HDGTI01',  'telefono' => '+52 442 000 0001', 'rol' => 'jefa_servicios'],
            ['nombre' => 'María',         'apellido' => 'Sánchez Ruiz',  'correo' => 'docente@cetis.edu.mx',      'identificador' => 'SARM800101AAA',       'telefono' => '+52 442 000 0002', 'rol' => 'docente'],
            ['nombre' => 'Juan',          'apellido' => 'García López',  'correo' => 'alumno@cetis.edu.mx',       'identificador' => 'GALJ050101HDFRCN01', 'telefono' => '+52 442 000 0003', 'rol' => 'alumno'],
            ['nombre' => 'Laura',         'apellido' => 'Reyes Díaz',    'correo' => 'orientadora@cetis.edu.mx', 'identificador' => 'REDL850201AAA',       'telefono' => '+52 442 000 0004', 'rol' => 'orientadora'],
        ];

        foreach ($usuarios as $u) {
            // Evitar duplicados
            $check = $db->prepare('SELECT id_usuario FROM usuario WHERE correo = :c LIMIT 1');
            $check->execute([':c' => $u['correo']]);
            if ($check->fetch()) continue;

            $ins = $db->prepare(
                'INSERT INTO usuario (nombre, apellido, identificador, telefono, correo, contrasena, id_rol)
                 VALUES (:n, :a, :i, :t, :c, :h, :r)'
            );
            $ins->execute([
                ':n' => $u['nombre'],
                ':a' => $u['apellido'],
                ':i' => $u['identificador'],
                ':t' => $u['telefono'],
                ':c' => $u['correo'],
                ':h' => $hash,
                ':r' => $roles[$u['rol']],
            ]);
            $newId = (int) $db->lastInsertId();

            // Crear registro docente
            if (in_array($u['rol'], ['docente', 'tutor_institucional'])) {
                $db->prepare('INSERT INTO docente (id_usuario, especialidad) VALUES (:id, :e)')
                   ->execute([':id' => $newId, ':e' => 'Informática']);
            }

            // Crear registro alumno
            if ($u['rol'] === 'alumno') {
                $gCheck = $db->query('SELECT id_grupo FROM grupo LIMIT 1')->fetch();
                if (!$gCheck) {
                    $db->exec("INSERT INTO grupo (nombre_grupo, grado, turno) VALUES ('3-B', '3°', 'matutino')");
                    $gCheck = ['id_grupo' => $db->lastInsertId()];
                }
                $matricula = 'CETIS-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
                $db->prepare(
                    'INSERT INTO alumno (id_usuario, matricula, id_grupo) VALUES (:uid, :m, :g)'
                )->execute([':uid' => $newId, ':m' => $matricula, ':g' => $gCheck['id_grupo']]);
            }
        }
    }
}
