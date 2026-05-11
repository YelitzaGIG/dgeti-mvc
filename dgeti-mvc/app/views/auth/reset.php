<?php /* app/views/auth/reset.php */ ?>
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
      <p class="brand-panel-sub">Nueva contraseña</p>
    </div>
  </div>

  <div class="auth-form-panel">
    <a href="<?= APP_URL ?>/public/auth/login" class="back-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Regresar al login
    </a>
    <h1 class="form-heading">Nueva contraseña</h1>
    <p class="form-subheading">Ingresa el código recibido y tu nueva contraseña.</p>

    <form method="POST" action="<?= APP_URL ?>/public/auth/resetpost"
          class="auth-form" id="reset-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

      <div class="field-group">
        <label class="field-label">Código de recuperación</label>
        <input type="text" name="codigo" class="field field-code"
               placeholder="000000" maxlength="6" required
               inputmode="numeric" autocomplete="one-time-code">
      </div>

      <div class="field-group">
        <label class="field-label">Nueva contraseña</label>
        <div class="field-wrap">
          <input type="password" name="password" id="reset-pass" class="field"
                 placeholder="Mínimo 8 caracteres" required minlength="8"
                 oninput="validateField(this)">
          <button type="button" class="field-toggle-pass"
                  onclick="togglePassword('reset-pass')" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="strength-bar" id="strength-bar">
          <div class="strength-seg" id="seg1"></div>
          <div class="strength-seg" id="seg2"></div>
          <div class="strength-seg" id="seg3"></div>
          <div class="strength-seg" id="seg4"></div>
        </div>
        <span class="field-hint" id="hint-reset-pass"></span>
      </div>

      <div class="field-group">
        <label class="field-label">Verificar contraseña</label>
        <div class="field-wrap">
          <input type="password" name="password_confirm" id="reset-pass2" class="field"
                 placeholder="Repetir contraseña" required
                 oninput="validateField(this)">
          <button type="button" class="field-toggle-pass"
                  onclick="togglePassword('reset-pass2')" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <span class="field-hint" id="hint-reset-pass2"></span>
      </div>

      <button type="submit" class="btn-primary btn-full btn-submit">Realizar cambio de credenciales</button>
    </form>
  </div>
</div>

<script>
/* ── Reglas de validación ─────────────────────────────────── */
const RESET_RULES = {
  'reset-pass': {
    re:  /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/,
    ok:  '✓ Contraseña segura',
    err: 'Mín. 8 caracteres con mayúscula, minúscula, número y símbolo'
  },
  'reset-pass2': { custom: 'confirmPassword' }
};

/* ── Validador principal ──────────────────────────────────── */
function validateField(el) {
  const id   = el.id;
  const val  = el.value.trim();
  const hint = document.getElementById('hint-' + id);
  const rule = RESET_RULES[id];

  if (!rule) return true;

  if (!val) {
    setStatus(el, hint, 'idle', '');
    if (id === 'reset-pass') resetStrengthBar();
    return false;
  }

  if (rule.custom === 'confirmPassword') {
    const pass = document.getElementById('reset-pass').value;
    const ok   = val === pass && val.length > 0;
    setStatus(el, hint, ok ? 'ok' : 'err',
              ok ? '✓ Las contraseñas coinciden' : 'Las contraseñas no coinciden');
    return ok;
  }

  if (id === 'reset-pass') updateStrengthBar(val);

  const ok = rule.re.test(val);
  setStatus(el, hint, ok ? 'ok' : 'err', ok ? rule.ok : rule.err);
  return ok;
}

/* ── Aplica clases y mensaje ──────────────────────────────── */
function setStatus(el, hint, state, msg) {
  el.classList.remove('field--valid', 'field--invalid');
  if (state === 'ok')  el.classList.add('field--valid');
  if (state === 'err') el.classList.add('field--invalid');
  if (hint) {
    hint.textContent = msg;
    hint.className   = 'field-hint field-hint--' + state;
  }
}

/* ── Barra de fortaleza ───────────────────────────────────── */
function updateStrengthBar(val) {
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

/* ── Toggle visibilidad contraseña ───────────────────────── */
function togglePassword(id) {
  const input = document.getElementById(id);
  input.type  = input.type === 'password' ? 'text' : 'password';
}

/* ── Validar al hacer submit ──────────────────────────────── */
document.getElementById('reset-form')?.addEventListener('submit', function(e) {
  const fields = ['reset-pass', 'reset-pass2'];
  let allValid = true;

  fields.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;

    if (!el.value.trim()) {
      const hint = document.getElementById('hint-' + id);
      setStatus(el, hint, 'err', 'Este campo es obligatorio');
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