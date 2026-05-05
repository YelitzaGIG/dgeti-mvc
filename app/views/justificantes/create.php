<?php /* app/views/justificantes/create.php */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Nuevo Justificante</h1>
    <p class="page-subtitle">Registra un nuevo justificante de ausencia</p>
  </div>
  <a href="<?= APP_URL ?>/public/justificantes" class="btn-outline">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Regresar
  </a>
</div>

<div class="form-layout">
  <div class="section-card form-card">
    <form method="POST" action="<?= APP_URL ?>/public/justificantes/store" class="create-form" id="createForm">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

      <!-- Datos del alumno (solo visible para roles distintos a alumno) -->
      <?php if ($user['rol'] !== 'alumno'): ?>
      <div class="form-section">
        <h3 class="form-section-title">
          <span class="form-section-num">1</span>
          Datos del alumno
        </h3>
        <div class="field-group">
          <label class="field-label" for="numero_control">Matrícula / N° Control <span class="required">*</span></label>
          <input type="text" id="numero_control" name="numero_control" class="field"
                 placeholder="CETIS-0001" required>
          <small style="color:var(--color-text-muted);font-size:.75rem;font-family:var(--font-sans);">
            El sistema buscará al alumno por su matrícula.
          </small>
        </div>
      </div>
      <?php else: ?>
      <!-- Alumno: sus datos van ocultos desde sesión -->
      <div class="form-section">
        <h3 class="form-section-title">
          <span class="form-section-num">1</span>
          Tus datos
        </h3>
        <div class="field-row">
          <div class="field-group">
            <label class="field-label">Nombre</label>
            <input type="text" class="field" value="<?= htmlspecialchars($user['nombre']) ?>" readonly>
          </div>
          <div class="field-group">
            <label class="field-label">Matrícula</label>
            <input type="text" class="field" value="<?= htmlspecialchars($user['matricula']) ?>" readonly>
          </div>
        </div>
        <div class="field-group">
          <label class="field-label">Grupo</label>
          <input type="text" class="field" value="<?= htmlspecialchars($user['grupo']) ?>" readonly>
        </div>
      </div>
      <?php endif; ?>

      <!-- Vincular ausencia (solo si hay asistencias sin justificar) -->
      <?php if ($user['rol'] === 'alumno' && !empty($asistencias)): ?>
      <div class="form-section">
        <h3 class="form-section-title">
          <span class="form-section-num">2</span>
          Ausencia a justificar
        </h3>
        <div class="field-group">
          <label class="field-label" for="id_asistencia">Selecciona la clase ausente <span class="required">*</span></label>
          <select id="id_asistencia" name="id_asistencia" class="field field-select" required>
            <option value="">— Selecciona una ausencia —</option>
            <?php foreach ($asistencias as $asi): ?>
            <option value="<?= $asi['id_asistencia'] ?>">
              <?= date('d/m/Y', strtotime($asi['fecha'])) ?> — <?= htmlspecialchars($asi['materia']) ?>
              (<?= htmlspecialchars($asi['docente']) ?>)
            </option>
            <?php endforeach; ?>
          </select>
          <small style="color:var(--color-text-muted);font-size:.75rem;font-family:var(--font-sans);">
            Solo se muestran ausencias sin justificante.
          </small>
        </div>
      </div>
      <?php elseif ($user['rol'] === 'alumno'): ?>
      <div class="form-section">
        <h3 class="form-section-title">
          <span class="form-section-num">2</span>
          Ausencia a justificar
        </h3>
        <div class="alert alert-info">
          No tienes ausencias registradas sin justificante. Si crees que hay un error, contacta a tu docente.
        </div>
        <input type="hidden" name="id_asistencia" value="">
      </div>
      <?php else: ?>
      <input type="hidden" name="id_asistencia" value="">
      <?php endif; ?>

      <!-- Detalles del justificante -->
      <div class="form-section">
        <h3 class="form-section-title">
          <span class="form-section-num"><?= $user['rol'] === 'alumno' ? '3' : '2' ?></span>
          Motivo del justificante
        </h3>
        <div class="field-group">
          <label class="field-label">Tipo de motivo <span class="required">*</span></label>
          <div class="motivo-options">
            <?php
              $motivos_info = [
                'Salud'    => ['emoji' => '🏥', 'desc' => 'Cita médica o enfermedad'],
                'Personal' => ['emoji' => '👤', 'desc' => 'Asunto personal o familiar'],
                'Comision' => ['emoji' => '📋', 'desc' => 'Representación institucional'],
              ];
            ?>
            <?php foreach (MOTIVOS as $m): ?>
            <label class="motivo-option">
              <input type="radio" name="motivo" value="<?= $m ?>" required>
              <span class="motivo-card">
                <span class="motivo-emoji"><?= $motivos_info[$m]['emoji'] ?></span>
                <span class="motivo-name"><?= MOTIVOS_LABEL[$m] ?></span>
                <span class="motivo-desc"><?= $motivos_info[$m]['desc'] ?></span>
              </span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="field-group" style="margin-top:var(--space-4)">
          <label class="field-label" for="descripcion_motivo">Descripción / Observaciones</label>
          <textarea id="descripcion_motivo" name="descripcion_motivo" class="field"
                    rows="3" placeholder="Detalla brevemente el motivo de la ausencia…"
                    style="resize:vertical;"></textarea>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary btn-lg" id="submitBtn">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Registrar Justificante
        </button>
        <a href="<?= APP_URL ?>/public/justificantes" class="btn-ghost">Cancelar</a>
      </div>
    </form>
  </div>

  <div class="form-sidebar">
    <div class="section-card help-card">
      <h3 class="help-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Información
      </h3>
      <ul class="help-list">
        <li>El justificante inicia en estado <strong>Generado</strong>.</li>
        <li>La orientadora lo aprueba o rechaza.</li>
        <li>Adjunta comprobante si el motivo es de salud.</li>
        <li>Comisión requiere autorización previa.</li>
      </ul>
    </div>

    <div class="section-card estados-card">
      <h3 class="help-title">Flujo de estados</h3>
      <div class="estado-flow" style="flex-direction:column;gap:var(--space-2);">
        <?php
          $flujo = [
            ['Generado',  'badge-generado',  '📝 Creado por el alumno/tutor'],
            ['Pendiente', 'badge-pendiente', '⏳ En revisión'],
            ['Entregado', 'badge-entregado', '📨 Comprobante entregado'],
            ['Aprobado',  'badge-aprobado',  '✅ Aprobado por orientadora'],
            ['Rechazado', 'badge-rechazado', '❌ Rechazado'],
          ];
          foreach ($flujo as [$label, $cls, $desc]):
        ?>
        <div style="display:flex;align-items:center;gap:var(--space-2);">
          <span class="badge-estado <?= $cls ?>"><?= $label ?></span>
          <span style="font-size:.75rem;color:var(--color-text-muted);font-family:var(--font-sans);"><?= $desc ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
