<?php
// ============================================================
// app/controllers/JustificantesController.php
// ============================================================

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/JustificanteModel.php';

class JustificantesController extends BaseController {

    private JustificanteModel $model;

    public function __construct() {
        $this->model = new JustificanteModel();
    }

    // GET /justificantes
    public function index(?string $p = null): void {
        $this->requireAuth();

        $user    = $_SESSION['user'];
        $flash   = $this->getFlash();
        $csrf    = $this->csrfToken();

        $filters = [];

        // Alumno solo ve los suyos
        if ($user['rol'] === 'alumno' && !empty($user['id_alumno'])) {
            $filters['id_alumno'] = $user['id_alumno'];
        }

        if (!empty($_GET['estado'])) $filters['estado'] = $_GET['estado'];
        if (!empty($_GET['motivo'])) $filters['motivo'] = $_GET['motivo'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];

        $justificantes = $this->model->getAll($filters);
        $stats         = $this->model->getStats(
            $user['rol'] === 'alumno' ? ($user['id_alumno'] ?? null) : null
        );

        $this->render('justificantes/index',
            compact('user', 'flash', 'csrf', 'justificantes', 'stats', 'filters'),
            'main'
        );
    }

    // GET /justificantes/create
    public function create(?string $p = null): void {
        $this->requireAuth();

        $user       = $_SESSION['user'];
        $flash      = $this->getFlash();
        $csrf       = $this->csrfToken();
        $asistencias = [];

        // Si es alumno, cargar sus ausencias sin justificante
        if ($user['rol'] === 'alumno' && !empty($user['id_alumno'])) {
            $asistencias = $this->model->getAsistenciasByAlumno((int) $user['id_alumno']);
        }

        $this->render('justificantes/create', compact('user', 'flash', 'csrf', 'asistencias'), 'main');
    }

    // POST /justificantes/store
    public function store(?string $p = null): void {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('justificantes/create');
        $this->verifyCsrf();

        $user = $_SESSION['user'];

        // Resolver id_alumno: alumno → desde sesión; otros roles → desde BD por matrícula
        $idAlumno = null;
        if ($user['rol'] === 'alumno') {
            $idAlumno = $user['id_alumno'] ?? null;
        } else {
            // Roles con permiso de registrar: docente, orientadora, jefa_servicios, tutor
            $matricula = $this->post('numero_control');
            if (!empty($matricula)) {
                $idAlumno = $this->model->getIdAlumnoByMatricula($matricula);
            }
        }

        $motivo = $this->post('motivo');
        $errors = [];

        if (empty($idAlumno)) {
            $errors[] = 'No se pudo identificar al alumno.';
        }
        if (!in_array($motivo, MOTIVOS)) {
            $errors[] = 'Motivo no válido.';
        }

        if ($errors) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('justificantes/create');
        }

        $data = [
            'id_alumno'          => $idAlumno,
            'tipo_motivo'        => $motivo,
            'descripcion_motivo' => $this->post('descripcion_motivo'),
            'id_asistencia'      => !empty($_POST['id_asistencia']) ? (int) $_POST['id_asistencia'] : null,
        ];

        $newId = $this->model->create($data);

        if ($newId) {
            $this->setFlash('success', "Justificante <strong>#$newId</strong> registrado correctamente.");
        } else {
            $this->setFlash('error', 'Error al crear el justificante. Intenta de nuevo.');
        }

        $this->redirect('justificantes');
    }

    // GET /justificantes/show/{id}
    public function show(?string $id = null): void {
        $this->requireAuth();
        $j = $this->model->getById((int) $id);
        if (!$j) {
            $this->setFlash('error', 'Justificante no encontrado.');
            $this->redirect('justificantes');
        }

        // Alumno solo puede ver los suyos
        $user = $_SESSION['user'];
        if ($user['rol'] === 'alumno' && $j['numero_control'] !== $user['matricula']) {
            $this->setFlash('error', 'No tienes permiso para ver este justificante.');
            $this->redirect('justificantes');
        }

        $flash = $this->getFlash();
        $csrf  = $this->csrfToken();
        $this->render('justificantes/show', compact('user', 'flash', 'csrf', 'j'), 'main');
    }

    // GET /justificantes/edit/{id}
    public function edit(?string $id = null): void {
        $this->requireRole('docente', 'orientadora', 'jefa_servicios', 'tutor_institucional');
        $j = $this->model->getById((int) $id);
        if (!$j) {
            $this->setFlash('error', 'Justificante no encontrado.');
            $this->redirect('justificantes');
        }

        $user  = $_SESSION['user'];
        $flash = $this->getFlash();
        $csrf  = $this->csrfToken();
        $this->render('justificantes/edit', compact('user', 'flash', 'csrf', 'j'), 'main');
    }

    // POST /justificantes/update/{id}
    public function update(?string $id = null): void {
        $this->requireRole('docente', 'orientadora', 'jefa_servicios', 'tutor_institucional');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('justificantes');
        $this->verifyCsrf();

        $data = [
            'tipo_motivo'        => $this->post('motivo'),
            'descripcion_motivo' => $this->post('descripcion_motivo'),
            'estado'             => $this->post('estado'),
            'observaciones'      => $this->post('observaciones'),
        ];

        if ($this->model->update((int) $id, $data)) {
            $this->setFlash('success', 'Justificante actualizado correctamente.');
        } else {
            $this->setFlash('error', 'Error al actualizar el justificante.');
        }
        $this->redirect('justificantes');
    }

    // POST /justificantes/delete/{id}
    public function delete(?string $id = null): void {
        $this->requireRole('jefa_servicios');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('justificantes');
        $this->verifyCsrf();

        if ($this->model->delete((int) $id)) {
            $this->setFlash('success', 'Justificante eliminado correctamente.');
        } else {
            $this->setFlash('error', 'Error al eliminar el justificante.');
        }
        $this->redirect('justificantes');
    }
}
