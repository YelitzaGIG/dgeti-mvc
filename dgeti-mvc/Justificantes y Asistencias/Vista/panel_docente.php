<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel Docente — CBTIS 199</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/estilos.css"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
 
<body class="panel-body">
 
<!-- BARRA SUPERIOR MÓVIL -->
<div class="mobile-topbar">
  <button class="mobile-topbar-btn" id="btnOpenSidebar">
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>
  <span class="mobile-topbar-title">Panel Docente</span>
</div>
 
<!-- TOAST -->
<div id="notif" class="notif-toast" style="display:none;"></div>
 
<!-- ════ MODALES ════ -->
 
<!-- MODAL CSV -->
<div class="modal-overlay" id="modalCSV">
  <div class="modal-box">
    <div class="modal-header">
      <div>
        <div class="modal-header-title">Subir lista de alumnos</div>
        <div class="modal-header-sub">
          Nombre del archivo: <strong>MATERIA_GRUPO.csv</strong><br>
          Ej: <code>Matematicas_1A.csv</code>
        </div>
      </div>
      <button class="modal-close" id="btnCerrarModalCSV">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-section-title">Cargar archivo CSV</div>
      <form action="<?= BASE_URL ?>/Controlador/DocenteControlador.php" method="POST"
            enctype="multipart/form-data">
        <input type="hidden" name="accion"      value="subir_csv">
        <input type="hidden" name="id_materia"  value="<?= $materia_sel ?>">
        <input type="hidden" name="id_grupo"    value="<?= $grupo_sel ?>">
        <input type="hidden" name="materia_ret" value="<?= $materia_sel ?>">
        <input type="hidden" name="grupo_ret"   value="<?= $grupo_sel ?>">
        <input type="hidden" name="fecha_ret"   value="<?= $fecha_sel ?>">
 
        <div class="modal-selectors">
          <div class="sel-card">
            <div class="sel-label">Materia</div>
            <div class="modal-val">
              <?php foreach($materias as $m): if($m['id']==$materia_sel) { echo htmlspecialchars($m['nombre']); break; } endforeach; ?>
            </div>
          </div>
          <div class="sel-card">
            <div class="sel-label">Grupo</div>
            <div class="modal-val">
              <?php foreach($grupos as $g): if($g['id']==$grupo_sel) { echo htmlspecialchars($g['nombre']); break; } endforeach; ?>
            </div>
          </div>
        </div>
 
        <div class="modal-file-card">
          <div class="modal-file-label">Archivo CSV</div>
          <input type="file" name="archivo_csv" id="modal_archivo" accept=".csv" required>
          <small style="color:var(--color-muted);display:block;margin-top:8px;">
            Columnas: <strong>matricula</strong>, <strong>nombre</strong>
          </small>
        </div>
 
        <div id="preview_nombre" style="display:none;margin-top:10px;padding:10px 14px;background:#f9f6f2;border-radius:8px;font-size:13px;">
          <strong>Detectado:</strong>
          <span id="txt_materia_detectada"></span> — Grupo: <span id="txt_grupo_detectado"></span>
        </div>
 
        <div style="padding:14px 0 0;display:flex;justify-content:flex-end;gap:10px;">
          <button type="button" id="btnCancelarCSV">Cancelar</button>
          <button type="submit" class="primary">Subir y procesar</button>
        </div>
      </form>
    </div>
  </div>
</div>
 
<!-- MODAL AGREGAR ALUMNO -->
<div class="modal-overlay" id="modalAlumno">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-header">
      <div>
        <div class="modal-header-title">Agregar alumno</div>
        <div class="modal-header-sub">
          <?php foreach($materias as $m): if($m['id']==$materia_sel) { echo htmlspecialchars($m['nombre']); break; } endforeach; ?>
          —
          <?php foreach($grupos as $g): if($g['id']==$grupo_sel) { echo htmlspecialchars($g['nombre']); break; } endforeach; ?>
        </div>
      </div>
      <button class="modal-close" id="btnCerrarModalAlumno">✕</button>
    </div>
    <div class="modal-body">
      <form action="<?= BASE_URL ?>/Controlador/DocenteControlador.php" method="POST">
        <input type="hidden" name="accion"      value="agregar_alumno">
        <input type="hidden" name="id_materia"  value="<?= $materia_sel ?>">
        <input type="hidden" name="id_grupo"    value="<?= $grupo_sel ?>">
        <input type="hidden" name="materia_ret" value="<?= $materia_sel ?>">
        <input type="hidden" name="grupo_ret"   value="<?= $grupo_sel ?>">
        <input type="hidden" name="fecha_ret"   value="<?= $fecha_sel ?>">
 
        <div style="display:flex;flex-direction:column;gap:14px;">
          <div class="sel-card">
            <div class="sel-label">Matrícula</div>
            <input type="text" name="matricula" placeholder="Ej: 2024011" required
                   style="width:100%;border:none;outline:none;font-size:14px;font-weight:500;background:transparent;">
          </div>
          <div class="sel-card">
            <div class="sel-label">Nombre completo</div>
            <input type="text" name="nombre_completo" placeholder="Ej: Juan Pérez García" required
                   style="width:100%;border:none;outline:none;font-size:14px;font-weight:500;background:transparent;">
          </div>
        </div>
 
        <div style="padding:18px 0 0;display:flex;justify-content:flex-end;gap:10px;">
          <button type="button" id="btnCancelarAlumno">Cancelar</button>
          <button type="submit" class="primary">Agregar alumno</button>
        </div>
      </form>
    </div>
  </div>
</div>
 
<!-- MODAL CONFIRMAR GUARDAR -->
<div class="modal-overlay" id="modalConfirm">
  <div class="modal-box" style="max-width:380px;text-align:center;">
    <div class="modal-header" style="justify-content:center;">
      <div class="modal-header-title">Guardar asistencia</div>
    </div>
    <div class="modal-body" style="padding:28px 22px;">
      <p style="font-size:15px;margin-bottom:6px;">¿Deseas guardar el registro?</p>
      <p style="font-size:13px;color:var(--color-muted);">Los cambios se guardarán en la base de datos.</p>
    </div>
    <div class="modal-footer" style="justify-content:center;gap:16px;">
      <button type="button" id="btnConfirmNo">Cancelar</button>
      <button type="button" id="btnConfirmSi" class="primary">Sí, guardar</button>
    </div>
  </div>
</div>
 
<!-- ════ LAYOUT CON SIDEBAR ════ -->
<div class="with-sidebar">
 
  <?php
    $pagina_actual = 'panel';
    $justificantes_pendientes_count = count($justificantes);
    require_once __DIR__ . '/layout/sidebar.php';
  ?>
 
  <div class="sidebar-content">
    <div class="container">
 
      <!-- HEADER -->
      <div class="header">
        <div class="header-left">
          <div class="avatar">
            <?= strtoupper(substr($docente['nombre'],0,1).substr(strrchr($docente['nombre'],' '),1,1)) ?>
          </div>
          <div>
            <div class="header-name">Prof. <?= htmlspecialchars($docente['nombre']) ?></div>
            <div class="header-sub">Docente — <?= htmlspecialchars($docente['materia_principal']) ?></div>
          </div>
        </div>
        <span class="badge badge-primary"><?= htmlspecialchars($docente['turno']) ?></span>
      </div>
 
      <div class="main">
 
        <!-- SELECTORES -->
        <div class="selectors">
          <div class="sel-card">
            <div class="sel-label">Materia</div>
            <select id="sel_materia" name="materia">
              <?php foreach($materias as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ($m['id']==$materia_sel)?'selected':'' ?>>
                  <?= htmlspecialchars($m['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="sel-card">
            <div class="sel-label">Grupo</div>
            <select id="sel_grupo" name="grupo">
              <?php foreach($grupos as $g): ?>
                <option value="<?= $g['id'] ?>" <?= ($g['id']==$grupo_sel)?'selected':'' ?>>
                  <?= htmlspecialchars($g['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="sel-card">
            <div class="sel-label">Fecha</div>
            <input type="hidden" id="fecha_real" value="<?= $fecha_sel ?>">
            <input type="text" id="fecha" class="input-date" readonly>
          </div>
        </div>
 
        <!-- BOTONES -->
        <div style="margin:15px 0;display:flex;gap:10px;flex-wrap:wrap;">
          <button type="button" class="primary" id="btnAbrirModalCSV">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Subir lista CSV
          </button>
          <button type="button" id="btnAbrirModalAlumno">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            Agregar alumno
          </button>
        </div>
 
        <!-- ESTADÍSTICAS -->
        <div class="stats">
          <div class="stat">
            <div class="stat-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-num"><?= $stats['total'] ?></div>
            <div class="stat-lbl">Total alumnos</div>
          </div>
          <div class="stat">
            <div class="stat-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-num"><?= $stats['presentes'] ?></div>
            <div class="stat-lbl">Presentes</div>
          </div>
          <div class="stat">
            <div class="stat-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="stat-num"><?= $stats['ausentes'] ?></div>
            <div class="stat-lbl">Ausentes</div>
          </div>
          <div class="stat">
            <div class="stat-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-num"><?= $stats['retardos'] ?></div>
            <div class="stat-lbl">Retardos</div>
          </div>
        </div>
 
        <!-- TABLA -->
        <div class="section-title">Registro de asistencia</div>
 
        <form id="formAsistencia" action="<?= BASE_URL ?>/Controlador/DocenteControlador.php" method="POST">
          <input type="hidden" name="accion"  value="guardar_asistencia"/>
          <input type="hidden" name="grupo"   value="<?= $grupo_sel ?>"/>
          <input type="hidden" name="materia" value="<?= $materia_sel ?>"/>
          <input type="hidden" name="fecha"   id="form_fecha" value="<?= $fecha_sel ?>"/>
 
          <div class="table-wrap"><div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Matrícula</th>
                  <th>Alumno</th>
                  <th>Estatus</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach($alumnos as $a): ?>
              <tr>
                <td style="color:var(--color-muted)"><?= htmlspecialchars($a['matricula']) ?></td>
                <td><?= htmlspecialchars($a['nombre']) ?></td>
                <td>
                  <?php
                    $estatus = strtolower($a['estatus']);
                    $clase = match($estatus) {
                      'presente'    => 'badge-green',
                      'ausente'     => 'badge-red',
                      'retardo'     => 'badge-amber',
                      'justificada' => 'badge-purple',
                      default       => 'badge-pending',
                    };
                  ?>
                  <span class="badge <?= $clase ?>"><?= ucfirst($estatus) ?></span>
                </td>
                <td>
                  <select name="estatus[<?= $a['id'] ?>]" class="sel-inline">
                    <?php foreach(['pendiente','presente','ausente','retardo','justificada'] as $op): ?>
                      <option value="<?= $op ?>" <?= ($estatus==$op)?'selected':'' ?>>
                        <?= ucfirst($op) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
</div></div>
 
          <div class="btn-row">
            <button type="submit" name="cerrar" value="0" id="btnGuardar" class="primary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              Guardar asistencia
            </button>
          </div>
        </form>
 
        <!-- JUSTIFICANTES PENDIENTES -->
        <div class="section-title">Justificantes pendientes</div>
        <?php if(empty($justificantes)): ?>
          <p style="font-size:13px;color:var(--color-muted);">No hay justificantes pendientes.</p>
        <?php else: ?>
          <?php foreach($justificantes as $j): ?>
          <div class="just-card">
            <div class="just-info">
              <div class="just-name"><?= htmlspecialchars($j['alumno']) ?></div>
              <div class="just-meta"><?= htmlspecialchars($j['motivo']) ?></div>
            </div>
            <div class="just-actions">
              <a href="<?= BASE_URL ?>/Controlador/JustificanteControlador.php?accion=ver&id=<?= $j['id'] ?>">
                <button type="button">Ver detalle</button>
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
 
      </div>
    </div>
  </div><!-- /sidebar-content -->
</div><!-- /with-sidebar -->
 
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
  var fechaActual   = "<?= $fecha_sel ?>";
  var materiaActual = <?= $materia_sel ?>;
  var grupoActual   = <?= $grupo_sel ?>;
</script>
<script src="<?= BASE_URL ?>/js/asistencia.js"></script>
 
</body>
</html>