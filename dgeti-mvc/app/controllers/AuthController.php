<?php
// ============================================================
// app/controllers/AuthController.php
// Login integrado con la BD original (identificador, telefono)
// Registro completo via sp_registrar_usuario
// ============================================================

require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/UserModel.php';


class AuthController extends BaseController {

    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // ── GET /auth ──────────────────────────────────────────
    public function index(?string $p = null): void {
        if (!empty($_SESSION['user'])) {
            $this->redirect('dashboard');
        }
        $flash = $this->getFlash();
        $csrf  = $this->csrfToken();
        $this->render('auth/welcome', compact('flash', 'csrf'), 'auth');
    }

    // ── GET /auth/login ────────────────────────────────────
    public function login(?string $p = null): void {
        if (!empty($_SESSION['user'])) $this->redirect('dashboard');
        $flash = $this->getFlash();
        $csrf  = $this->csrfToken();
        $this->render('auth/login', compact('flash', 'csrf'), 'auth');
    }

    // ── POST /auth/loginpost ───────────────────────────────
    public function loginpost(?string $p = null): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        $this->verifyCsrf();

        $email    = filter_var($this->post('email'), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Por favor completa todos los campos.');
            $this->redirect('auth/login');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            $this->setFlash('error', 'Correo o contraseña incorrectos.');
            $this->redirect('auth/login');
        }

        if (empty($user['activo'])) {
            $this->setFlash('error', 'Tu cuenta está desactivada. Contacta al administrador.');
            $this->redirect('auth/login');
        }

        // ── Iniciar sesión con todos los campos del esquema ──
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'              => $user['id'],
            'nombre'          => $user['nombre_completo'],
            'nombre_corto'    => $user['nombre'],
            'apellido'        => $user['apellido'],
            'email'           => $user['email'],
            'rol'             => $user['rol'],
            'identificador'   => $user['identificador'] ?? '',
            'telefono'        => $user['telefono']       ?? '',
            'grupo'           => $user['grupo'],
            'matricula'       => $user['matricula'],
            'id_alumno'       => $user['id_alumno']  ?? null,
            'id_docente'      => $user['id_docente'] ?? null,
            'id_grupo'        => $user['id_grupo']   ?? null,
        ];

        $this->setFlash('success', '¡Bienvenido, ' . $user['nombre'] . '!');
        $this->redirect('dashboard');
    }

    // ── GET /auth/logout ───────────────────────────────────
    public function logout(?string $p = null): void {
        $_SESSION = [];
        session_destroy();
        $this->redirect('auth/login');
    }

    // ── GET /auth/register ─────────────────────────────────
    public function register(?string $p = null): void {
        $flash = $this->getFlash();
        $csrf  = $this->csrfToken();
        $this->render('auth/register', compact('flash', 'csrf'), 'auth');
    }

    // ── POST /auth/registerpost ────────────────────────────
    // Llama a sp_registrar_usuario y traduce los códigos de resultado
    public function registerpost(?string $p = null): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('auth/register');
        $this->verifyCsrf();

        // ── Recoger campos del formulario ──────────────────
        $nombre        = $this->post('nombres');
        $apellido      = $this->post('apellidos');
        $identificador = strtoupper($this->post('identificador'));
        $telefono      = $this->post('telefono');
        $correo        = filter_var($this->post('email'), FILTER_SANITIZE_EMAIL);
        $contrasena    = $_POST['password'] ?? '';
        $rolNombre     = $this->post('rol');   // 'alumno', 'docente', etc.

        // ── Validación mínima en PHP antes de llamar al SP ─
        if (empty($nombre) || empty($apellido) || empty($identificador)
            || empty($telefono) || empty($correo) || empty($contrasena) || empty($rolNombre)) {
            $this->setFlash('error', 'Por favor completa todos los campos.');
            $this->redirect('auth/register');
        }

        // Resolver id_rol
        $idRol = $this->userModel->getRolId($rolNombre);
        if (!$idRol) {
            $this->setFlash('error', 'Rol seleccionado no válido.');
            $this->redirect('auth/register');
        }

        // ── Formatear teléfono si viene solo con 10 dígitos ─
        // El SP espera formato '+52 772 XXX XXXX' o '+52 773 XXX XXXX'
        $telefonoFmt = $this->formatTelefono($telefono);

        // ── Llamar al stored procedure ─────────────────────
        $resultado = $this->userModel->registrar([
            'nombre'        => $nombre,
            'apellido'      => $apellido,
            'identificador' => $identificador,
            'telefono'      => $telefonoFmt,
            'correo'        => $correo,
            'contrasena'    => $contrasena,
            'id_rol'        => $idRol,
        ]);

        // ── Traducir resultado del SP ──────────────────────
        $mensajes = [
            'OK'               => ['type' => 'success', 'msg' => '¡Cuenta creada exitosamente! Ya puedes iniciar sesión.', 'redirect' => 'auth/login'],
            'EXISTE'           => ['type' => 'error',   'msg' => 'El correo o identificador ya están registrados.',        'redirect' => 'auth/register'],
            'CORREO_INVALIDO'  => ['type' => 'error',   'msg' => 'El correo debe ser institucional (@cbtis.edu.mx) o una matrícula válida.', 'redirect' => 'auth/register'],
            'TELEFONO_INVALIDO'=> ['type' => 'error',   'msg' => 'El teléfono debe tener formato +52 772 XXX XXXX o +52 773 XXX XXXX.',      'redirect' => 'auth/register'],
            'ERROR_CURP_ALUMNO'=> ['type' => 'error',   'msg' => 'La CURP debe tener exactamente 18 caracteres.',          'redirect' => 'auth/register'],
            'ERROR_RFC_PERSONAL'=>['type' => 'error',   'msg' => 'El RFC debe tener exactamente 13 caracteres.',           'redirect' => 'auth/register'],
            'PASSWORD_LONGITUD'=> ['type' => 'error',   'msg' => 'La contraseña debe tener exactamente 8 caracteres.',     'redirect' => 'auth/register'],
            'PASSWORD_MINUSCULA'=>['type' => 'error',   'msg' => 'La contraseña debe incluir al menos una letra minúscula.','redirect' => 'auth/register'],
            'PASSWORD_MAYUSCULA'=>['type' => 'error',   'msg' => 'La contraseña debe incluir al menos una letra mayúscula.','redirect' => 'auth/register'],
            'PASSWORD_NUMERO'  => ['type' => 'error',   'msg' => 'La contraseña debe incluir al menos un número.',          'redirect' => 'auth/register'],
            'PASSWORD_ESPECIAL'=> ['type' => 'error',   'msg' => 'La contraseña debe incluir al menos un carácter especial (!@#$%^&*...).', 'redirect' => 'auth/register'],
        ];

        $info = $mensajes[$resultado] ?? [
            'type'     => 'error',
            'msg'      => 'Ocurrió un error inesperado (' . htmlspecialchars($resultado) . '). Inténtalo de nuevo.',
            'redirect' => 'auth/register',
        ];

        $this->setFlash($info['type'], $info['msg']);
        $this->redirect($info['redirect']);
    }

    // ── GET /auth/forgotpassword ───────────────────────────
    public function forgotpassword(?string $p = null): void {
        $flash = $this->getFlash();
        $csrf  = $this->csrfToken();
        $this->render('auth/forgot', compact('flash', 'csrf'), 'auth');
    }

    // ── POST /auth/forgotpost ──────────────────────────────
    public function forgotpost(?string $p = null): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('auth/forgotpassword');
        $this->verifyCsrf();

        $correo = filter_var($this->post('email'), FILTER_SANITIZE_EMAIL);

        if (empty($correo)) {
            $this->setFlash('error', 'Ingresa tu correo institucional.');
            $this->redirect('auth/forgotpassword');
        }

        // Generar token y expiración (1 hora)
        $token      = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare('CALL sp_generar_token(:correo, :token, :exp)');
            $stmt->execute([':correo' => $correo, ':token' => $token, ':exp' => $expiracion]);
            $row  = $stmt->fetch();
            $res  = $row['resultado'] ?? 'ERROR';
        } catch (PDOException $e) {
            error_log('forgotpost SP error: ' . $e->getMessage());
            $res = 'ERROR';
        }

        if ($res === 'TOKEN_GENERADO') {
            // En producción aquí se enviaría el correo con el enlace de recuperación.
            // Por ahora redirigimos con el token en la URL (solo para pruebas locales).
            $this->setFlash('success', 'Se ha enviado un enlace de recuperación a tu correo institucional.');
            $this->redirect('auth/resetpassword?token=' . urlencode($token));
        } elseif ($res === 'USUARIO_NO_EXISTE') {
            $this->setFlash('error', 'No encontramos una cuenta con ese correo.');
            $this->redirect('auth/forgotpassword');
        } else {
            $this->setFlash('error', 'No se pudo procesar la solicitud. Inténtalo más tarde.');
            $this->redirect('auth/forgotpassword');
        }
    }

    // ── GET /auth/resetpassword ────────────────────────────
    public function resetpassword(?string $p = null): void {
        $flash = $this->getFlash();
        $csrf  = $this->csrfToken();
        // Pasar el token desde la URL a la vista
        $token = $_GET['token'] ?? '';
        $this->render('auth/reset', compact('flash', 'csrf', 'token'), 'auth');
    }

    // ── POST /auth/resetpost ───────────────────────────────
    public function resetpost(?string $p = null): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('auth/resetpassword');
        $this->verifyCsrf();

        $token    = $_POST['token']            ?? '';
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (empty($token) || empty($password)) {
            $this->setFlash('error', 'Datos incompletos. Vuelve a solicitar el cambio.');
            $this->redirect('auth/forgotpassword');
        }

        if ($password !== $confirm) {
            $this->setFlash('error', 'Las contraseñas no coinciden.');
            $this->redirect('auth/resetpassword?token=' . urlencode($token));
        }

        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare('CALL sp_cambiar_password(:token, :pass)');
            $stmt->execute([':token' => $token, ':pass' => $password]);
            $row  = $stmt->fetch();
            $res  = $row['resultado'] ?? 'ERROR';
        } catch (PDOException $e) {
            error_log('resetpost SP error: ' . $e->getMessage());
            $res = 'ERROR';
        }

        if ($res === 'PASSWORD_ACTUALIZADA') {
            $this->setFlash('success', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
            $this->redirect('auth/login');
        } elseif ($res === 'TOKEN_INVALIDO') {
            $this->setFlash('error', 'El enlace de recuperación ha expirado o ya fue utilizado. Solicita uno nuevo.');
            $this->redirect('auth/forgotpassword');
        } else {
            $this->setFlash('error', 'No se pudo actualizar la contraseña. Inténtalo de nuevo.');
            $this->redirect('auth/forgotpassword');
        }
    }

    // ── Helpers privados ───────────────────────────────────

    /**
     * Formatea un número de teléfono al formato esperado por el SP:
     * '+52 77X XXX XXXX'
     * Acepta entrada como '7721234567', '+527721234567', '+52 772 123 4567', etc.
     */
    private function formatTelefono(string $raw): string {
        // Quitar todo lo que no sea dígito
        $digits = preg_replace('/\D/', '', $raw);

        // Si empieza con 52, quitarlo para quedarnos con 10 dígitos locales
        if (str_starts_with($digits, '52') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        // Si ya tiene formato correcto, solo retornar como está
        if (preg_match('/^\+52 \d{3} \d{3} \d{4}$/', $raw)) {
            return $raw;
        }

        // Formatear 10 dígitos → '+52 NNN NNN NNNN'
        if (strlen($digits) === 10) {
            return '+52 ' . substr($digits, 0, 3) . ' ' . substr($digits, 3, 3) . ' ' . substr($digits, 6);
        }

        // Si no se puede formatear, devolver como vino (el SP lo rechazará con TELEFONO_INVALIDO)
        return $raw;
    }
}
