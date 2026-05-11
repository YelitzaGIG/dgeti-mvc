// js/exportar.js
document.addEventListener('DOMContentLoaded', () => {

  iniciarSidebar();

  const selMateria  = document.getElementById('sel_materia');
  const selGrupo    = document.getElementById('sel_grupo');
  const form        = document.getElementById('formExportar');
  const btn         = document.getElementById('btnExportar');
  const tablaBody   = document.getElementById('tablaBody');
  const tablaWrap   = document.getElementById('tablaWrap');
  const sinArchivos = document.getElementById('sinArchivos');
  const BASE        = document.querySelector('link[href*="estilos"]')
                        .href.replace('/css/estilos.css','')
                        .replace(window.location.origin, '');

  // ── Cambio de materia → actualizar grupos ───────────────────
  if (selMateria) {
    selMateria.addEventListener('change', () => {
      fetch(`ExportarControlador.php?accion=get_grupos&materia=${selMateria.value}`)
        .then(r => r.json())
        .then(grupos => {
          selGrupo.innerHTML = '';
          grupos.forEach(g => {
            const o = document.createElement('option');
            o.value = g.id; o.textContent = g.nombre;
            selGrupo.appendChild(o);
          });
        });
    });
  }

  // ── Submit: AJAX → guarda + descarga + refresca tabla ───────
  if (form && btn) {
    form.addEventListener('submit', e => {
      e.preventDefault(); // No recarga la página

      btn.textContent = '⏳ Generando...';
      btn.disabled = true;

      const data = new FormData(form);
      data.append('accion', 'generar_ajax');

      fetch('ExportarControlador.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
          if (!res.ok) return;

          // 1. Descargar via iframe (sin bloqueo del navegador)
          const url    = `ExportarControlador.php?accion=descargar_guardado&archivo=${encodeURIComponent(res.archivo)}`;
          const iframe = document.createElement('iframe');
          iframe.style.display = 'none';
          iframe.src = url;
          document.body.appendChild(iframe);
          setTimeout(() => document.body.removeChild(iframe), 5000);

          // 2. Refrescar tabla con la lista actualizada
          renderTabla(res.lista);
        })
        .finally(() => {
          btn.textContent = '📥 Generar y descargar CSV';
          btn.disabled = false;
        });
    });
  }

  // ── Renderizar tabla de archivos ─────────────────────────────
  function renderTabla(lista) {
    if (!tablaBody) return;

    if (lista.length === 0) {
      sinArchivos.style.display = '';
      tablaWrap.style.display   = 'none';
      return;
    }

    sinArchivos.style.display = 'none';
    tablaWrap.style.display   = '';

    tablaBody.innerHTML = lista.map(a => `
      <tr>
        <td><span style="font-size:13px;font-weight:500;font-family:Arial,sans-serif;">📄 ${a.nombre}</span></td>
        <td style="color:var(--color-muted);font-size:12px;">${a.fecha}</td>
        <td style="color:var(--color-muted);font-size:12px;">${(a.tamano/1024).toFixed(1)} KB</td>
        <td>
          <div style="display:flex;gap:8px;">
            <a href="ExportarControlador.php?accion=descargar_guardado&archivo=${encodeURIComponent(a.nombre)}">
              <button type="button" class="primary" style="font-size:11px;padding:5px 12px;">⬇ Descargar</button>
            </a>
            <button type="button"
              onclick="eliminar('${a.nombre.replace(/'/g,"\\'")}')"
              style="font-size:11px;padding:5px 12px;color:var(--color-absent-txt);border-color:var(--color-absent-bg);">
              🗑 Eliminar
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // ── Eliminar archivo ─────────────────────────────────────────
  window.eliminar = function(nombre) {
    if (!confirm('¿Eliminar este archivo?')) return;
    window.location.href = `ExportarControlador.php?accion=eliminar&archivo=${encodeURIComponent(nombre)}`;
  };

});

// ── Sidebar toggle ────────────────────────────────────────────
function iniciarSidebar() {
  const sidebar   = document.getElementById('sidebar');
  const overlay   = document.getElementById('sidebarOverlay');
  const btnOpen   = document.getElementById('btnOpenSidebar');
  const btnToggle = document.getElementById('btnToggleSidebar');

  const abrir  = () => { sidebar?.classList.add('open'); overlay?.classList.add('visible'); document.body.style.overflow='hidden'; };
  const cerrar = () => { sidebar?.classList.remove('open'); overlay?.classList.remove('visible'); document.body.style.overflow=''; };

  btnOpen?.addEventListener('click', abrir);
  btnToggle?.addEventListener('click', cerrar);
  overlay?.addEventListener('click', cerrar);
}