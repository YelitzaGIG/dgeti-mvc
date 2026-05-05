<?php
// ============================================================
// app/controllers/DashboardController.php
// ============================================================

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/JustificanteModel.php';
require_once ROOT . '/app/models/UserModel.php';

class DashboardController extends BaseController {

    private JustificanteModel $model;
    private UserModel $userModel;

    public function __construct() {
        $this->model     = new JustificanteModel();
        $this->userModel = new UserModel();
    }

    // GET /dashboard
    public function index(?string $p = null): void {
        $this->requireAuth();

        $user  = $_SESSION['user'];
        $flash = $this->getFlash();

        // Filtrar por alumno si corresponde
        $filters = [];
        if ($user['rol'] === 'alumno' && !empty($user['id_alumno'])) {
            $filters['id_alumno'] = $user['id_alumno'];
        }

        $stats     = $this->model->getStats($user['rol'] === 'alumno' ? ($user['id_alumno'] ?? null) : null);
        $recientes = array_slice($this->model->getAll($filters), 0, 5);

        $this->render('dashboard/index', compact('user', 'flash', 'stats', 'recientes'), 'main');
    }

    // GET /dashboard/perfil
    public function perfil(?string $p = null): void {
        $this->requireAuth();
        $user  = $_SESSION['user'];
        $flash = $this->getFlash();
        $csrf  = $this->csrfToken();
        $this->render('dashboard/perfil', compact('user', 'flash', 'csrf'), 'main');
    }

    // POST /dashboard/perfilpost
    public function perfilpost(?string $p = null): void {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard/perfil');
        }
        $this->verifyCsrf();

        $action = $this->post('action');
        $userId = (int) $_SESSION['user']['id'];

        if ($action === 'change_password') {
            $current = $_POST['password_current'] ?? '';
            $new     = $_POST['password_new']     ?? '';
            $confirm = $_POST['password_confirm'] ?? '';

            if (empty($current) || empty($new) || empty($confirm)) {
                $this->setFlash('error', 'Completa todos los campos de contraseña.');
                $this->redirect('dashboard/perfil');
            }
            if ($new !== $confirm) {
                $this->setFlash('error', 'Las contraseñas nuevas no coinciden.');
                $this->redirect('dashboard/perfil');
            }
            if (strlen($new) < 8) {
                $this->setFlash('error', 'La contraseña debe tener al menos 8 caracteres.');
                $this->redirect('dashboard/perfil');
            }

            // Verificar contraseña actual contra BD
            $hash = $this->userModel->getPasswordHash($userId);
            if (!$hash || !$this->userModel->verifyPassword($current, $hash)) {
                $this->setFlash('error', 'La contraseña actual es incorrecta.');
                $this->redirect('dashboard/perfil');
            }

            $this->userModel->updatePassword($userId, $new);
            $this->setFlash('success', 'Contraseña actualizada correctamente.');

        } else {
            // Actualizar nombre
            $nombre   = $this->post('nombre');
            $apellido = $this->post('apellido');

            if (empty($nombre)) {
                $this->setFlash('error', 'El nombre no puede estar vacío.');
                $this->redirect('dashboard/perfil');
            }

            $this->userModel->updateNombre($userId, $nombre, $apellido);

            // Actualizar sesión
            $_SESSION['user']['nombre']        = trim($nombre . ' ' . $apellido);
            $_SESSION['user']['nombre_corto']  = $nombre;
            $_SESSION['user']['apellido']      = $apellido;

            $this->setFlash('success', 'Perfil actualizado correctamente.');
        }

        $this->redirect('dashboard/perfil');
    }
}
