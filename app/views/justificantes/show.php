<?php /* app/views/justificantes/show.php */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Detalle del Justificante</h1>
    <p class="page-subtitle">ID: <strong>#<?= (int) $j['id'] ?></strong></p>
  </div>
  <div class="header-actions">
    <?php if (in_array($user['rol'], ['docente', 'orientadora', 'jefa_servicios', 'tutor_institucional'])): ?>
    <a href="<?= APP_URL ?>/public/justificantes/edit/<?= (int) $j['id'] ?>" class="btn-primary">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Editar
    </a>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/public/justificantes" class="btn-outline">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Regresar
    </a>
  </div>
</div>

<div class="two-col-layout">
  <!-- Columna izquierda: detalles -->
  <div class="section-card">
    <div class="detail-header">
      <span class="folio-tag folio-lg">#<?= (int) $j['id'] ?></span>
      <span class="badge-estado badge-<?= strtolower($j['estado']) ?> badge-lg"><?= htmlspecialchars($j['estado']) ?></span>
    </div>

    <div class="detail-grid">
      <div class="detail-item">
        <span class="detail-key">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Alumno
        </span>
        <span class="detail-val"><?= htmlspecialchars($j['nombre_alumno']) ?></span>
      </div>

      <div class="detail-item">
        <span class="detail-key">Matrícula</span>
        <span class="detail-val"><code><?= htmlspecialchars($j['numero_control']) ?></code></span>
      </div>

      <div class="detail-item">
        <span class="detail-key">Grupo</span>
        <span class="detail-val"><span class="grupo-tag"><?= htmlspecialchars($j['grupo']) ?></span></span>
      </div>

      <div class="detail-item">
        <span class="detail-key">Tipo de motivo</span>
        <span class="detail-val">
          <?php
            $iconos = ['Salud' => '🏥', 'Comision' => '📋', 'Personal' => '👤'];
            $label  = MOTIVOS_LABEL[$j['motivo']] ?? $j['motivo'];
          ?>
          <span class="motivo-tag motivo-<?= strtolower($j['motivo']) ?>">
            <?= ($iconos[$j['motivo']] ?? '') . ' ' . htmlspecialchars($label) ?>
          </span>
        </span>
      </div>

      <?php if (!empty($j['descripcion_motivo'])): ?>
      <div class="detail-item">
        <span class="detail-key">Descripción</span>
        <span class="detail-val" style="white-space:pre-line;"><?= htmlspecialchars($j['descripcion_motivo']) ?></span>
      </div>
      <?php endif; ?>

      <?php if (!empty($j['fecha_ausencia'])): ?>
      <div class="detail-item">
        <span class="detail-key">Fecha de ausencia</span>
        <span class="detail-val"><?= date('d \d\e F \d\e Y', strtotime($j['fecha_ausencia'])) ?></span>
      </div>
      <?php endif; ?>

      <?php if (!empty($j['materia'])): ?>
      <div class="detail-item">
        <span class="detail-key">Materia</span>
        <span class="detail-val"><?= htmlspecialchars($j['materia']) ?></span>
      </div>
      <?php endif; ?>

      <div class="detail-item">
        <span class="detail-key">Fecha de solicitud</span>
        <span class="detail-val"><?= date('d/m/Y H:i', strtotime($j['created_at'])) ?></span>
      </div>

      <?php if (!empty($j['fecha_resolucion'])): ?>
      <div class="detail-item">
        <span class="detail-key">Fecha de resolución</span>
        <span class="detail-val"><?= date('d/m/Y H:i', strtotime($j['fecha_resolucion'])) ?></span>
      </div>
      <?php endif; ?>

      <?php if (!empty($j['nombre_orientadora'])): ?>
      <div class="detail-item">
        <span class="detail-key">Orientadora</span>
        <span class="detail-val"><?= htmlspecialchars($j['nombre_orientadora']) ?></span>
      </div>
      <?php endif; ?>

      <?php if (!empty($j['observaciones'])): ?>
      <div class="detail-item">
        <span class="detail-key">Observaciones</span>
        <span class="detail-val" style="white-space:pre-line;color:var(--color-info);">
          <?= htmlspecialchars($j['observaciones']) ?>
        </span>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Columna derecha: timeline + acciones -->
  <div>
    <div class="section-card">
      <h3 class="section-title">Estado del justificante</h3>
      <div class="estado-timeline">
        <?php
          $estadoIdx = array_search($j['estado'], ESTADOS);
          foreach (ESTADOS as $idx => $e):
            $done    = $idx < $estadoIdx;
            $current = $idx === $estadoIdx;
        ?>
        <div class="timeline-step <?= $done ? 'done' : ($current ? 'current' : 'pending') ?>">
          <div class="timeline-dot">
            <?php if ($done): ?>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            <?php elseif ($current): ?>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="6" fill="currentColor"/></svg>
            <?php else: ?>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/></svg>
            <?php endif; ?>
          </div>
          <div class="timeline-info">
            <span class="timeline-label"><?= $e ?></span>
            <?php if ($current): ?><span class="timeline-current-tag">Estado actual</span><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (in_array($user['rol'], ['jefa_servicios'])): ?>
    <div class="section-card">
      <h3 class="section-title">Acciones administrativas</h3>
      <form method="POST" action="<?= APP_URL ?>/public/justificantes/delete/<?= (int) $j['id'] ?>" onsubmit="return confirmDelete(this)">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <button type="submit" class="btn-danger btn-full">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
          Eliminar justificante
        </button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</div>
