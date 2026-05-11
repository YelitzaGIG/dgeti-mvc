<?php
// index.php — Login + Registro del sistema
require_once __DIR__ . '/config/config.php';

// Si ya hay sesión activa, ir directo al dashboard
if (!empty($_SESSION['id_docente'])) {
    header('Location: Controlador/DocenteControlador.php?accion=dashboard');
    exit;
}

$error = $_GET['error'] ?? '';
$ok    = $_GET['ok']    ?? '';
// Si hubo error de registro, abrir pestaña registro automáticamente
$tab_activa = ($error === 'correo_duplicado' || $ok === 'registrado') ? 'registro' : 'login';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Acceso — CBTIS 199</title>
  <link rel="stylesheet" href="css/estilos.css"/>
  <style>
    .login-body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--color-bg, #f5f0ea);
    }
    .login-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 4px 32px rgba(98,17,50,.13);
      padding: 36px 40px 36px;
      width: 100%;
      max-width: 400px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .login-logo { margin-bottom: 14px; }
    .login-title {
      font-size: 20px;
      font-weight: 700;
      color: #621132;
      font-family: Georgia, serif;
      text-align: center;
      margin: 0 0 4px;
    }
    .login-sub {
      font-size: 13px;
      color: #888;
      text-align: center;
      margin: 0 0 22px;
      font-family: Arial, sans-serif;
    }

    /* ── PESTAÑAS ── */
    .tabs {
      display: flex;
      width: 100%;
      border-radius: 10px;
      overflow: hidden;
      border: 1.5px solid #e0cdd6;
      margin-bottom: 24px;
    }
    .tab-btn {
      flex: 1;
      padding: 10px 0;
      background: #f9f4f6;
      border: none;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      font-family: Arial, sans-serif;
      color: #999;
      transition: background .2s, color .2s;
    }
    .tab-btn.active {
      background: #621132;
      color: #D4C19C;
    }

    /* ── PANELES ── */
    .tab-panel { display: none; width: 100%; }
    .tab-panel.active { display: flex; flex-direction: column; gap: 14px; }

    /* ── CAMPOS ── */
    .login-field label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: #555;
      margin-bottom: 5px;
      text-transform: uppercase;
      letter-spacing: .04em;
      font-family: Arial, sans-serif;
    }
    .login-field input {
      width: 100%;
      padding: 11px 14px;
      border-radius: 9px;
      border: 1.5px solid #ddd;
      font-size: 14px;
      font-family: Arial, sans-serif;
      box-sizing: border-box;
      transition: border-color .2s;
      outline: none;
    }
    .login-field input:focus { border-color: #621132; }

    /* ── DOS COLUMNAS (nombre / apellido) ── */
    .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    /* ── MENSAJES ── */
    .msg {
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      font-family: Arial, sans-serif;
      text-align: center;
      width: 100%;
      box-sizing: border-box;
    }
    .msg-error   { background: #fde8e8; color: #8b1a1a; }
    .msg-success { background: #e8fde8; color: #1a5c1a; }

    /* ── BOTÓN ── */
    .btn-login {
      width: 100%;
      background: #621132;
      color: #D4C19C;
      border: none;
      border-radius: 10px;
      padding: 13px;
      font-size: 15px;
      font-weight: 700;
      font-family: Georgia, serif;
      cursor: pointer;
      margin-top: 4px;
      transition: background .2s;
    }
    .btn-login:hover { background: #7a1a3e; }
  </style>
</head>
<body class="login-body">
  <div class="login-card">

    <div class="login-logo">
      <svg width="52" height="52" viewBox="0 0 48 48" fill="none">
        <rect width="48" height="48" rx="12" fill="#621132"/>
        <path d="M14 34V20l10-8 10 8v14H30v-8h-4v8H14Z" fill="#D4C19C"/>
      </svg>
    </div>

    <h1 class="login-title">Sistema de Asistencias</h1>
    <p class="login-sub">CBTIS 199 — Acceso docente</p>

    <!-- ── PESTAÑAS ── -->
    <div class="tabs">
      <button class="tab-btn <?= $tab_activa === 'login'    ? 'active' : '' ?>"
              onclick="switchTab('login')"    type="button">Iniciar sesión</button>
      <button class="tab-btn <?= $tab_activa === 'registro' ? 'active' : '' ?>"
              onclick="switchTab('registro')" type="button">Registrarse</button>
    </div>

    <!-- ══════════════════════════════════════════
         PANEL LOGIN
    ══════════════════════════════════════════ -->
    <div id="panel-login" class="tab-panel <?= $tab_activa === 'login' ? 'active' : '' ?>">

      <?php if ($error === 'sesion'): ?>
        <div class="msg msg-error">Tu sesión expiró. Inicia sesión de nuevo.</div>
      <?php elseif ($error === 'credenciales'): ?>
        <div class="msg msg-error">Correo o contraseña incorrectos.</div>
      <?php endif; ?>

      <form action="Controlador/LoginControlador.php" method="POST">
        <div style="display:flex;flex-direction:column;gap:14px;">
          <div class="login-field">
            <label for="correo">Correo / Usuario</label>
            <input type="text" id="correo" name="correo" required
                   placeholder="tu@correo.com" autocomplete="username"/>
          </div>
          <div class="login-field">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required
                   placeholder="••••••••" autocomplete="current-password"/>
          </div>
          <button type="submit" class="btn-login">Entrar</button>
        </div>
      </form>

    </div>

    <!-- ══════════════════════════════════════════
         PANEL REGISTRO
    ══════════════════════════════════════════ -->
    <div id="panel-registro" class="tab-panel <?= $tab_activa === 'registro' ? 'active' : '' ?>">

      <?php if ($error === 'correo_duplicado'): ?>
        <div class="msg msg-error">Ese correo ya está registrado. Usa otro.</div>
      <?php elseif ($ok === 'registrado'): ?>
        <div class="msg msg-success">¡Usuario registrado! Ya puedes iniciar sesión.</div>
      <?php endif; ?>

      <form action="Controlador/UsuarioControlador.php" method="POST">
        <input type="hidden" name="accion" value="registrar"/>
        <div style="display:flex;flex-direction:column;gap:14px;">

          <div class="row-2">
            <div class="login-field">
              <label for="reg_nombre">Nombre</label>
              <input type="text" id="reg_nombre" name="nombre" required placeholder="Juan"/>
            </div>
            <div class="login-field">
              <label for="reg_apellido">Apellido</label>
              <input type="text" id="reg_apellido" name="apellido" required placeholder="Pérez"/>
            </div>
          </div>

          <div class="login-field">
            <label for="reg_correo">Correo</label>
            <input type="email" id="reg_correo" name="correo" required
                   placeholder="tu@correo.com" autocomplete="email"/>
          </div>

          <div class="login-field">
            <label for="reg_especialidad">Especialidad</label>
            <input type="text" id="reg_especialidad" name="especialidad"
                   placeholder="Ej. Informática, Electrónica…"/>
          </div>

          <div class="login-field">
            <label for="reg_password">Contraseña</label>
            <input type="password" id="reg_password" name="password" required
                   placeholder="Mínimo 6 caracteres" minlength="6"/>
          </div>

          <div class="login-field">
            <label for="reg_password2">Confirmar contraseña</label>
            <input type="password" id="reg_password2" name="password2" required
                   placeholder="Repite la contraseña" minlength="6"/>
          </div>

          <button type="submit" class="btn-login" onclick="return validarRegistro()">
            Crear cuenta
          </button>
        </div>
      </form>

    </div>

  </div><!-- /.login-card -->

  <script>
    function switchTab(tab) {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      document.getElementById('panel-' + tab).classList.add('active');
      event.currentTarget.classList.add('active');
    }

    function validarRegistro() {
      const p1 = document.getElementById('reg_password').value;
      const p2 = document.getElementById('reg_password2').value;
      if (p1 !== p2) {
        alert('Las contraseñas no coinciden.');
        return false;
      }
      return true;
    }
  </script>
</body>
</html>
