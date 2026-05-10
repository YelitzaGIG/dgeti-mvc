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
      <h2 class="brand-panel-title">DGETI</h2>
      <p class="brand-panel-sub">Crea tu cuenta institucional</p>
    </div>
  </div>

  <div class="auth-form-panel auth-form-panel--scroll">
    <a href="<?= APP_URL ?>/public/auth/login" class="back-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Regresar
    </a>
    <h1 class="form-heading">Crear cuenta</h1>
    <p class="form-subheading">Completa los datos para registrarte</p>

    <form method="POST" action="<?= APP_URL ?>/public/auth/registerpost"
          class="auth-form" id="register-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Nombres</label>
          <input type="text" name="nombres" id="nombres" class="field"
                 placeholder="Nombre(s)" required oninput="validateField(this)">
          <span class="field-hint" id="hint-nombres"></span>
        </div>
        <div class="field-group">
          <label class="field-label">Apellidos</label>
          <input type="text" name="apellidos" id="apellidos" class="field"
                 placeholder="Apellido Apellido" required oninput="validateField(this)">
          <span class="field-hint" id="hint-apellidos"></span>
        </div>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Matrícula / N° Control</label>
          <input type="text" name="matricula" id="matricula" class="field"
                 placeholder="CETIS-0000" required oninput="validateField(this)"
                 style="text-transform:uppercase">
          <span class="field-hint" id="hint-matricula"></span>
        </div>
        <div class="field-group">
          <label class="field-label">Semestre</label>
          <select name="semestre" class="field field-select">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?>°</option>
            <?php endfor; ?>
          </select>
        </div>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Grupo</label>
          <input type="text" name="grupo" id="grupo" class="field"
                 placeholder="Ej: ISC-401" oninput="validateField(this)"
                 style="text-transform:uppercase">
          <span class="field-hint" id="hint-grupo"></span>
        </div>
        <div class="field-group">
          <label class="field-label">Rol</label>
          <select name="rol" class="field field-select">
            <option value="alumno">Alumno</option>
            <option value="docente">Docente</option>
          </select>
        </div>
      </div>

      <div class="field-group">
        <label class="field-label">Correo electrónico institucional</label>
        <div class="field-wrap">
          <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <input type="email" name="email" id="email" class="field"
                 placeholder="correo@cetis.edu.mx" required oninput="validateField(this)">
        </div>
        <span class="field-hint" id="hint-email"></span>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Contraseña</label>
          <div class="field-wrap">
            <input type="password" name="password" id="reg-pass" class="field"
                   placeholder="Mín. 8 caracteres" required minlength="8"
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
          <span class="field-hint" id="hint-reg-pass"></span>
        </div>
        <div class="field-group">
          <label class="field-label">Confirmar contraseña</label>
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

<script>
/* ── Reglas de validación─────────────────────────────── */
const RULES = {
  nombres: {
    re:  /^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ]+(?: [a-záéíóúüñA-ZÁÉÍÓÚÜÑ]+)*$/,
    ok:  'Nombre válido',
    err: 'Solo letras; separa cada nombre con un espacio'
  },
  apellidos: {
    re:  /^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ]+(?: [a-záéíóúüñA-ZÁÉÍÓÚÜÑ]+)*$/,
    ok:  'Apellidos válidos',
    err: 'Solo letras; separa con un espacio'
  },
  matricula: {
    // Acepta: CETIS-1234, CBTIS-001, CBTis0012, etc.
    re:  /^[A-Z]{2,8}-?\d{3,6}$/i,
    ok:  'Matrícula válida',
    err: 'Formato: LETRAS-NÚMEROS (ej: CETIS-1234)'
  },
  grupo: {
    // Acepta: ISC-401, A1, TICS-301 — campo opcional
    re:  /^[A-Z0-9]{1,6}(?:-[A-Z0-9]{1,6})?$/i,
    ok:  'Grupo válido',
    err: 'Formato: LETRAS-NÚMS (ej: ISC-401)'
  },
  email: {
    // Solo dominios institucionales DGETI
    re:  /^[\w.+\-]+@(?:cetis|cbtis|dgeti\.sep)\.(?:edu\.mx|gob\.mx)$/i,
    ok:  'Correo institucional válido',
    err: 'Usa tu correo @cetis.edu.mx, @cbtis.edu.mx o @dgeti.sep.gob.mx'
  },
  'reg-pass': {
    // Mín 8 chars: 1 mayúscula, 1 minúscula, 1 dígito, 1 símbolo
    re:  /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/,
    ok:  'Contraseña segura',
    err: 'Mín. 8 caracteres con mayúscula, minúscula, número y símbolo'
  },
  'reg-pass2': { custom: 'confirmPassword' }
};

/* ── Validador principal ─────────────────────────────────────────── */
function validateField(el) {
  const id   = el.id;
  const val  = el.value.trim();
  const hint = document.getElementById('hint-' + id);
  const rule = RULES[id];

  if (!rule) return true;

  // Campo vacío → estado neutral
  if (!val) {
    setStatus(el, hint, 'idle', '');
    if (id === 'reg-pass') resetStrengthBar();
    return false;
  }

  // Validación especial: confirmar contraseña
  if (rule.custom === 'confirmPassword') {
    const pass = document.getElementById('reg-pass').value;
    const ok   = val === pass && val.length > 0;
    setStatus(el, hint, ok ? 'ok' : 'err',
              ok ? '✓ Las contraseñas coinciden' : 'Las contraseñas no coinciden');
    return ok;
  }

  // Barra de fortaleza para contraseña
  if (id === 'reg-pass') updateStrengthBar(val);

  const ok = rule.re.test(val);
  setStatus(el, hint, ok ? 'ok' : 'err', ok ? rule.ok : rule.err);
  return ok;
}

/* ── Aplica clases CSS y mensaje de hint ─────────────────────────── */
function setStatus(el, hint, state, msg) {
  el.classList.remove('field--valid', 'field--invalid');
  if (state === 'ok')  el.classList.add('field--valid');
  if (state === 'err') el.classList.add('field--invalid');
  if (hint) {
    hint.textContent = msg;
    hint.className   = 'field-hint field-hint--' + state;
  }
}

/* ── Barra de fortaleza de contraseña ───────────────────────────── */
function updateStrengthBar(val) {
  const bar = document.getElementById('strength-bar');
  if (!bar) return;

  const checks = [
    val.length >= 8,
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

/* ── Toggle visibilidad de contraseña ───────────────────────────── */
function togglePassword(id) {
  const input = document.getElementById(id);
  input.type  = input.type === 'password' ? 'text' : 'password';
}

/* ── Validar todo al hacer submit ───────────────────────────────── */
document.getElementById('register-form')?.addEventListener('submit', function(e) {
  // Campos requeridos a validar
  const required = ['nombres', 'apellidos', 'matricula', 'email', 'reg-pass', 'reg-pass2'];
  // Campos opcionales que igual se validan si tienen contenido
  const optional = ['grupo'];
  let allValid = true;

  [...required, ...optional].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;

    if (required.includes(id) && !el.value.trim()) {
      // Campo requerido vacío
      const hint = document.getElementById('hint-' + id);
      setStatus(el, hint, 'err', 'Este campo es obligatorio');
      allValid = false;
    } else if (el.value.trim()) {
      // Tiene contenido: validar con regex
      if (!validateField(el)) allValid = false;
    }
  });

  if (!allValid) {
    e.preventDefault();
    // Scroll suave al primer error
    const firstInvalid = document.querySelector('.field--invalid');
    if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});
</script>