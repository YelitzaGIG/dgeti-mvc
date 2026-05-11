<?php /* app/views/auth/register.php */ ?>
<div class="auth-split">
  <div class="auth-brand-panel">
    <div class="brand-panel-content">
      <svg viewBox="0 0 120 120" class="brand-gear" xmlns="http://www.w3.org/2000/svg">
        <g fill="none">
          <circle cx="60" cy="60" r="36" stroke="white" stroke-width="3"/>
          <circle cx="60" cy="60" r="22" stroke="white" stroke-width="2.5"/>
          <g stroke="white" stroke-width="3" stroke-linecap="round">
            <line x1="60" y1="18" x2="60" y2="9"/><line x1="60" y1="102" x2="60" y2="111"/>
            <line x1="18" y1="60" x2="9" y2="60"/><line x1="102" y1="60" x2="111" y2="60"/>
            <line x1="30" y1="30" x2="23" y2="23"/><line x1="90" y1="90" x2="97" y2="97"/>
            <line x1="90" y1="30" x2="97" y2="23"/><line x1="30" y1="90" x2="23" y2="97"/>
          </g>
          <path d="M44 55L52 55L52 50L68 60L52 70L52 65L44 65Z" fill="white" opacity=".9"/>
        </g>
        <text x="42" y="55" font-family="Montserrat,sans-serif" font-weight="800" font-size="13" fill="white">DG</text>
        <text x="40" y="71" font-family="Montserrat,sans-serif" font-weight="800" font-size="13" fill="white">ETi</text>
      </svg>
      <h2 class="brand-panel-title">CBTIS</h2>
      <p class="brand-panel-sub">Crea tu cuenta institucional</p>
    </div>
  </div>

  <div class="auth-form-panel auth-form-panel--scroll">
    <a href="<?= APP_URL ?>/public/auth/login" class="back-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Regresar
    </a>
    <h1 class="form-heading">Crear cuenta</h1>
    <p class="form-subheading">Completa los datos para registrarte en el sistema</p>

    <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= $flash['type'] ?> animate-fadein">
      <?= $flash['msg'] ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/public/auth/registerpost"
          class="auth-form" id="register-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

      <!-- Nombres y apellidos -->
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Nombres <span class="required">*</span></label>
          <input type="text" name="nombres" id="nombres" class="field"
                 placeholder="Nombre(s)" required oninput="validateField(this)">
          <span class="field-hint" id="hint-nombres"></span>
        </div>
        <div class="field-group">
          <label class="field-label">Apellidos <span class="required">*</span></label>
          <input type="text" name="apellidos" id="apellidos" class="field"
                 placeholder="Apellido Apellido" required oninput="validateField(this)">
          <span class="field-hint" id="hint-apellidos"></span>
        </div>
      </div>

      <!-- Rol -->
      <div class="field-group">
        <label class="field-label">Rol <span class="required">*</span></label>
        <select name="rol" id="rol" class="field field-select" onchange="onRolChange(this.value)" required>
          <option value="">— Selecciona tu rol —</option>
          <option value="alumno">Alumno</option>
          <option value="docente">Docente</option>
          <option value="tutor_institucional">Tutor Institucional</option>
          <option value="orientadora">Orientadora</option>
          <option value="jefa_servicios">Jefa de Servicios</option>
        </select>
        <span class="field-hint" id="hint-rol"></span>
      </div>

      <!-- Identificador (CURP / RFC) -->
      <div class="field-group">
        <label class="field-label" id="label-identificador">
          CURP / RFC <span class="required">*</span>
          <span class="badge-info" id="badge-identificador" style="margin-left:.4rem;font-size:.7rem;padding:.1rem .45rem;border-radius:999px;background:rgba(98,17,50,.1);color:var(--color-primary);font-weight:700;"></span>
        </label>
        <div class="field-wrap">
          <input type="text" name="identificador" id="identificador" class="field"
                 placeholder="Selecciona primero tu rol" required
                 oninput="validateField(this)"
                 style="text-transform:uppercase" maxlength="18">
        </div>
        <span class="field-hint" id="hint-identificador">
          Alumno: CURP (18 caracteres) · Personal: RFC con homoclave (13 caracteres)
        </span>
      </div>

      <!-- Teléfono -->
      <div class="field-group">
        <label class="field-label">Teléfono celular <span class="required">*</span></label>
        <div class="field-wrap">
          <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.65 3.18 2 2 0 0 1 3.62 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.55a16 16 0 0 0 6.55 6.55l.71-.71a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
          </svg>
          <input type="tel" name="telefono" id="telefono" class="field"
                 placeholder="+52 772 000 0000" required
                 oninput="maskTelefono(this)" maxlength="16">
        </div>
        <span class="field-hint" id="hint-telefono">Formato: +52 772 XXX XXXX (área Querétaro)</span>
      </div>

      <!-- Correo -->
      <div class="field-group">
        <label class="field-label">Correo institucional <span class="required">*</span></label>
        <div class="field-wrap">
          <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          <input type="email" name="email" id="email" class="field"
                 placeholder="correo@cbtis.edu.mx" required oninput="validateField(this)">
        </div>
        <span class="field-hint" id="hint-email"></span>
      </div>

      <!-- Contraseña -->
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Contraseña <span class="required">*</span></label>
          <div class="field-wrap">
            <input type="password" name="password" id="reg-pass" class="field"
                   placeholder="Exactamente 8 caracteres" required minlength="8" maxlength="8"
                   oninput="validateField(this)">
            <button type="button" class="field-toggle-pass"
                    onclick="togglePassword('reg-pass')" tabindex="-1">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div class="strength-bar" id="strength-bar">
            <div class="strength-seg" id="seg1"></div>
            <div class="strength-seg" id="seg2"></div>
            <div class="strength-seg" id="seg3"></div>
            <div class="strength-seg" id="seg4"></div>
          </div>
          <span class="field-hint" id="hint-reg-pass">Debe tener mayúscula, minúscula, número y símbolo</span>
        </div>
        <div class="field-group">
          <label class="field-label">Confirmar contraseña <span class="required">*</span></label>
          <div class="field-wrap">
            <input type="password" name="password_confirm" id="reg-pass2" class="field"
                   placeholder="Repetir contraseña" required oninput="validateField(this)">
            <button type="button" class="field-toggle-pass"
                    onclick="togglePassword('reg-pass2')" tabindex="-1">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <span class="field-hint" id="hint-reg-pass2"></span>
        </div>
      </div>

      <button type="submit" class="btn-primary btn-full btn-submit">Crear cuenta</button>
    </form>

    <p class="auth-alt-link">¿Ya tienes cuenta? <a href="<?= APP_URL ?>/public/auth/login">Inicia sesión</a></p>
  </div>
</div>

<style>
/* Estilos extra para strength bar */
.strength-bar{display:flex;gap:4px;margin-top:6px;height:4px;}
.strength-seg{flex:1;border-radius:2px;background:#e5e7eb;transition:background .3s;}
</style>

<script>
// ── Estado global de rol ──────────────────────────────────
let currentRol = '';

// Cambia las restricciones del campo identificador según el rol
function onRolChange(rol) {
  currentRol = rol;
  const input  = document.getElementById('identificador');
  const badge  = document.getElementById('badge-identificador');
  const hint   = document.getElementById('hint-identificador');

  if (rol === 'alumno') {
    input.maxLength   = 18;
    input.placeholder = 'CURP (18 caracteres)';
    badge.textContent = 'CURP · 18 chars';
    hint.textContent  = 'Ingresa tu CURP completa (18 caracteres)';
  } else if (rol) {
    input.maxLength   = 13;
    input.placeholder = 'RFC con homoclave (13 caracteres)';
    badge.textContent = 'RFC · 13 chars';
    hint.textContent  = 'Ingresa tu RFC con homoclave (13 caracteres)';
  } else {
    input.maxLength   = 18;
    input.placeholder = 'Selecciona primero tu rol';
    badge.textContent = '';
    hint.textContent  = 'Alumno: CURP (18) · Personal: RFC (13)';
  }

  // Re-validar si ya tiene contenido
  if (input.value.trim()) validateField(input);
}

// ── Máscara de teléfono ───────────────────────────────────
function maskTelefono(el) {
  let num = el.value.replace(/\D/g, '');
  // Quitar el 52 inicial si lo incluyeron
  if (num.startsWith('52') && num.length > 10) num = num.slice(2);

  let fmt = '+52';
  if (num.length > 0)  fmt += ' ' + num.substring(0, 3);
  if (num.length > 3)  fmt += ' ' + num.substring(3, 6);
  if (num.length > 6)  fmt += ' ' + num.substring(6, 10);

  el.value = fmt;

  // Validar
  const hint  = document.getElementById('hint-telefono');
  const ok    = /^\+52 (772|773) \d{3} \d{4}$/.test(fmt);
  const ready = num.length >= 10;
  setStatus(el, hint,
    ready ? (ok ? 'ok' : 'err') : 'idle',
    ready ? (ok ? '✓ Teléfono válido' : 'El número debe ser de Querétaro (+52 772 / 773)') : 'Ingresa los 10 dígitos de tu celular'
  );
}

// ── Reglas de validación ──────────────────────────────────
const RULES = {
  nombres: {
    re:  /^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ]+(?: [a-záéíóúüñA-ZÁÉÍÓÚÜÑ]+)*$/,
    ok:  '✓ Nombre válido',
    err: 'Solo letras; separa nombres con un espacio'
  },
  apellidos: {
    re:  /^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ]+(?: [a-záéíóúüñA-ZÁÉÍÓÚÜÑ]+)*$/,
    ok:  '✓ Apellidos válidos',
    err: 'Solo letras; separa con un espacio'
  },
  identificador: {
    custom: 'identificador'
  },
  email: {
    re:  /^[\w.+\-]+@cbtis\.edu\.mx$/i,
    ok:  '✓ Correo institucional válido',
    err: 'Usa tu correo @cbtis.edu.mx'
  },
  'reg-pass': {
    re:  /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,8}$/,
    ok:  '✓ Contraseña válida (8 caracteres)',
    err: 'Exactamente 8 caracteres: mayúscula, minúscula, número y símbolo'
  },
  'reg-pass2': { custom: 'confirmPassword' }
};

// ── Validador principal ───────────────────────────────────
function validateField(el) {
  const id   = el.id;
  const val  = el.value.trim();
  const hint = document.getElementById('hint-' + id);
  const rule = RULES[id];

  if (!rule) return true;

  if (!val) {
    setStatus(el, hint, 'idle', '');
    if (id === 'reg-pass') resetStrengthBar();
    return false;
  }

  // Confirmar contraseña
  if (rule.custom === 'confirmPassword') {
    const pass = document.getElementById('reg-pass').value;
    const ok   = val === pass && val.length > 0;
    setStatus(el, hint, ok ? 'ok' : 'err',
              ok ? '✓ Las contraseñas coinciden' : 'Las contraseñas no coinciden');
    return ok;
  }

  // Identificador (CURP / RFC según rol)
  if (rule.custom === 'identificador') {
    const upper = val.toUpperCase();
    el.value = upper;
    let ok, msg;
    if (currentRol === 'alumno') {
      ok  = upper.length === 18;
      msg = ok ? '✓ CURP válida' : `CURP: ${upper.length}/18 caracteres`;
    } else if (currentRol) {
      ok  = upper.length === 13;
      msg = ok ? '✓ RFC válido' : `RFC: ${upper.length}/13 caracteres`;
    } else {
      ok  = false;
      msg = 'Selecciona primero tu rol';
    }
    setStatus(el, hint, ok ? 'ok' : 'err', msg);
    return ok;
  }

  // Barra de fortaleza
  if (id === 'reg-pass') updateStrengthBar(val);

  const ok = rule.re.test(val);
  setStatus(el, hint, ok ? 'ok' : 'err', ok ? rule.ok : rule.err);
  return ok;
}

// ── Helpers de UI ─────────────────────────────────────────
function setStatus(el, hint, state, msg) {
  el.classList.remove('field--valid', 'field--invalid');
  if (state === 'ok')  el.classList.add('field--valid');
  if (state === 'err') el.classList.add('field--invalid');
  if (hint) {
    hint.textContent = msg;
    hint.className   = 'field-hint field-hint--' + state;
  }
}

function updateStrengthBar(val) {
  const checks = [
    val.length === 8,
    /[A-Z]/.test(val) && /[a-z]/.test(val),
    /\d/.test(val),
    /[\W_]/.test(val)
  ];
  const score  = checks.filter(Boolean).length;
  const colors = { 1: '#ef4444', 2: '#f97316', 3: '#eab308', 4: '#22c55e' };
  for (let i = 1; i <= 4; i++) {
    const seg = document.getElementById('seg' + i);
    if (seg) seg.style.background = i <= score ? (colors[score] || '#e5e7eb') : '#e5e7eb';
  }
}

function resetStrengthBar() {
  for (let i = 1; i <= 4; i++) {
    const seg = document.getElementById('seg' + i);
    if (seg) seg.style.background = '#e5e7eb';
  }
}

function togglePassword(id) {
  const input = document.getElementById(id);
  input.type  = input.type === 'password' ? 'text' : 'password';
}

// ── Validar todo al hacer submit ──────────────────────────
document.getElementById('register-form')?.addEventListener('submit', function(e) {
  const required = ['nombres', 'apellidos', 'identificador', 'email', 'reg-pass', 'reg-pass2'];
  let allValid = true;

  // Validar rol
  const rolEl = document.getElementById('rol');
  if (!rolEl.value) {
    const hint = document.getElementById('hint-rol');
    if (hint) { hint.textContent = 'Selecciona un rol'; hint.className = 'field-hint field-hint--err'; }
    rolEl.classList.add('field--invalid');
    allValid = false;
  }

  // Validar teléfono
  const telEl  = document.getElementById('telefono');
  const telOk  = /^\+52 (772|773) \d{3} \d{4}$/.test(telEl.value);
  if (!telOk) {
    setStatus(telEl, document.getElementById('hint-telefono'), 'err', 'Ingresa un teléfono válido de Querétaro');
    allValid = false;
  }

  required.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    if (!el.value.trim()) {
      setStatus(el, document.getElementById('hint-' + id), 'err', 'Este campo es obligatorio');
      allValid = false;
    } else if (!validateField(el)) {
      allValid = false;
    }
  });

  if (!allValid) {
    e.preventDefault();
    const firstInvalid = document.querySelector('.field--invalid');
    if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});
</script>
