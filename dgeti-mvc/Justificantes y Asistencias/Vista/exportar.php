<?php
// Vista/exportar.php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Exportar CSV — CBTIS 199</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/estilos.css"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css"/>
</head>
<body class="panel-body">

<div class="mobile-topbar">
  <button class="mobile-topbar-btn" id="btnOpenSidebar">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>
  <span class="mobile-topbar-title">Exportar CSV</span>
</div>

<div class="with-sidebar">
  <?php require_once __DIR__ . '/layout/sidebar.php'; ?>

  <div class="sidebar-content">
    <div class="container">

      <div class="header">
        <div class="header-left">
          <div class="avatar"><?= strtoupper(substr($docente['nombre'], 0, 1)) ?></div>
          <div>
            <div class="header-name">Exportar Asistencias</div>
            <div class="header-sub">Genera y descarga reportes en formato CSV</div>
          </div>
        </div>
        <span class="badge badge-primary">Exportar</span>
      </div>

      <div class="main">

        <div class="section-title">Generar nuevo reporte</div>

        <form id="formExportar">
          <div class="selectors" style="grid-template-columns: 1fr 1fr 1fr 1fr; gap:12px; margin-bottom:20px;">
            <div class="sel-card">
              <div class="sel-label">Materia</div>
              <select name="materia" id="sel_materia">
                <?php foreach ($materias as $m): ?>
                  <option value="<?= $m['id'] ?>" <?= ($m['id'] == $materia_sel) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="sel-card">
              <div class="sel-label">Grupo</div>
              <select name="grupo" id="sel_grupo">
                <?php foreach ($grupos as $g): ?>
                  <option value="<?= $g['id'] ?>" <?= ($g['id'] == $grupo_sel) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="sel-card">
              <div class="sel-label">Desde</div>
              <input type="date" name="fecha_ini" value="<?= htmlspecialchars($fecha_ini) ?>"
                     style="background:transparent;border:none;outline:none;font-size:13px;font-weight:600;width:100%;"/>
            </div>
            <div class="sel-card">
              <div class="sel-label">Hasta</div>
              <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>"
                     style="background:transparent;border:none;outline:none;font-size:13px;font-weight:600;width:100%;"/>
            </div>
          </div>

          <div style="display:flex; gap:10px; align-items:center; margin-bottom:28px;">
            <button type="submit" class="primary" id="btnExportar">
              📥 Generar y descargar CSV
            </button>
            <span style="font-size:12px; color:var(--color-muted); font-family:Arial,sans-serif;">
              El archivo se abrirá en Excel automáticamente (UTF-8 con BOM)
            </span>
          </div>
        </form>

        <!-- TABLA DE ARCHIVOS -->
        <div class="section-title">Archivos generados anteriormente</div>

        <div id="sinArchivos" style="<?= !empty($archivos_generados) ? 'display:none;' : '' ?>background:#f9f6f2; border-radius:10px; padding:28px; text-align:center; color:var(--color-muted); font-size:14px; border:1px solid var(--color-border);">
          No hay archivos generados todavía.
        </div>

        <div id="tablaWrap" style="<?= empty($archivos_generados) ? 'display:none;' : '' ?>">
          <div class="table-wrap"><div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Archivo</th>
                  <th>Generado</th>
                  <th>Tamaño</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="tablaBody">
                <?php foreach ($archivos_generados as $arch): ?>
                <tr>
                  <td><span style="font-size:13px;font-weight:500;font-family:Arial,sans-serif;">📄 <?= htmlspecialchars($arch['nombre']) ?></span></td>
                  <td style="color:var(--color-muted);font-size:12px;"><?= $arch['fecha'] ?></td>
                  <td style="color:var(--color-muted);font-size:12px;"><?= number_format($arch['tamano']/1024,1) ?> KB</td>
                  <td>
                    <div style="display:flex;gap:8px;">
                      <a href="<?= BASE_URL ?>/Controlador/ExportarControlador.php?accion=descargar_guardado&archivo=<?= urlencode($arch['nombre']) ?>">
                        <button type="button" class="primary" style="font-size:11px;padding:5px 12px;">⬇ Descargar</button>
                      </a>
                      <a href="<?= BASE_URL ?>/Controlador/ExportarControlador.php?accion=eliminar&archivo=<?= urlencode($arch['nombre']) ?>"
                         onclick="return confirm('¿Eliminar este archivo?')">
                        <button type="button" style="font-size:11px;padding:5px 12px;color:var(--color-absent-txt);border-color:var(--color-absent-bg);">🗑 Eliminar</button>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div></div>
        </div>

        <div style="margin-top:24px;background:#f9f6f2;border-radius:10px;padding:18px;border:1px solid var(--color-border);font-size:13px;font-family:Arial,sans-serif;color:var(--color-muted);">
          <strong style="color:var(--color-text);">📂 ¿Dónde se guardan los archivos?</strong><br>
          Los CSV generados se almacenan en la carpeta <code>uploads/excel/</code> dentro del proyecto.<br>
          Puedes descargarlos desde esta página en cualquier momento.
        </div>

      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/js/exportar.js"></script>
</body>
</html>