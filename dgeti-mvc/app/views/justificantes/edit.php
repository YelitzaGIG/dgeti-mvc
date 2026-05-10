<?php /* app/views/justificantes/edit.php */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Editar Justificante</h1>
    <p class="page-subtitle">ID: <strong>#<?= (int) $j['id'] ?></strong></p>
  </div>
  <a href="<?= APP_URL ?>/public/justificantes/show/<?= (int) $j['id'] ?>" class="btn-outline">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Cancelar
  </a>
</div>

<div class="form-layout">
  <div class="section-card form-card">
    <form method="POST" action="<?= APP_URL ?>/public/justificantes/update/<?= (int) $j['id'] ?>" class="create-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

      <!-- Datos del alumno (solo lectura) -->
      <div class="form-section">
        <h3 class="form-section-title">
          <span class="form-section-num">1</span>
          Datos del alumno
        </h3>
        <div class="field-row">
          <div class="field-group">
            <label class="field-label">Nombre</label>
            <input type="text" class="field" value="<?= htmlspecialchars($j['nombre_alumno']) ?>" readonly>
          </div>
          <div class="field-group">
            <label class="field-label">Matrícula</label>
            <input type="text" class="field" value="<?= htmlspecialchars($j['numero_control']) ?>" readonly>
          </div>
        </div>
        <div class="field-group">
          <label class="field-label">Grupo</label>
          <input type="text" class="field" value="<?= htmlspecialchars($j['grupo']) ?>" readonly>
        </div>
        <?php if (!empty($j['fecha_ausencia'])): ?>
        <div class="field-row" style="margin-top:var(--space-3)">
          <div class="field-group">
            <label class="field-label">Fecha ausencia</label>
            <input type="text" class="field" value="<?= date('d/m/Y', strtotime($j['fecha_ausencia'])) ?>" readonly>
          </div>
          <div class="field-group">
            <label class="field-label">Materia</label>
            <input type="text" class="field" value="<?= htmlspecialchars($j['materia'] ?? 'N/A') ?>" readonly>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Motivo y estado -->
      <div class="form-section">
        <h3 class="form-section-title">
          <span class="form-section-num">2</span>
          Motivo y estado
        </h3>
        <div class="field-row">
          <div class="field-group">
            <label class="field-label">Tipo de motivo <span class="required">*</span></label>
            <select name="motivo" class="field field-select" required>
              <?php foreach (MOTIVOS as $m): ?>
              <option value="<?= $m ?>" <?= $j['motivo'] === $m ? 'selected' : '' ?>><?= MOTIVOS_LABEL[$m] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Estado <span class="required">*</span></label>
            <select name="estado" class="field field-select" required>
              <?php foreach (ESTADOS as $e): ?>
              <option value="<?= $e ?>" <?= $j['estado'] === $e ? 'selected' : '' ?>><?= $e ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="field-group" style="margin-top:var(--space-3)">
          <label class="field-label">Descripción del motivo</label>
          <textarea name="descripcion_motivo" class="field" rows="3" style="resize:vertical;"
                    placeholder="Detalle del motivo…"><?= htmlspecialchars($j['descripcion_motivo'] ?? '') ?></textarea>
        </div>
        <div class="field-group" style="margin-top:var(--space-3)">
          <label class="field-label">Observaciones (orientadora)</label>
          <textarea name="observaciones" class="field" rows="2" style="resize:vertical;"
                    placeholder="Comentarios de resolución…"><?= htmlspecialchars($j['observaciones'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Guardar cambios
        </button>
        <a href="<?= APP_URL ?>/public/justificantes" class="btn-ghost">Cancelar</a>
      </div>
    </form>
  </div>

  <div class="form-sidebar">
    <div class="section-card">
      <h3 class="section-title">Estado actual</h3>
      <div class="current-data">
        <div class="detail-item">
          <span class="detail-key">ID</span>
          <span class="folio-tag">#<?= (int) $j['id'] ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-key">Estado</span>
          <span class="badge-estado badge-<?= strtolower($j['estado']) ?>"><?= $j['estado'] ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-key">Solicitud</span>
          <span><?= date('d/m/Y H:i', strtotime($j['created_at'])) ?></span>
        </div>
        <?php if (!empty($j['fecha_resolucion'])): ?>
        <div class="detail-item">
          <span class="detail-key">Resolución</span>
          <span><?= date('d/m/Y H:i', strtotime($j['fecha_resolucion'])) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($j['nombre_orientadora'])): ?>
        <div class="detail-item">
          <span class="detail-key">Orientadora</span>
          <span><?= htmlspecialchars($j['nombre_orientadora']) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
