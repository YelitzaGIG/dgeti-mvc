<?php
// Vista/detalle_justificante.php
require_once __DIR__ . '/../config/config.php';

$tipo_badge = [
  'Salud'    => ['color' => '#1a5fa8', 'bg' => '#e8f1fb', 'icon' => '🏥'],
  'Personal' => ['color' => '#633806', 'bg' => '#FAEEDA', 'icon' => '👤'],
  'Comision' => ['color' => '#4A1A6B', 'bg' => '#EEE6F1', 'icon' => '📋'],
];
$tipo_info = $tipo_badge[$justificante['tipo_motivo']] ?? ['color'=>'#555','bg'=>'#eee','icon'=>'📄'];

$estado_map = [
  'Generado'  => ['bg'=>'#f0f0f0', 'color'=>'#555',    'desc'=>'La solicitud fue creada por el alumno.'],
  'Pendiente' => ['bg'=>'#FAEEDA', 'color'=>'#633806',  'desc'=>'En espera de revisión por la orientadora.'],
  'Entregado' => ['bg'=>'#e8f1fb', 'color'=>'#1a5fa8',  'desc'=>'El comprobante fue entregado físicamente.'],
  'Aprobado'  => ['bg'=>'#EAF3DE', 'color'=>'#27500A',  'desc'=>'La orientadora aprobó el justificante.'],
  'Rechazado' => ['bg'=>'#FCEBEB', 'color'=>'#791F1F',  'desc'=>'La orientadora rechazó el justificante.'],
];
$est = $estado_map[$justificante['estado']] ?? $estado_map['Pendiente'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Detalle Justificante — CBTIS 199</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/estilos.css"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css"/>
  <style>
    .det-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 18px;
      align-items: start;
    }
    @media (max-width: 700px) { .det-grid { grid-template-columns: 1fr; } }

    .det-card {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: 14px;
      overflow: hidden;
      margin-bottom: 0;
    }
    .det-card-head {
      padding: 14px 20px;
      border-bottom: 1px solid var(--color-border);
      font-size: 13px;
      font-weight: 700;
      font-family: Arial, sans-serif;
      color: var(--color-primary);
    }
    .det-card-body { padding: 18px 20px; }

    .det-field { margin-bottom: 16px; }
    .det-field:last-child { margin-bottom: 0; }
    .det-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--color-muted);
      font-family: Arial, sans-serif;
      margin-bottom: 4px;
    }
    .det-value {
      font-size: 14px;
      font-weight: 600;
      font-family: Arial, sans-serif;
      color: var(--color-text);
    }
    .det-value.large { font-size: 18px; }
    .det-text {
      font-size: 13px;
      font-family: Arial, sans-serif;
      color: var(--color-text);
      line-height: 1.6;
      background: var(--color-bg);
      border-radius: 8px;
      padding: 12px 14px;
      border: 1px solid var(--color-border);
    }

    /* ESTADO VISUAL */
    .estado-box {
      border-radius: 12px;
      padding: 16px;
      text-align: center;
      margin-bottom: 14px;
    }
    .estado-box-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-family: Arial, sans-serif;
      margin-bottom: 6px;
      opacity: 0.8;
    }
    .estado-box-value {
      font-size: 20px;
      font-weight: 700;
      font-family: Georgia, serif;
    }
    .estado-box-desc {
      font-size: 11px;
      margin-top: 6px;
      font-family: Arial, sans-serif;
      opacity: 0.85;
      line-height: 1.4;
    }

    /* TIMELINE ESTADO */
    .timeline { padding: 4px 0; }
    .tl-step {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 14px;
    }
    .tl-step:last-child { margin-bottom: 0; }
    .tl-dot {
      width: 14px; height: 14px;
      border-radius: 50%;
      flex-shrink: 0;
      margin-top: 2px;
      border: 2px solid var(--color-border);
      background: var(--color-surface);
    }
    .tl-dot.done { background: var(--color-primary); border-color: var(--color-primary); }
    .tl-dot.current { background: #e67e22; border-color: #e67e22; }
    .tl-info { flex: 1; }
    .tl-name {
      font-size: 12px;
      font-weight: 700;
      font-family: Arial, sans-serif;
      color: var(--color-text);
    }
    .tl-name.muted { color: var(--color-muted); font-weight: 400; }
    .tl-date { font-size: 11px; color: var(--color-muted); font-family: Arial, sans-serif; }

    /* COMPROBANTES */
    .comp-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      border: 1px solid var(--color-border);
      border-radius: 10px;
      margin-bottom: 8px;
      font-family: Arial, sans-serif;
    }
    .comp-icon { font-size: 20px; }
    .comp-name { font-size: 13px; font-weight: 600; color: var(--color-text); }
    .comp-meta { font-size: 11px; color: var(--color-muted); }
    .comp-btn {
      margin-left: auto;
      font-size: 11px;
      padding: 4px 12px;
      border-radius: 6px;
      border: 1px solid var(--color-primary);
      background: transparent;
      color: var(--color-primary);
      text-decoration: none;
      font-weight: 600;
      cursor: pointer;
    }
    .comp-btn:hover { background: var(--color-primary); color: var(--color-beige); }

    .readonly-notice {
      background: #e8f1fb;
      color: #1a5fa8;
      border-radius: 10px;
      padding: 10px 16px;
      font-size: 12px;
      font-family: Arial, sans-serif;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
  </style>
</head>
<body class="panel-body">

<div class="mobile-topbar">
  <button class="mobile-topbar-btn" id="btnOpenSidebar">
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>
  <span class="mobile-topbar-title">Detalle Justificante</span>
</div>

<div class="with-sidebar">
  <?php require_once __DIR__ . '/layout/sidebar.php'; ?>

  <div class="sidebar-content">
    <div class="container">

      <!-- HEADER -->
      <div class="header">
        <div class="header-left">
          <div class="avatar"><?= strtoupper(substr($justificante['alumno'], 0, 1)) ?></div>
          <div>
            <div class="header-name"><?= htmlspecialchars($justificante['alumno']) ?></div>
            <div class="header-sub">
              <?= htmlspecialchars($justificante['grupo'] ?? 'Sin grupo') ?>
              <?php if (!empty($justificante['materia'])): ?>
                · <?= htmlspecialchars($justificante['materia']) ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <a href="<?= BASE_URL ?>/Controlador/JustificanteControlador.php?accion=justificantes">
          <button type="button">← Volver</button>
        </a>
      </div>

      <!-- AVISO -->
      <div class="readonly-notice">
        ℹ️ Vista de <strong>solo consulta</strong>. La resolución del justificante corresponde a la orientadora.
      </div>

      <!-- GRID PRINCIPAL -->
      <div class="det-grid">

        <!-- COLUMNA IZQUIERDA: Datos del justificante -->
        <div style="display:flex; flex-direction:column; gap:18px;">

          <!-- Info del alumno -->
          <div class="det-card">
            <div class="det-card-head">👤 Datos del alumno</div>
            <div class="det-card-body">
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="det-field">
                  <div class="det-label">Nombre completo</div>
                  <div class="det-value large"><?= htmlspecialchars($justificante['alumno']) ?></div>
                </div>
                <div class="det-field">
                  <div class="det-label">Matrícula</div>
                  <div class="det-value large"><?= htmlspecialchars($justificante['matricula'] ?? '—') ?></div>
                </div>
                <div class="det-field">
                  <div class="det-label">Grupo</div>
                  <div class="det-value"><?= htmlspecialchars($justificante['grupo'] ?? '—') ?></div>
                </div>
                <div class="det-field">
                  <div class="det-label">Materia afectada</div>
                  <div class="det-value"><?= htmlspecialchars($justificante['materia'] ?? '—') ?></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Motivo y detalles -->
          <div class="det-card">
            <div class="det-card-head">📋 Detalles de la solicitud</div>
            <div class="det-card-body">

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div class="det-field">
                  <div class="det-label">Fecha de falta</div>
                  <div class="det-value">
                    <?= !empty($justificante['fecha_falta'])
                      ? date('d/m/Y', strtotime($justificante['fecha_falta']))
                      : '—' ?>
                  </div>
                </div>
                <div class="det-field">
                  <div class="det-label">Fecha de solicitud</div>
                  <div class="det-value"><?= date('d/m/Y', strtotime($justificante['fecha_solicitud'])) ?></div>
                </div>
              </div>

              <div class="det-field">
                <div class="det-label">Tipo de motivo</div>
                <div style="margin-top:4px;">
                  <span style="background:<?= $tipo_info['bg'] ?>;color:<?= $tipo_info['color'] ?>;
                               font-size:12px;font-weight:700;font-family:Arial,sans-serif;
                               padding:4px 12px;border-radius:20px;">
                    <?= $tipo_info['icon'] ?> <?= htmlspecialchars($justificante['tipo_motivo']) ?>
                  </span>
                </div>
              </div>

              <div class="det-field">
                <div class="det-label">Descripción del motivo</div>
                <div class="det-text">
                  <?= !empty($justificante['descripcion_motivo'])
                    ? nl2br(htmlspecialchars($justificante['descripcion_motivo']))
                    : '<span style="color:var(--color-muted);font-style:italic;">Sin descripción.</span>' ?>
                </div>
              </div>

              <?php if (!empty($justificante['observaciones'])): ?>
              <div class="det-field">
                <div class="det-label">Observaciones de la orientadora</div>
                <div class="det-text">
                  <?= nl2br(htmlspecialchars($justificante['observaciones'])) ?>
                </div>
              </div>
              <?php endif; ?>

            </div>
          </div>

          <!-- Comprobantes adjuntos -->
          <div class="det-card">
            <div class="det-card-head">📎 Comprobantes adjuntos</div>
            <div class="det-card-body">
              <?php if (empty($justificante['comprobantes'])): ?>
                <div style="color:var(--color-muted);font-size:13px;font-family:Arial,sans-serif;text-align:center;padding:12px 0;">
                  Sin comprobantes adjuntos aún.
                </div>
              <?php else: ?>
                <?php foreach ($justificante['comprobantes'] as $c): ?>
                <div class="comp-item">
                  <span class="comp-icon"><?= strtolower($c['tipo_archivo']) === 'pdf' ? '📄' : '🖼️' ?></span>
                  <div>
                    <div class="comp-name"><?= strtoupper($c['tipo_archivo']) ?></div>
                    <div class="comp-meta">Subido: <?= date('d/m/Y H:i', strtotime($c['fecha_subida'])) ?></div>
                  </div>
                  <a href="<?= BASE_URL ?>/<?= htmlspecialchars($c['archivo_url']) ?>"
                     target="_blank" class="comp-btn">Ver archivo</a>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

        </div><!-- /col izquierda -->

        <!-- COLUMNA DERECHA: Estado y timeline -->
        <div style="display:flex; flex-direction:column; gap:18px;">

          <!-- Estado actual -->
          <div class="det-card">
            <div class="det-card-head">📊 Estado actual</div>
            <div class="det-card-body">
              <div class="estado-box"
                   style="background:<?= $est['bg'] ?>;color:<?= $est['color'] ?>;">
                <div class="estado-box-label">Estado</div>
                <div class="estado-box-value"><?= $justificante['estado'] ?></div>
                <div class="estado-box-desc"><?= $est['desc'] ?></div>
              </div>

              <?php if (!empty($justificante['fecha_resolucion'])): ?>
              <div class="det-field">
                <div class="det-label">Fecha de resolución</div>
                <div class="det-value">
                  <?= date('d/m/Y H:i', strtotime($justificante['fecha_resolucion'])) ?>
                </div>
              </div>
              <?php endif; ?>

              <?php if (!empty($justificante['estatus_asistencia'])): ?>
              <div class="det-field" style="margin-top:12px;">
                <div class="det-label">Asistencia registrada como</div>
                <?php
                  $asis = $justificante['estatus_asistencia'];
                  $asis_cls = ['presente'=>'badge-green','ausente'=>'badge-red','retardo'=>'badge-amber','justificada'=>'badge-purple'][$asis] ?? 'badge-pending';
                ?>
                <span class="badge <?= $asis_cls ?>" style="font-size:12px;padding:4px 12px;">
                  <?= ucfirst($asis) ?>
                </span>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Timeline del proceso -->
          <div class="det-card">
            <div class="det-card-head">🔄 Proceso del justificante</div>
            <div class="det-card-body">
              <div class="timeline">
                <?php
                  $pasos = ['Generado','Pendiente','Entregado','Aprobado'];
                  $estado_actual = $justificante['estado'];
                  $orden = array_flip($pasos);
                  $idx_actual = $orden[$estado_actual] ?? ($estado_actual === 'Rechazado' ? 99 : -1);

                  $iconos = ['Generado'=>'📝','Pendiente'=>'⏳','Entregado'=>'📬','Aprobado'=>'✅'];
                  foreach ($pasos as $i => $paso):
                    if ($estado_actual === 'Rechazado' && $paso === 'Aprobado') continue;
                    $done    = $i < $idx_actual;
                    $current = $paso === $estado_actual;
                ?>
                <div class="tl-step">
                  <div class="tl-dot <?= $done ? 'done' : ($current ? 'current' : '') ?>"></div>
                  <div class="tl-info">
                    <div class="tl-name <?= (!$done && !$current) ? 'muted' : '' ?>">
                      <?= $iconos[$paso] ?> <?= $paso ?>
                    </div>
                    <?php if ($current && !empty($justificante['fecha_solicitud'])): ?>
                      <div class="tl-date"><?= date('d/m/Y', strtotime($justificante['fecha_solicitud'])) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endforeach; ?>

                <?php if ($estado_actual === 'Rechazado'): ?>
                <div class="tl-step">
                  <div class="tl-dot current" style="background:#791F1F;border-color:#791F1F;"></div>
                  <div class="tl-info">
                    <div class="tl-name">❌ Rechazado</div>
                    <?php if (!empty($justificante['fecha_resolucion'])): ?>
                      <div class="tl-date"><?= date('d/m/Y', strtotime($justificante['fecha_resolucion'])) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div><!-- /col derecha -->

      </div><!-- /det-grid -->

    </div>
  </div>
</div>

<script>
(function(){
  var btn = document.getElementById('btnOpenSidebar');
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');
  if(btn && sidebar){
    btn.addEventListener('click', function(){ sidebar.classList.toggle('open'); if(overlay) overlay.classList.toggle('active'); });
    if(overlay) overlay.addEventListener('click', function(){ sidebar.classList.remove('open'); overlay.classList.remove('active'); });
  }
})();
</script>
</body>
</html>