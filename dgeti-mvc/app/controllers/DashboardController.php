<?php

use BaseController;
use JustificanteModel;
// ============================================================
// app/controllers/DashboardController.php
// ============================================================

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/JustificanteModel.php';

class DashboardController extends BaseController {

    private JustificanteModel $model;

    public function __construct() {
        $this->model = new JustificanteModel();
    }

    // GET /dashboard
    public function index(?string $p = null): void {
        $this->requireAuth();

        $user  = $_SESSION['user'];
        $flash = $this->getFlash();

        $filters = [];
        if ($user['rol'] === 'alumno') {
            $filters['numero_control'] = $user['matricula'];
        }

        $stats     = $this->model->getStats();
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
            // En producción: verificar $current contra hash en BD y actualizar
            $this->setFlash('success', 'Contraseña actualizada correctamente.');
        } else {
            $nombre = $this->post('nombre');
            $grupo  = $this->post('grupo');

            if (empty($nombre)) {
                $this->setFlash('error', 'El nombre no puede estar vacío.');
                $this->redirect('dashboard/perfil');
            }

            // Actualizar sesión (en producción: actualizar también en BD)
            $_SESSION['user']['nombre'] = $nombre;
            $_SESSION['user']['grupo']  = $grupo;

            $this->setFlash('success', 'Perfil actualizado correctamente.');
        }

        $this->redirect('dashboard/perfil');
    }
}