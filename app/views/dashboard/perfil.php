<?php /* app/views/dashboard/perfil.php */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Mi Perfil</h1>
    <p class="page-subtitle">Información de tu cuenta institucional</p>
  </div>
</div>

<div class="two-col-layout">
  <!-- Tarjeta de perfil -->
  <div class="section-card profile-card-full">
    <div class="profile-cover"></div>
    <div class="profile-body">
      <div class="profile-avatar-lg"><?= mb_strtoupper(mb_substr($user['nombre'], 0, 1)) ?></div>
      <h2 class="profile-name"><?= htmlspecialchars($user['nombre']) ?></h2>
      <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
      <span class="badge-role badge-<?= $user['rol'] ?>">
        <?= htmlspecialchars(ROLES_LABEL[$user['rol']] ?? ucfirst($user['rol'])) ?>
      </span>
      <div class="profile-meta-grid">
        <div class="meta-item">
          <span class="meta-key">Matrícula / ID</span>
          <span class="meta-val"><?= htmlspecialchars($user['matricula']) ?></span>
        </div>
        <div class="meta-item">
          <span class="meta-key">Grupo</span>
          <span class="meta-val"><?= htmlspecialchars($user['grupo']) ?></span>
        </div>
        <div class="meta-item">
          <span class="meta-key">Institución</span>
          <span class="meta-val">CETIS 193</span>
        </div>
        <div class="meta-item">
          <span class="meta-key">Ciclo</span>
          <span class="meta-val">2025-2026</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Formularios -->
  <div class="section-card">
    <h2 class="section-title">Actualizar información</h2>
    <form method="POST" action="<?= APP_URL ?>/public/dashboard/perfilpost" class="edit-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Nombre(s)</label>
          <input type="text" name="nombre" class="field"
                 value="<?= htmlspecialchars($user['nombre_corto'] ?? '') ?>" required>
        </div>
        <div class="field-group">
          <label class="field-label">Apellidos</label>
          <input type="text" name="apellido" class="field"
                 value="<?= htmlspecialchars($user['apellido'] ?? '') ?>">
        </div>
      </div>
      <div class="field-group">
        <label class="field-label">Correo institucional</label>
        <input type="email" class="field" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        <small style="color:var(--color-text-muted);font-family:var(--font-sans);font-size:.75rem;">
          El correo no se puede modificar desde aquí.
        </small>
      </div>
      <button type="submit" class="btn-primary">Guardar cambios</button>
    </form>

    <hr class="section-divider">

    <h2 class="section-title">Cambiar contraseña</h2>
    <form method="POST" action="<?= APP_URL ?>/public/dashboard/perfilpost" class="edit-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="change_password">
      <div class="field-group">
        <label class="field-label">Contraseña actual</label>
        <input type="password" name="password_current" class="field" placeholder="••••••••" required>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Nueva contraseña</label>
          <input type="password" name="password_new" class="field"
                 placeholder="Mín. 8 caracteres" required minlength="8">
        </div>
        <div class="field-group">
          <label class="field-label">Confirmar nueva</label>
          <input type="password" name="password_confirm" class="field"
                 placeholder="Repetir contraseña" required>
        </div>
      </div>
      <button type="submit" class="btn-outline">Actualizar contraseña</button>
    </form>
  </div>
</div>
