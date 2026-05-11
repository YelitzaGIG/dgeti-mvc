// js/historial.js
document.addEventListener('DOMContentLoaded', () => {

  iniciarSidebar();

  const formFiltros  = document.getElementById('formFiltros');
  const selMateria   = document.getElementById('sel_materia');
  const selGrupo     = document.getElementById('sel_grupo');
  const grupoActual  = selGrupo ? selGrupo.value : '';

  // ── Cuando cambia la materia: actualizar grupos vía AJAX ─────
  if (selMateria) {
    selMateria.addEventListener('change', () => {
      const idMateria = selMateria.value;
      const url = `HistorialControlador.php?accion=get_grupos&materia=${idMateria}`;

      fetch(url)
        .then(r => r.json())
        .then(grupos => {
          selGrupo.innerHTML = '';
          grupos.forEach(g => {
            const opt = document.createElement('option');
            opt.value = g.id;
            opt.textContent = g.nombre;
            selGrupo.appendChild(opt);
          });
          // Enviar el form con el nuevo grupo
          formFiltros.submit();
        });
    });
  }

  // ── Cuando cambia el grupo: re-enviar form ───────────────────
  if (selGrupo) {
    selGrupo.addEventListener('change', () => formFiltros.submit());
  }

});

// ── Sidebar toggle (móvil) ────────────────────────────────────
function iniciarSidebar() {
  const sidebar   = document.getElementById('sidebar');
  const overlay   = document.getElementById('sidebarOverlay');
  const btnOpen   = document.getElementById('btnOpenSidebar');
  const btnToggle = document.getElementById('btnToggleSidebar');

  function abrirSidebar() {
    if (sidebar) sidebar.classList.add('open');
    if (overlay) overlay.classList.add('visible');
    document.body.style.overflow = 'hidden';
  }

  function cerrarSidebar() {
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('visible');
    document.body.style.overflow = '';
  }

  if (btnOpen)   btnOpen.addEventListener('click', abrirSidebar);
  if (btnToggle) btnToggle.addEventListener('click', cerrarSidebar);
  if (overlay)   overlay.addEventListener('click', cerrarSidebar);
}