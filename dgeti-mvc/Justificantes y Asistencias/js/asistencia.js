// js/asistencia.js

document.addEventListener('DOMContentLoaded', () => {

  const fechaRealInput = document.getElementById('fecha_real');
  const formFechaInput = document.getElementById('form_fecha');
  const selMateria     = document.getElementById('sel_materia');
  const selGrupo       = document.getElementById('sel_grupo');
  const formAsistencia = document.getElementById('formAsistencia');

  // ── Helpers de fecha ─────────────────────────────────────────
  const hoy = new Date();
  const hoyStr = hoy.getFullYear() + '-' +
    String(hoy.getMonth() + 1).padStart(2, '0') + '-' +
    String(hoy.getDate()).padStart(2, '0');

  const fechaInicial = (fechaRealInput && fechaRealInput.value) ? fechaRealInput.value : hoyStr;

  if (fechaRealInput && !fechaRealInput.value) {
    fechaRealInput.value = hoyStr;
  }

  // Formatea "2026-05-08" → "jueves, 08 de mayo de 2026"
  // Se parsea con T00:00:00 para forzar hora local y evitar desfase de zona horaria
  function formatearFechaES(ymd) {
    if (!ymd) return '';
    const [anio, mes, dia] = ymd.split('-').map(Number);
    const dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
    const meses = ['enero','febrero','marzo','abril','mayo','junio',
                   'julio','agosto','septiembre','octubre','noviembre','diciembre'];
    // Agregar T00:00:00 evita que JS interprete la fecha como UTC y retroceda un día
    const d = new Date(`${anio}-${String(mes).padStart(2,'0')}-${String(dia).padStart(2,'0')}T00:00:00`);
    return `${dias[d.getDay()]}, ${String(dia).padStart(2,'0')} de ${meses[mes-1]} de ${anio}`;
  }

  // Mostrar la fecha formateada en el input visible desde el inicio
  const inputFechaVisible = document.getElementById('fecha');
  if (inputFechaVisible) {
    inputFechaVisible.value = formatearFechaES(fechaInicial);
  }

  // ── Flatpickr (sin altInput, solo para el calendario visual) ─
  let flatpickrListo = false;

  flatpickr("#fecha", {
    dateFormat: "Y-m-d",
    altInput: false,
    maxDate: "today",
    onReady: function(selectedDates, dateStr, instance) {
      instance.setDate(fechaInicial, false);
      if (fechaRealInput) fechaRealInput.value = fechaInicial;
      if (inputFechaVisible) inputFechaVisible.value = formatearFechaES(fechaInicial);
      flatpickrListo = true;
    },
    onChange: function(selectedDates, dateStr) {
      if (!flatpickrListo) return;
      if (fechaRealInput) fechaRealInput.value = dateStr;
      if (inputFechaVisible) inputFechaVisible.value = formatearFechaES(dateStr);
      recargarPanel();
    }
  });

  // ── Toast según parámetro URL ────────────────────────────────
  const params   = new URLSearchParams(window.location.search);
  const guardado = params.get('guardado');
  if (guardado === '1') {
    mostrarToast('✅ Asistencia guardada correctamente');
  } else if (guardado === 'alumno') {
    mostrarToast('✅ Alumno agregado correctamente');
  }
  if (guardado) {
    const url = new URL(window.location.href);
    url.searchParams.delete('guardado');
    window.history.replaceState({}, '', url);
  }

  // ── Recargar al cambiar materia/grupo ────────────────────────
  if (selMateria) selMateria.addEventListener('change', async () => {
    const idMateria = selMateria.value;
    try {
      const resp   = await fetch('DocenteControlador.php?accion=get_grupos&materia=' + idMateria);
      const grupos = await resp.json();
      selGrupo.innerHTML = '';
      if (grupos.length === 0) {
        const opt = document.createElement('option');
        opt.value = '0'; opt.textContent = 'Sin grupos';
        selGrupo.appendChild(opt);
      } else {
        grupos.forEach(g => {
          const opt = document.createElement('option');
          opt.value = g.id; opt.textContent = g.nombre;
          selGrupo.appendChild(opt);
        });
      }
    } catch(e) {
      console.error('Error al obtener grupos:', e);
    }
    recargarPanel();
  });

  if (selGrupo) selGrupo.addEventListener('change', recargarPanel);

  // ── Badges y estadísticas en tiempo real ─────────────────────
  document.querySelectorAll('select[name^="estatus["]').forEach(sel => {
    sel.addEventListener('change', () => {
      const row   = sel.closest('tr');
      const badge = row.querySelector('.badge');
      if (badge) {
        const map = {
          'presente':    ['badge-green',   'Presente'],
          'ausente':     ['badge-red',     'Ausente'],
          'retardo':     ['badge-amber',   'Retardo'],
          'justificada': ['badge-purple',  'Justificada'],
          'pendiente':   ['badge-pending', 'Pendiente'],
        };
        const [cls, label] = map[sel.value] ?? ['badge-pending', sel.value];
        badge.className   = 'badge ' + cls;
        badge.textContent = label;
      }
      recalcularStats();
    });
  });
  recalcularStats();

  // ════════════════════════════════════════════════════════════
  //  MODAL CONFIRMACIÓN GUARDAR
  // ════════════════════════════════════════════════════════════
  const modalConfirm = document.getElementById('modalConfirm');
  const btnGuardar   = document.getElementById('btnGuardar');
  const btnConfirmSi = document.getElementById('btnConfirmSi');
  const btnConfirmNo = document.getElementById('btnConfirmNo');

  if (btnGuardar) {
    btnGuardar.addEventListener('click', e => {
      e.preventDefault();
      if (fechaRealInput && formFechaInput) formFechaInput.value = fechaRealInput.value;
      abrirModal(modalConfirm);
    });
  }
  if (btnConfirmNo) btnConfirmNo.addEventListener('click', () => cerrarModal(modalConfirm));
  if (btnConfirmSi) btnConfirmSi.addEventListener('click', () => {
    cerrarModal(modalConfirm);
    if (formAsistencia) formAsistencia.submit();
  });
  if (modalConfirm) modalConfirm.addEventListener('click', e => {
    if (e.target === modalConfirm) cerrarModal(modalConfirm);
  });

  // ════════════════════════════════════════════════════════════
  //  MODAL CSV
  // ════════════════════════════════════════════════════════════
  const modalCSV     = document.getElementById('modalCSV');
  const btnAbrirCSV  = document.getElementById('btnAbrirModalCSV');
  const btnCerrarCSV = document.getElementById('btnCerrarModalCSV');
  const btnCancelCSV = document.getElementById('btnCancelarCSV');
  const modalArchivo = document.getElementById('modal_archivo');
  const previewDiv   = document.getElementById('preview_nombre');

  if (btnAbrirCSV)  btnAbrirCSV.addEventListener('click',  () => abrirModal(modalCSV));
  if (btnCerrarCSV) btnCerrarCSV.addEventListener('click', () => cerrarModal(modalCSV));
  if (btnCancelCSV) btnCancelCSV.addEventListener('click', () => cerrarModal(modalCSV));
  if (modalCSV)     modalCSV.addEventListener('click', e => {
    if (e.target === modalCSV) cerrarModal(modalCSV);
  });

  if (modalArchivo) {
    modalArchivo.addEventListener('change', function () {
      const nombre = this.files[0]?.name ?? '';
      const partes = nombre.replace('.csv', '').split('_');
      if (partes.length >= 2 && previewDiv) {
        document.getElementById('txt_materia_detectada').textContent = partes.slice(0, -1).join(' ');
        document.getElementById('txt_grupo_detectado').textContent   = partes[partes.length - 1];
        previewDiv.style.display = 'block';
      } else if (previewDiv) {
        previewDiv.style.display = 'none';
      }
    });
  }

  // ════════════════════════════════════════════════════════════
  //  MODAL AGREGAR ALUMNO
  // ════════════════════════════════════════════════════════════
  const modalAlumno     = document.getElementById('modalAlumno');
  const btnAbrirAlumno  = document.getElementById('btnAbrirModalAlumno');
  const btnCerrarAlumno = document.getElementById('btnCerrarModalAlumno');
  const btnCancelAlumno = document.getElementById('btnCancelarAlumno');

  if (btnAbrirAlumno)  btnAbrirAlumno.addEventListener('click',  () => abrirModal(modalAlumno));
  if (btnCerrarAlumno) btnCerrarAlumno.addEventListener('click', () => cerrarModal(modalAlumno));
  if (btnCancelAlumno) btnCancelAlumno.addEventListener('click', () => cerrarModal(modalAlumno));
  if (modalAlumno)     modalAlumno.addEventListener('click', e => {
    if (e.target === modalAlumno) cerrarModal(modalAlumno);
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      cerrarModal(modalConfirm);
      cerrarModal(modalCSV);
      cerrarModal(modalAlumno);
    }
  });

});

// ── Abrir / cerrar modal genérico ────────────────────────────
function abrirModal(modal) {
  if (modal) { modal.classList.add('open'); document.body.style.overflow = 'hidden'; }
}
function cerrarModal(modal) {
  if (modal) { modal.classList.remove('open'); document.body.style.overflow = ''; }
}

// ── Toast ────────────────────────────────────────────────────
function mostrarToast(msg) {
  const toast = document.getElementById('notif');
  if (!toast) return;
  toast.textContent = msg;
  toast.style.display = 'block';
  toast.classList.add('notif-show');
  setTimeout(() => {
    toast.classList.remove('notif-show');
    setTimeout(() => { toast.style.display = 'none'; }, 400);
  }, 4000);
}

// ── Estadísticas en tiempo real ──────────────────────────────
function recalcularStats() {
  const selects = document.querySelectorAll('select[name^="estatus["]');
  let presentes = 0, ausentes = 0, retardos = 0;
  selects.forEach(sel => {
    if      (sel.value === 'presente') presentes++;
    else if (sel.value === 'ausente')  ausentes++;
    else if (sel.value === 'retardo')  retardos++;
  });
  const np = document.querySelector('.stat:nth-child(2) .stat-num');
  const na = document.querySelector('.stat:nth-child(3) .stat-num');
  const nr = document.querySelector('.stat:nth-child(4) .stat-num');
  if (np) np.textContent = presentes;
  if (na) na.textContent = ausentes;
  if (nr) nr.textContent = retardos;
}

// ── Recargar panel ───────────────────────────────────────────
function recargarPanel() {
  const materia = document.getElementById('sel_materia').value;
  const grupo   = document.getElementById('sel_grupo').value;
  const fecha   = document.getElementById('fecha_real')?.value || '';
  window.location.href =
    'DocenteControlador.php?accion=panel' +
    '&materia=' + materia +
    '&grupo='   + grupo   +
    '&fecha='   + fecha;
}