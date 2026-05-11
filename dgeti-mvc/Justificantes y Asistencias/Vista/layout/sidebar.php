<?php
// Vista/layout/sidebar.php
require_once __DIR__ . '/../../config/config.php';

$pagina_actual = $pagina_actual ?? '';
?>
<div class="sidebar" id="sidebar">

  <!-- LOGO / ENCABEZADO -->
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
        <rect width="48" height="48" rx="10" fill="#D4C19C"/>
        <path d="M14 34V20l10-8 10 8v14H30v-8h-4v8H14Z" fill="#621132"/>
      </svg>
    </div>
    <div class="sidebar-brand">
      <span class="sidebar-brand-title">CBTIS 199</span>
      <span class="sidebar-brand-sub">Asistencias</span>
    </div>
    <button class="sidebar-toggle" id="btnToggleSidebar" title="Cerrar menú">&#9776;</button>
  </div>

  <!-- NAVEGACIÓN -->
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Principal</div>

    <a href="<?= BASE_URL ?>/Controlador/DocenteControlador.php?accion=dashboard"
       class="sidebar-link <?= ($pagina_actual === 'dashboard') ? 'active' : '' ?>">
      <span class="sidebar-icon">🏠</span>
      <span class="sidebar-link-text">Dashboard</span>
    </a>

    <a href="<?= BASE_URL ?>/Controlador/DocenteControlador.php?accion=panel&fecha=<?= date('Y-m-d') ?>"
       class="sidebar-link <?= ($pagina_actual === 'panel') ? 'active' : '' ?>">
      <span class="sidebar-icon">✅</span>
      <span class="sidebar-link-text">Tomar Asistencia</span>
    </a>

    <div class="sidebar-section-label">Reportes</div>

    <a href="<?= BASE_URL ?>/Controlador/HistorialControlador.php?accion=historial"
       class="sidebar-link <?= ($pagina_actual === 'historial') ? 'active' : '' ?>">
      <span class="sidebar-icon">📋</span>
      <span class="sidebar-link-text">Historial por Grupo</span>
    </a>

    <a href="<?= BASE_URL ?>/Controlador/ExportarControlador.php?accion=exportar"
       class="sidebar-link <?= ($pagina_actual === 'exportar') ? 'active' : '' ?>">
      <span class="sidebar-icon">📥</span>
      <span class="sidebar-link-text">Exportar CSV</span>
    </a>

    <div class="sidebar-section-label">Gestión</div>

    <a href="<?= BASE_URL ?>/Controlador/JustificanteControlador.php?accion=justificantes"
       class="sidebar-link <?= ($pagina_actual === 'justificantes') ? 'active' : '' ?>">
      <span class="sidebar-icon">📝</span>
      <span class="sidebar-link-text">Justificantes</span>
      <?php if (!empty($justificantes_pendientes_count) && $justificantes_pendientes_count > 0): ?>
        <span class="sidebar-badge"><?= $justificantes_pendientes_count ?></span>
      <?php endif; ?>
    </a>

  </nav>

  <!-- PIE — nombre del docente + botón cerrar sesión -->
  <div class="sidebar-footer">
    <div class="sidebar-user-info">
      <div class="sidebar-avatar">
        <?= strtoupper(substr($docente['nombre'] ?? 'D', 0, 1)) ?>
      </div>
      <div>
        <div class="sidebar-user-name"><?= htmlspecialchars($docente['nombre'] ?? 'Docente') ?></div>
        <div class="sidebar-user-role">Docente</div>
      </div>
    </div>
    <!-- Cierra la sesión correctamente -->
    <a href="<?= BASE_URL ?>/Controlador/LoginControlador.php?accion=logout"
       class="sidebar-logout" title="Cerrar sesión">⏻</a>
  </div>

</div>

<!-- OVERLAY para cerrar sidebar en móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>