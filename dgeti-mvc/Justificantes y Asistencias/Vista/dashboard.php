<?php
// Vista/dashboard.php
require_once __DIR__ . '/../config/config.php';

// Las stats vienen calculadas globalmente desde DocenteControlador.php
$label_periodo = 'Todas las materias';
$total_real    = $stats_hoy['total'];

$t  = $total_real ?: 1;
$pP = round(($stats_hoy['presentes']   / $t) * 100);
$pA = round(($stats_hoy['ausentes']    / $t) * 100);
$pR = round(($stats_hoy['retardos']    / $t) * 100);
$pJ = round(($stats_hoy['justificadas']/ $t) * 100);

$dias_es  = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes',
             'Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
$meses_es = ['January'=>'enero','February'=>'febrero','March'=>'marzo','April'=>'abril',
             'May'=>'mayo','June'=>'junio','July'=>'julio','August'=>'agosto',
             'September'=>'septiembre','October'=>'octubre','November'=>'noviembre','December'=>'diciembre'];
$fecha_obj    = new DateTime();
$dia_nombre   = $dias_es[$fecha_obj->format('l')]  ?? $fecha_obj->format('l');
$mes_nombre   = $meses_es[$fecha_obj->format('F')] ?? $fecha_obj->format('F');
$fecha_bonita = $dia_nombre . ', ' . $fecha_obj->format('d') . ' de ' . $mes_nombre . ' de ' . $fecha_obj->format('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — CBTIS 199</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/estilos.css"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css"/>
  <style>
    :root {
      --primary:   #5C1A2E;
      --primary-light: #7d2540;
      --gold:      #C9922A;
      --gold-light:#f5d98a;
      --present:   #1B6B3A;
      --absent:    #8B1A1A;
      --late:      #7A4A00;
      --just:      #2E3A8C;
      --bg:        #F7F3EE;
      --surface:   #FFFFFF;
      --border:    #E8E0D5;
      --text:      #1C1412;
      --muted:     #7A6E66;
      --radius:    16px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body.panel-body {
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
    }

    /* ── CONTENIDO PRINCIPAL ── */
    .dash-wrap {
      padding: 28px 32px;
      max-width: 1200px;
    }

    /* ── HEADER ── */
    .dash-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 32px;
    }
    .dash-avatar {
      width: 52px; height: 52px;
      border-radius: 50%;
      background: var(--primary);
      color: #fff;
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 4px 12px rgba(92,26,46,0.25);
    }
    .dash-greeting {
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      font-weight: 700;
      color: var(--primary);
      line-height: 1.2;
    }
    .dash-sub {
      font-size: 13px;
      color: var(--muted);
      margin-top: 3px;
      font-weight: 400;
    }
    .dash-date {
      background: var(--primary);
      color: #fff;
      border-radius: 24px;
      padding: 10px 20px;
      font-size: 13px;
      font-weight: 500;
      white-space: nowrap;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(92,26,46,0.2);
    }

    /* ── DIVIDER LABEL ── */
    .section-label {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--muted);
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .section-label::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border);
    }

    /* ── KPI GRID ── */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 12px;
      margin-bottom: 32px;
    }
    @media(max-width:900px){ .kpi-grid { grid-template-columns: repeat(3,1fr); } }
    @media(max-width:560px){ .kpi-grid { grid-template-columns: repeat(2,1fr); } }

    .kpi-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px 16px 16px;
      position: relative;
      overflow: hidden;
      transition: transform .2s, box-shadow .2s;
    }
    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .kpi-card::after {
      content: '';
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 3px;
      border-radius: 0 0 var(--radius) var(--radius);
    }
    .kpi-card.k-total::after  { background: var(--primary); }
    .kpi-card.k-present::after{ background: var(--present); }
    .kpi-card.k-absent::after { background: var(--absent); }
    .kpi-card.k-late::after   { background: var(--late); }
    .kpi-card.k-just::after   { background: var(--just); }
    .kpi-card.k-pend::after   { background: var(--gold); }

    .kpi-icon {
      font-size: 20px;
      margin-bottom: 10px;
      display: block;
    }
    .kpi-num {
      font-family: 'Playfair Display', serif;
      font-size: 36px;
      font-weight: 700;
      line-height: 1;
      display: block;
      margin-bottom: 6px;
    }
    .kpi-card.k-total   .kpi-num { color: var(--primary); }
    .kpi-card.k-present .kpi-num { color: var(--present); }
    .kpi-card.k-absent  .kpi-num { color: var(--absent); }
    .kpi-card.k-late    .kpi-num { color: var(--late); }
    .kpi-card.k-just    .kpi-num { color: var(--just); }
    .kpi-card.k-pend    .kpi-num { color: var(--gold); }

    .kpi-label {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--muted);
      display: block;
    }
    .kpi-pct {
      font-size: 11px;
      color: var(--muted);
      display: block;
      margin-top: 2px;
    }
    .kpi-periodo {
      position: absolute;
      top: 12px; right: 12px;
      font-size: 9px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      background: var(--bg);
      color: var(--muted);
      border-radius: 6px;
      padding: 2px 6px;
    }

    /* ── BARRA ASISTENCIA ── */
    .attend-wrap {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px 24px;
      margin-bottom: 32px;
    }
    .attend-title {
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .attend-bar {
      height: 10px;
      border-radius: 8px;
      overflow: hidden;
      background: var(--border);
      display: flex;
      margin-bottom: 12px;
    }
    .seg { height: 100%; transition: width .6s ease; }
    .seg-p { background: var(--present); }
    .seg-a { background: var(--absent); }
    .seg-r { background: var(--late); }
    .seg-j { background: var(--just); }

    .attend-legend {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
    }
    .legend-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: var(--muted);
    }
    .legend-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    /* ── GRID INFERIOR ── */
    .bottom-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 32px;
    }
    @media(max-width:700px){ .bottom-grid { grid-template-columns: 1fr; } }

    /* ── ACCESO RÁPIDO ── */
    .quick-grid {
      display: grid;
      grid-template-columns: repeat(4,1fr);
      gap: 12px;
      margin-bottom: 32px;
    }
    @media(max-width:700px){ .quick-grid { grid-template-columns: repeat(2,1fr); } }

    .quick-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 22px 16px;
      text-align: center;
      text-decoration: none;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      transition: all .2s;
      cursor: pointer;
    }
    .quick-card:hover {
      border-color: var(--primary);
      background: #fdf7f9;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(92,26,46,0.1);
    }
    .quick-icon {
      width: 44px; height: 44px;
      border-radius: 12px;
      background: #f5eded;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
      transition: background .2s;
    }
    .quick-card:hover .quick-icon {
      background: var(--primary);
    }
    .quick-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* ── CARD GENÉRICA ── */
    .dash-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
    }
    .dash-card-head {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .dash-card-title {
      font-size: 13px;
      font-weight: 700;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .badge-count {
      background: var(--primary);
      color: #fff;
      border-radius: 20px;
      padding: 2px 10px;
      font-size: 11px;
      font-weight: 700;
    }
    .dash-card-body { padding: 16px 20px; }

    /* ── GRUPOS ── */
    .grupo-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid var(--border);
    }
    .grupo-row:last-child { border-bottom: none; }
    .grupo-name {
      font-size: 15px;
      font-weight: 700;
      font-family: 'Playfair Display', serif;
      color: var(--text);
    }
    .grupo-sub {
      font-size: 11px;
      color: var(--muted);
      margin-top: 2px;
    }
    .btn-asist {
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 8px 16px;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: background .2s;
      white-space: nowrap;
    }
    .btn-asist:hover { background: var(--primary-light); }

    /* ── JUSTIFICANTES PENDIENTES ── */
    .pend-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid var(--border);
    }
    .pend-row:last-child { border-bottom: none; }
    .pend-avatar {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: #f5eded;
      color: var(--primary);
      font-family: 'Playfair Display', serif;
      font-size: 15px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      font-weight: 700;
    }
    .pend-name {
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
    }
    .pend-motivo {
      font-size: 11px;
      color: var(--muted);
      margin-top: 2px;
    }
    .pend-badge {
      margin-left: auto;
      background: #FEF3CD;
      color: #7A4A00;
      border-radius: 8px;
      padding: 3px 10px;
      font-size: 11px;
      font-weight: 700;
      white-space: nowrap;
    }

    .empty-state {
      text-align: center;
      padding: 24px 0;
      color: var(--muted);
      font-size: 13px;
    }
    .empty-state-icon { font-size: 28px; margin-bottom: 8px; display: block; }

    /* ── ANIMACIONES ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .kpi-card      { animation: fadeUp .4s ease both; }
    .kpi-card:nth-child(1){ animation-delay:.05s }
    .kpi-card:nth-child(2){ animation-delay:.10s }
    .kpi-card:nth-child(3){ animation-delay:.15s }
    .kpi-card:nth-child(4){ animation-delay:.20s }
    .kpi-card:nth-child(5){ animation-delay:.25s }
    .kpi-card:nth-child(6){ animation-delay:.30s }
  </style>
</head>
<body class="panel-body">

<div class="mobile-topbar">
  <button class="mobile-topbar-btn" id="btnOpenSidebar">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
      <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
  </button>
  <span class="mobile-topbar-title">Dashboard</span>
</div>

<div class="with-sidebar">
  <?php require_once __DIR__ . '/layout/sidebar.php'; ?>

  <div class="sidebar-content">
    <div class="dash-wrap">

      <!-- HEADER -->
      <div class="dash-header">
        <div style="display:flex;align-items:center;gap:14px;">
          <div class="dash-avatar">
            <?= strtoupper(substr($docente['nombre'], 0, 1)) ?>
          </div>
          <div>
            <div class="dash-greeting">
              Bienvenido, Prof. <?= htmlspecialchars(explode(' ', $docente['nombre'])[0]) ?>
            </div>
            <div class="dash-sub">
              <?= htmlspecialchars($docente['materia_principal']) ?>
              <?php if(!empty($docente['turno'])): ?>
                · <?= htmlspecialchars($docente['turno']) ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="dash-date">
          📅 <?= $fecha_bonita ?>
        </div>
      </div>

      <!-- KPI CARDS -->
      <div class="section-label">Resumen de asistencia — <?= $label_periodo ?></div>
      <div class="kpi-grid">

        <div class="kpi-card k-total">
          <span class="kpi-periodo"><?= $label_periodo ?></span>
          <span class="kpi-icon">👥</span>
          <span class="kpi-num"><?= $total_real ?></span>
          <span class="kpi-label">Total alumnos</span>
          <span class="kpi-pct">Grupo activo</span>
        </div>

        <div class="kpi-card k-present">
          <span class="kpi-icon">✅</span>
          <span class="kpi-num"><?= $stats_hoy['presentes'] ?></span>
          <span class="kpi-label">Presentes</span>
          <span class="kpi-pct"><?= $total_real > 0 ? $pP : 0 ?>% del grupo</span>
        </div>

        <div class="kpi-card k-absent">
          <span class="kpi-icon">❌</span>
          <span class="kpi-num"><?= $stats_hoy['ausentes'] ?></span>
          <span class="kpi-label">Ausentes</span>
          <span class="kpi-pct"><?= $total_real > 0 ? $pA : 0 ?>% del grupo</span>
        </div>

        <div class="kpi-card k-late">
          <span class="kpi-icon">⏰</span>
          <span class="kpi-num"><?= $stats_hoy['retardos'] ?></span>
          <span class="kpi-label">Retardos</span>
          <span class="kpi-pct"><?= $total_real > 0 ? $pR : 0 ?>% del grupo</span>
        </div>

        <div class="kpi-card k-just">
          <span class="kpi-icon">📋</span>
          <span class="kpi-num"><?= $stats_hoy['justificadas'] ?></span>
          <span class="kpi-label">Justificadas</span>
          <span class="kpi-pct">Faltas con doc.</span>
        </div>

        <div class="kpi-card k-pend">
          <span class="kpi-icon">⏳</span>
          <span class="kpi-num"><?= count($justificantes) ?></span>
          <span class="kpi-label">Pendientes</span>
          <span class="kpi-pct">Por revisar</span>
        </div>

      </div>

      <!-- BARRA DE DISTRIBUCIÓN -->
      <?php if($total_real > 0): ?>
      <div class="attend-wrap">
        <div class="attend-title">📊 Distribución de asistencia</div>
        <div class="attend-bar">
          <?php if($pP): ?><div class="seg seg-p" style="width:<?= $pP ?>%"></div><?php endif; ?>
          <?php if($pA): ?><div class="seg seg-a" style="width:<?= $pA ?>%"></div><?php endif; ?>
          <?php if($pR): ?><div class="seg seg-r" style="width:<?= $pR ?>%"></div><?php endif; ?>
          <?php if($pJ): ?><div class="seg seg-j" style="width:<?= $pJ ?>%"></div><?php endif; ?>
        </div>
        <div class="attend-legend">
          <div class="legend-item"><div class="legend-dot" style="background:var(--present)"></div> Presentes <?= $pP ?>%</div>
          <div class="legend-item"><div class="legend-dot" style="background:var(--absent)"></div> Ausentes <?= $pA ?>%</div>
          <div class="legend-item"><div class="legend-dot" style="background:var(--late)"></div> Retardos <?= $pR ?>%</div>
          <div class="legend-item"><div class="legend-dot" style="background:var(--just)"></div> Justificadas <?= $pJ ?>%</div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ACCESO RÁPIDO -->
      <div class="section-label">Acceso rápido</div>
      <div class="quick-grid">
        <a class="quick-card" href="<?= BASE_URL ?>/Controlador/DocenteControlador.php?accion=panel">
          <div class="quick-icon">✅</div>
          <span class="quick-label">Tomar Asistencia</span>
        </a>
        <a class="quick-card" href="<?= BASE_URL ?>/Controlador/HistorialControlador.php?accion=historial">
          <div class="quick-icon">📋</div>
          <span class="quick-label">Ver Historial</span>
        </a>
        <a class="quick-card" href="<?= BASE_URL ?>/Controlador/ExportarControlador.php?accion=exportar">
          <div class="quick-icon">📥</div>
          <span class="quick-label">Exportar CSV</span>
        </a>
        <a class="quick-card" href="<?= BASE_URL ?>/Controlador/JustificanteControlador.php?accion=justificantes">
          <div class="quick-icon">📝</div>
          <span class="quick-label">Justificantes</span>
        </a>
      </div>

      <!-- GRUPOS + PENDIENTES -->
      <div class="bottom-grid">

        <!-- MIS GRUPOS -->
        <div class="dash-card">
          <div class="dash-card-head">
            <div class="dash-card-title">🏫 Mis grupos</div>
            <span class="badge-count"><?= count($grupos) ?> grupos</span>
          </div>
          <div class="dash-card-body">
            <?php if(empty($grupos)): ?>
              <div class="empty-state">
                <span class="empty-state-icon">📭</span>
                Sin grupos registrados
              </div>
            <?php else: ?>
              <?php foreach($grupos as $g): ?>
              <div class="grupo-row">
                <div>
                  <div class="grupo-name"><?= htmlspecialchars($g['nombre']) ?></div>
                  <div class="grupo-sub">Grupo activo</div>
                </div>
                <a class="btn-asist"
                   href="<?= BASE_URL ?>/Controlador/DocenteControlador.php?accion=panel&grupo=<?= $g['id'] ?>">
                  Asistencia →
                </a>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- JUSTIFICANTES PENDIENTES -->
        <div class="dash-card">
          <div class="dash-card-head">
            <div class="dash-card-title">📬 Justificantes pendientes</div>
            <?php if(!empty($justificantes)): ?>
              <span class="badge-count" style="background:#7A4A00;"><?= count($justificantes) ?></span>
            <?php endif; ?>
          </div>
          <div class="dash-card-body">
            <?php if(empty($justificantes)): ?>
              <div class="empty-state">
                <span class="empty-state-icon">✅</span>
                Sin justificantes pendientes
              </div>
            <?php else: ?>
              <?php foreach(array_slice($justificantes, 0, 5) as $j): ?>
              <div class="pend-row">
                <div class="pend-avatar"><?= strtoupper(substr($j['alumno'], 0, 1)) ?></div>
                <div>
                  <div class="pend-name"><?= htmlspecialchars($j['alumno']) ?></div>
                  <div class="pend-motivo"><?= htmlspecialchars(substr($j['motivo'] ?? 'Sin motivo', 0, 40)) ?>…</div>
                </div>
                <span class="pend-badge">⏳ Pendiente</span>
              </div>
              <?php endforeach; ?>
              <?php if(count($justificantes) > 5): ?>
                <div style="text-align:center;margin-top:12px;">
                  <a href="<?= BASE_URL ?>/Controlador/JustificanteControlador.php?accion=justificantes&estado=Pendiente"
                     style="font-size:12px;color:var(--primary);font-weight:600;text-decoration:none;">
                    Ver todos (<?= count($justificantes) ?>) →
                  </a>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div><!-- /dash-wrap -->
  </div>
</div>

<script>
(function(){
  var btn = document.getElementById('btnOpenSidebar');
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');
  if(btn && sidebar){
    btn.addEventListener('click', function(){
      sidebar.classList.toggle('open');
      if(overlay) overlay.classList.toggle('active');
    });
    if(overlay) overlay.addEventListener('click', function(){
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
  }
})();
</script>
</body>
</html>