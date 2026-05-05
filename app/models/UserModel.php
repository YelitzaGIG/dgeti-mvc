<?php
// ============================================================
// app/models/UserModel.php
// Consulta las tablas: usuario, rol, alumno, docente
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
                u.id_usuario   AS id,
                u.nombre,
                u.apellido,
                u.correo       AS email,
                u.contrasena   AS password,
                u.activo,
                r.nombre_rol   AS rol,

                /* datos de alumno (NULL si no es alumno) */
                a.matricula,
                a.id_grupo,
                g.nombre_grupo AS grupo,
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
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    // ── Buscar por ID ──────────────────────────────────────
    public function findById(int $id): ?array {
        $sql = "
            SELECT
                u.id_usuario AS id,
                u.nombre,
                u.apellido,
                u.correo     AS email,
                u.activo,
                r.nombre_rol AS rol,
                a.matricula,
                g.nombre_grupo AS grupo,
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
    public function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'UPDATE usuario SET contrasena = :h WHERE id_usuario = :id'
        );
        return $stmt->execute([':h' => $hash, ':id' => $id]);
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
            ['nombre' => 'Administrador', 'apellido' => 'DGETI',         'correo' => 'admin@cetis.edu.mx',    'rol' => 'jefa_servicios'],
            ['nombre' => 'María',         'apellido' => 'Sánchez Ruiz',  'correo' => 'docente@cetis.edu.mx',  'rol' => 'docente'],
            ['nombre' => 'Juan',          'apellido' => 'García López',  'correo' => 'alumno@cetis.edu.mx',   'rol' => 'alumno'],
            ['nombre' => 'Laura',         'apellido' => 'Reyes Díaz',    'correo' => 'orientadora@cetis.edu.mx', 'rol' => 'orientadora'],
        ];

        foreach ($usuarios as $u) {
            // Evitar duplicados
            $check = $db->prepare('SELECT id_usuario FROM usuario WHERE correo = :c LIMIT 1');
            $check->execute([':c' => $u['correo']]);
            if ($check->fetch()) continue;

            $ins = $db->prepare(
                'INSERT INTO usuario (nombre, apellido, correo, contrasena, id_rol)
                 VALUES (:n, :a, :c, :h, :r)'
            );
            $ins->execute([
                ':n' => $u['nombre'],
                ':a' => $u['apellido'],
                ':c' => $u['correo'],
                ':h' => $hash,
                ':r' => $roles[$u['rol']],
            ]);
            $newId = (int) $db->lastInsertId();

            // Crear registro docente
            if ($u['rol'] === 'docente' || $u['rol'] === 'tutor_institucional') {
                $db->prepare('INSERT INTO docente (id_usuario, especialidad) VALUES (:id, :e)')
                   ->execute([':id' => $newId, ':e' => 'Informática']);
            }

            // Crear registro alumno
            if ($u['rol'] === 'alumno') {
                // Asegurarse de que existe al menos un grupo
                $gCheck = $db->query('SELECT id_grupo FROM grupo LIMIT 1')->fetch();
                if (!$gCheck) {
                    $db->exec("INSERT INTO grupo (nombre_grupo, grado, turno) VALUES ('3-B', '3°', 'matutino')");
                    $gCheck = ['id_grupo' => $db->lastInsertId()];
                }
                $matricula = 'CETIS-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
                $db->prepare(
                    'INSERT INTO alumno (id_usuario, matricula, id_grupo)
                     VALUES (:uid, :m, :g)'
                )->execute([':uid' => $newId, ':m' => $matricula, ':g' => $gCheck['id_grupo']]);
            }
        }
    }
}
