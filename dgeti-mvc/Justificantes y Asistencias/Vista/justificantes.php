<?php
// Vista/justificantes.php
require_once __DIR__ . '/../config/config.php';

$tipo_badge = [
  'Salud'    => ['color' => '#1a5fa8', 'bg' => '#e8f1fb', 'icon' => '🏥'],
  'Personal' => ['color' => '#633806', 'bg' => '#FAEEDA', 'icon' => '👤'],
  'Comision' => ['color' => '#4A1A6B', 'bg' => '#EEE6F1', 'icon' => '📋'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Justificantes — CBTIS 199</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/estilos.css"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css"/>
  <style>
    /* ── JUSTIFICANTES STYLES ── */
    .just-page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 24px;
    }
    .just-page-title {
      font-size: 20px;
      font-weight: 700;
      color: var(--color-primary);
      font-family: Georgia, serif;
    }
    .just-page-sub {
      font-size: 13px;
      color: var(--color-muted);
      font-family: Arial, sans-serif;
      margin-top: 2px;
    }

    /* FILTROS */
    .filtros-wrap {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: 14px;
      padding: 16px 20px;
      margin-bottom: 20px;
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      align-items: flex-end;
    }
    .filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .filtro-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--color-muted);
      font-family: Arial, sans-serif;
    }
    .filtro-pills { display: flex; gap: 6px; flex-wrap: wrap; }
    .pill {
      padding: 5px 14px;
      border-radius: 20px;
      border: 1px solid var(--color-border);
      background: var(--color-surface);
      font-size: 12px;
      font-family: Arial, sans-serif;
      font-weight: 600;
      color: var(--color-muted);
      text-decoration: none;
      cursor: pointer;
      transition: all 0.15s;
    }
    .pill:hover { border-color: var(--color-primary); color: var(--color-primary); }
    .pill.active { background: var(--color-primary); color: var(--color-beige); border-color: var(--color-primary); }
    .pill.pill-pend.active  { background: #633806; border-color: #633806; color: #fff; }
    .pill.pill-apro.active  { background: #27500A; border-color: #27500A; color: #fff; }
    .pill.pill-rech.active  { background: #791F1F; border-color: #791F1F; color: #fff; }

    .filtro-select {
      padding: 6px 12px;
      border: 1px solid var(--color-border);
      border-radius: 8px;
      font-size: 13px;
      font-family: Arial, sans-serif;
      color: var(--color-text);
      background: var(--color-surface);
      cursor: pointer;
    }

    /* RESUMEN KPIs pequeños */
    .just-kpi-row {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .just-kpi {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: 10px;
      padding: 10px 18px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-family: Arial, sans-serif;
    }
    .just-kpi-num {
      font-size: 22px;
      font-weight: 700;
      font-family: Georgia, serif;
    }
    .just-kpi-lbl { font-size: 11px; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.04em; }

    /* CARDS DE JUSTIFICANTE */
    .jcard {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: 14px;
      padding: 18px 20px;
      margin-bottom: 12px;
      display: flex;
      gap: 16px;
      align-items: flex-start;
      transition: box-shadow 0.2s;
    }
    .jcard:hover { box-shadow: 0 4px 16px rgba(98,17,50,0.08); }
    .jcard-left { flex: 1; min-width: 0; }
    .jcard-right { flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }

    .jcard-alumno {
      font-size: 15px;
      font-weight: 700;
      font-family: Arial, sans-serif;
      color: var(--color-text);
    }
    .jcard-meta {
      font-size: 12px;
      color: var(--color-muted);
      font-family: Arial, sans-serif;
      margin-top: 3px;
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .jcard-meta span { display: flex; align-items: center; gap: 4px; }
    .jcard-motivo {
      font-size: 13px;
      color: var(--color-text);
      font-family: Arial, sans-serif;
      margin-top: 8px;
      line-height: 1.5;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .jcard-badges { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }

    .tipo-badge {
      font-size: 11px;
      font-weight: 700;
      font-family: Arial, sans-serif;
      padding: 3px 10px;
      border-radius: 20px;
    }
    .estado-badge {
      font-size: 11px;
      font-weight: 700;
      font-family: Arial, sans-serif;
      padding: 3px 10px;
      border-radius: 20px;
    }
    .estado-Pendiente  { background: #FAEEDA; color: #633806; }
    .estado-Aprobado   { background: #EAF3DE; color: #27500A; }
    .estado-Rechazado  { background: #FCEBEB; color: #791F1F; }
    .estado-Entregado  { background: #e8f1fb; color: #1a5fa8; }
    .estado-Generado   { background: #f0f0f0; color: #555; }

    .btn-detalle {
      font-size: 12px;
      padding: 6px 16px;
      border-radius: 8px;
      border: 1.5px solid var(--color-primary);
      background: transparent;
      color: var(--color-primary);
      font-family: Arial, sans-serif;
      font-weight: 700;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.15s;
      white-space: nowrap;
    }
    .btn-detalle:hover { background: var(--color-primary); color: var(--color-beige); }

    .empty-state {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: 14px;
      padding: 48px 24px;
      text-align: center;
    }
    .empty-icon { font-size: 40px; margin-bottom: 12px; }
    .empty-txt { font-size: 14px; color: var(--color-muted); font-family: Arial, sans-serif; }

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
  <span class="mobile-topbar-title">Justificantes</span>
</div>

<div class="with-sidebar">
  <?php require_once __DIR__ . '/layout/sidebar.php'; ?>

  <div class="sidebar-content">
    <div class="container">

      <!-- HEADER -->
      <div class="just-page-header">
        <div>
          <div class="just-page-title">📝 Justificantes de mis alumnos</div>
          <div class="just-page-sub">Visualización de solicitudes — solo lectura</div>
        </div>
        <span class="badge badge-primary">Docente</span>
      </div>

      <!-- AVISO SOLO LECTURA -->
      <div class="readonly-notice">
        ℹ️ Como docente puedes <strong>consultar</strong> el estado de los justificantes. La aprobación corresponde a la orientadora.
      </div>

      <!-- KPIs RÁPIDOS -->
      <?php
        $tot  = count($justificantes);
        $pend = count(array_filter($justificantes, fn($j) => $j['estado'] === 'Pendiente'));
        $apro = count(array_filter($justificantes, fn($j) => $j['estado'] === 'Aprobado'));
        $rech = count(array_filter($justificantes, fn($j) => $j['estado'] === 'Rechazado'));
      ?>
      <div class="just-kpi-row">
        <div class="just-kpi">
          <span class="just-kpi-num"><?= $tot ?></span>
          <span class="just-kpi-lbl">Total</span>
        </div>
        <div class="just-kpi">
          <span class="just-kpi-num" style="color:#633806"><?= $pend ?></span>
          <span class="just-kpi-lbl">Pendientes</span>
        </div>
        <div class="just-kpi">
          <span class="just-kpi-num" style="color:#27500A"><?= $apro ?></span>
          <span class="just-kpi-lbl">Aprobados</span>
        </div>
        <div class="just-kpi">
          <span class="just-kpi-num" style="color:#791F1F"><?= $rech ?></span>
          <span class="just-kpi-lbl">Rechazados</span>
        </div>
      </div>

      <!-- FILTROS -->
      <div class="filtros-wrap">

        <!-- Filtro estado -->
        <div class="filtro-group">
          <span class="filtro-label">Estado</span>
          <div class="filtro-pills">
            <?php
              $estados = [
                'todos'     => 'Todos',
                'Pendiente' => 'Pendiente',
                'Entregado' => 'Entregado',
                'Aprobado'  => 'Aprobado',
                'Rechazado' => 'Rechazado',
              ];
              $clases_pill = [
                'Pendiente' => 'pill-pend',
                'Aprobado'  => 'pill-apro',
                'Rechazado' => 'pill-rech',
              ];
              foreach ($estados as $val => $lbl):
                $cls = $clases_pill[$val] ?? '';
                $activo = ($filtro_estado === $val) ? 'active' : '';
            ?>
              <a class="pill <?= $cls ?> <?= $activo ?>"
                 href="?accion=justificantes&estado=<?= $val ?>&materia=<?= $filtro_materia ?>">
                <?= $lbl ?>
                <?php if ($val === 'Pendiente' && $pend > 0): ?>
                  <span style="background:rgba(0,0,0,0.15);border-radius:10px;padding:1px 6px;margin-left:4px;font-size:10px;"><?= $pend ?></span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Filtro materia -->
        <div class="filtro-group">
          <span class="filtro-label">Materia</span>
          <select class="filtro-select" onchange="window.location='?accion=justificantes&estado=<?= $filtro_estado ?>&materia='+this.value">
            <option value="0" <?= $filtro_materia === 0 ? 'selected' : '' ?>>— Todas mis materias —</option>
            <?php foreach ($mis_materias as $m): ?>
              <option value="<?= $m['id'] ?>" <?= $filtro_materia === $m['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

      </div>

      <!-- LISTA -->
      <?php if (empty($justificantes)): ?>
        <div class="empty-state">
          <div class="empty-icon">📭</div>
          <div class="empty-txt">
            <?= $filtro_estado === 'todos'
              ? 'No hay justificantes registrados para tus alumnos.'
              : 'No hay justificantes con estado <strong>' . htmlspecialchars($filtro_estado) . '</strong>.' ?>
          </div>
        </div>

      <?php else: ?>
        <?php foreach ($justificantes as $j):
          $tipo_info = $tipo_badge[$j['tipo_motivo']] ?? ['color'=>'#555','bg'=>'#eee','icon'=>'📄'];
        ?>
        <div class="jcard">
          <div class="jcard-left">

            <div class="jcard-alumno">
              <?= htmlspecialchars($j['alumno']) ?>
            </div>

            <div class="jcard-meta">
              <?php if (!empty($j['grupo'])): ?>
                <span>🏫 <?= htmlspecialchars($j['grupo']) ?></span>
              <?php endif; ?>
              <?php if (!empty($j['materia'])): ?>
                <span>📚 <?= htmlspecialchars($j['materia']) ?></span>
              <?php endif; ?>
              <?php if (!empty($j['fecha_falta'])): ?>
                <span>📅 Falta: <?= date('d/m/Y', strtotime($j['fecha_falta'])) ?></span>
              <?php endif; ?>
              <span>🕐 Solicitud: <?= date('d/m/Y', strtotime($j['fecha'])) ?></span>
            </div>

            <?php if (!empty($j['motivo'])): ?>
              <div class="jcard-motivo"><?= htmlspecialchars($j['motivo']) ?></div>
            <?php endif; ?>

            <div class="jcard-badges">
              <span class="tipo-badge"
                    style="background:<?= $tipo_info['bg'] ?>;color:<?= $tipo_info['color'] ?>">
                <?= $tipo_info['icon'] ?> <?= htmlspecialchars($j['tipo_motivo']) ?>
              </span>
              <span class="estado-badge estado-<?= $j['estado'] ?>">
                <?= $j['estado'] ?>
              </span>
              <?php if (!empty($j['fecha_resolucion'])): ?>
                <span style="font-size:11px;color:var(--color-muted);font-family:Arial,sans-serif;align-self:center;">
                  Resuelto: <?= date('d/m/Y', strtotime($j['fecha_resolucion'])) ?>
                </span>
              <?php endif; ?>
            </div>

          </div>

          <div class="jcard-right">
            <a class="btn-detalle"
               href="<?= BASE_URL ?>/Controlador/JustificanteControlador.php?accion=ver&id=<?= $j['id'] ?>">
              Ver detalle →
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

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