<?php /* app/views/dashboard/perfil.php */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Mi Perfil</h1>
    <p class="page-subtitle">Información de tu cuenta institucional</p>
  </div>
</div>

<div class="two-col-layout">
  <!-- Tarjeta de perfil (solo lectura) -->
  <div class="section-card profile-card-full">
    <div class="profile-cover"></div>
    <div class="profile-body">
      <div class="profile-avatar-lg"><?= mb_strtoupper(mb_substr($user['nombre_corto'] ?? $user['nombre'], 0, 1)) ?></div>
      <h2 class="profile-name"><?= htmlspecialchars($user['nombre']) ?></h2>
      <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
      <span class="badge-role badge-<?= $user['rol'] ?>">
        <?= htmlspecialchars(ROLES_LABEL[$user['rol']] ?? ucfirst($user['rol'])) ?>
      </span>
      <div class="profile-meta-grid">
        <div class="meta-item">
          <span class="meta-key">Matrícula / ID</span>
          <span class="meta-val"><?= htmlspecialchars($user['matricula'] ?? '—') ?></span>
        </div>
        <div class="meta-item">
          <span class="meta-key">Grupo</span>
          <span class="meta-val"><?= htmlspecialchars($user['grupo'] ?? 'N/A') ?></span>
        </div>
        <?php if (!empty($user['identificador'])): ?>
        <div class="meta-item">
          <span class="meta-key"><?= $user['rol'] === 'alumno' ? 'CURP' : 'RFC' ?></span>
          <span class="meta-val" style="font-family:'Courier New',monospace;font-size:.8rem;">
            <?= htmlspecialchars($user['identificador']) ?>
          </span>
        </div>
        <?php endif; ?>
        <?php if (!empty($user['telefono'])): ?>
        <div class="meta-item">
          <span class="meta-key">Teléfono</span>
          <span class="meta-val"><?= htmlspecialchars($user['telefono']) ?></span>
        </div>
        <?php endif; ?>
        <div class="meta-item">
          <span class="meta-key">Institución</span>
          <span class="meta-val">CBTIS 199</span>
        </div>
        <div class="meta-item">
          <span class="meta-key">Ciclo</span>
          <span class="meta-val">2025-2026</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Solo cambio de contraseña -->
  <div class="section-card">
    <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= $flash['type'] ?> animate-fadein">
      <?= $flash['msg'] ?>
    </div>
    <?php endif; ?>

    <h2 class="section-title">Cambiar contraseña</h2>
    <p style="font-family:var(--font-sans);font-size:.83rem;color:var(--color-text-muted);margin-bottom:var(--space-5);">
      El nombre, correo, CURP/RFC y teléfono son datos fijos y no pueden modificarse desde aquí. Contacta a Servicios Escolares para actualizarlos.
    </p>

    <form method="POST" action="<?= APP_URL ?>/public/dashboard/perfilpost" class="edit-form" id="form-perfil">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="change_password">

      <div class="field-group">
        <label class="field-label">Contraseña actual</label>
        <div class="field-wrap">
          <input type="password" name="password_current" id="pass-current" class="field"
                 placeholder="••••••••" required autocomplete="current-password">
          <button type="button" class="field-toggle-pass" onclick="togglePassword('pass-current')" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Nueva contraseña</label>
          <div class="field-wrap">
            <input type="password" name="password_new" id="pass-new" class="field"
                   placeholder="Exactamente 8 caracteres" required
                   minlength="8" maxlength="8"
                   autocomplete="new-password"
                   oninput="validatePassNew(this)">
            <button type="button" class="field-toggle-pass" onclick="togglePassword('pass-new')" tabindex="-1">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <span class="field-hint" id="hint-pass-new">Mayúscula, minúscula, número y símbolo</span>
        </div>
        <div class="field-group">
          <label class="field-label">Confirmar nueva</label>
          <div class="field-wrap">
            <input type="password" name="password_confirm" id="pass-confirm" class="field"
                   placeholder="Repetir contraseña" required
                   autocomplete="new-password"
                   oninput="validatePassConfirm(this)">
            <button type="button" class="field-toggle-pass" onclick="togglePassword('pass-confirm')" tabindex="-1">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <span class="field-hint" id="hint-pass-confirm"></span>
        </div>
      </div>

      <div style="padding-top:var(--space-2)">
        <button type="submit" class="btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          Actualizar contraseña
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const RE_PASS = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,8}$/;

function validatePassNew(el) {
  const hint = document.getElementById('hint-pass-new');
  const val  = el.value;
  if (!val) { setPassStatus(el, hint, 'idle', 'Mayúscula, minúscula, número y símbolo'); return; }
  const ok = RE_PASS.test(val);
  setPassStatus(el, hint, ok ? 'ok' : 'err',
    ok ? '✓ Contraseña válida' : 'Exactamente 8 caracteres con mayúscula, minúscula, número y símbolo');
}

function validatePassConfirm(el) {
  const hint = document.getElementById('hint-pass-confirm');
  const val  = el.value;
  const pass = document.getElementById('pass-new').value;
  if (!val) { setPassStatus(el, hint, 'idle', ''); return; }
  const ok = val === pass && val.length > 0;
  setPassStatus(el, hint, ok ? 'ok' : 'err',
    ok ? '✓ Las contraseñas coinciden' : 'Las contraseñas no coinciden');
}

function setPassStatus(el, hint, state, msg) {
  el.classList.remove('field--valid', 'field--invalid');
  if (state === 'ok')  el.classList.add('field--valid');
  if (state === 'err') el.classList.add('field--invalid');
  if (hint) {
    hint.textContent = msg;
    hint.className   = 'field-hint field-hint--' + state;
  }
}

function togglePassword(id) {
  const input = document.getElementById(id);
  input.type  = input.type === 'password' ? 'text' : 'password';
}
</script>
