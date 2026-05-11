// configuracion.js - Lógica del panel de configuración

const configuracionDefault = {
    tiposJustificante: [
        { id: 1, nombre: 'Justificante médico', activo: true, maxDias: 15 },
        { id: 2, nombre: 'Permiso particular', activo: true, maxDias: 3 },
        { id: 3, nombre: 'Actividad escolar', activo: true, maxDias: 5 },
        { id: 4, nombre: 'Representación deportiva', activo: true, maxDias: 10 }
    ],
    notificaciones: {
        alAprobar: true,
        alRechazar: true,
        alTutor: true,
        alDocente: false,
        correoCopia: ''
    },
    formato: {
        institucion: 'CENTRO DE BACHILLERATO TECNOLÓGICO INDUSTRIAL Y DE SERVICIOS No. 199',
        encabezado: 'SERVICIOS ESCOLARES - JUSTIFICANTE DE FALTA',
        textoFirma: 'Jefa de Servicios Escolares'
    },
    seguridad: {
        tiempoSesion: 480
    }
};

let configuracion = JSON.parse(localStorage.getItem('configuracionJustificantes')) || configuracionDefault;

document.addEventListener('DOMContentLoaded', () => {
    inicializarEventos();
    cargarConfiguracion();
    renderizarTiposJustificante();
});

function inicializarEventos() {
    document.getElementById('volverBtn')?.addEventListener('click', () => {
        window.location.href = 'index.html';
    });
    
    document.getElementById('logoutBtn')?.addEventListener('click', () => {
        mostrarNotificacion('Sesión cerrada correctamente', 'success');
    });
    
    document.querySelectorAll('.config-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.dataset.tab;
            cambiarPestaña(tabId);
        });
    });
    
    document.getElementById('agregarTipoBtn')?.addEventListener('click', agregarNuevoTipo);
    document.getElementById('guardarBtn')?.addEventListener('click', guardarConfiguracion);
    document.getElementById('cancelarBtn')?.addEventListener('click', cancelarCambios);
    document.getElementById('resetearConfigBtn')?.addEventListener('click', resetearConfiguracion);
    document.getElementById('exportarDatosBtn')?.addEventListener('click', exportarDatos);
    document.getElementById('restaurarLogoBtn')?.addEventListener('click', restaurarLogo);
    document.getElementById('vistaPreviaBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        mostrarNotificacion('Vista previa del justificante generada', 'info');
    });
}

function cambiarPestaña(tabId) {
    document.querySelectorAll('.config-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`.config-tab[data-tab="${tabId}"]`).classList.add('active');
    
    document.querySelectorAll('.config-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    document.getElementById(`panel-${tabId}`).classList.add('active');
}

function cargarConfiguracion() {
    document.getElementById('notificarAprobacion').checked = configuracion.notificaciones.alAprobar;
    document.getElementById('notificarRechazo').checked = configuracion.notificaciones.alRechazar;
    document.getElementById('notificarTutor').checked = configuracion.notificaciones.alTutor;
    document.getElementById('notificarDocente').checked = configuracion.notificaciones.alDocente;
    document.getElementById('correoCopia').value = configuracion.notificaciones.correoCopia || '';
    
    document.getElementById('institucionNombre').value = configuracion.formato.institucion;
    document.getElementById('encabezadoTexto').value = configuracion.formato.encabezado;
    document.getElementById('textoFirma').value = configuracion.formato.textoFirma;
    
    document.getElementById('tiempoSesion').value = configuracion.seguridad.tiempoSesion;
}

function renderizarTiposJustificante() {
    const container = document.getElementById('tiposLista');
    if (!container) return;
    
    container.innerHTML = configuracion.tiposJustificante.map(tipo => `
        <div class="tipo-item" data-id="${tipo.id}">
            <div class="tipo-info">
                <input type="checkbox" class="tipo-activo" ${tipo.activo ? 'checked' : ''}>
                <span class="tipo-nombre">${tipo.nombre}</span>
                <div class="tipo-dias">
                    <input type="number" class="tipo-maxdias" value="${tipo.maxDias}" min="1" max="30" style="width: 70px;">
                    <span>días máx</span>
                </div>
            </div>
            <div class="tipo-acciones">
                <button class="btn-editar tipo-editar" title="Editar nombre">✏️</button>
                <button class="btn-eliminar tipo-eliminar" title="Eliminar">🗑️</button>
            </div>
        </div>
    `).join('');
    
    document.querySelectorAll('.tipo-editar').forEach((btn, index) => {
        btn.addEventListener('click', () => editarNombreTipo(index));
    });
    
    document.querySelectorAll('.tipo-eliminar').forEach((btn, index) => {
        btn.addEventListener('click', () => eliminarTipo(index));
    });
}

function agregarNuevoTipo() {
    const nuevoId = Date.now();
    configuracion.tiposJustificante.push({
        id: nuevoId,
        nombre: 'Nuevo tipo',
        activo: true,
        maxDias: 1
    });
    renderizarTiposJustificante();
    mostrarNotificacion('Nuevo tipo de justificante agregado', 'success');
}

function editarNombreTipo(index) {
    const nuevoNombre = prompt('Ingrese el nuevo nombre:', configuracion.tiposJustificante[index].nombre);
    if (nuevoNombre && nuevoNombre.trim()) {
        configuracion.tiposJustificante[index].nombre = nuevoNombre.trim();
        renderizarTiposJustificante();
        mostrarNotificacion('Tipo de justificante actualizado', 'success');
    }
}

function eliminarTipo(index) {
    if (confirm(`¿Eliminar "${configuracion.tiposJustificante[index].nombre}"?`)) {
        configuracion.tiposJustificante.splice(index, 1);
        renderizarTiposJustificante();
        mostrarNotificacion('Tipo de justificante eliminado', 'success');
    }
}

function guardarConfiguracion() {
    const tiposActualizados = [];
    document.querySelectorAll('.tipo-item').forEach(item => {
        const id = parseInt(item.dataset.id);
        const activo = item.querySelector('.tipo-activo').checked;
        const maxDias = parseInt(item.querySelector('.tipo-maxdias').value);
        const nombre = item.querySelector('.tipo-nombre').textContent;
        tiposActualizados.push({ id, nombre, activo, maxDias });
    });
    configuracion.tiposJustificante = tiposActualizados;
    
    configuracion.notificaciones = {
        alAprobar: document.getElementById('notificarAprobacion').checked,
        alRechazar: document.getElementById('notificarRechazo').checked,
        alTutor: document.getElementById('notificarTutor').checked,
        alDocente: document.getElementById('notificarDocente').checked,
        correoCopia: document.getElementById('correoCopia').value
    };
    
    configuracion.formato = {
        institucion: document.getElementById('institucionNombre').value,
        encabezado: document.getElementById('encabezadoTexto').value,
        textoFirma: document.getElementById('textoFirma').value
    };
    
    configuracion.seguridad = {
        tiempoSesion: parseInt(document.getElementById('tiempoSesion').value)
    };
    
    localStorage.setItem('configuracionJustificantes', JSON.stringify(configuracion));
    mostrarNotificacion('✅ Configuración guardada correctamente', 'success');
}

function cancelarCambios() {
    if (confirm('¿Cancelar cambios? Se perderán las modificaciones no guardadas.')) {
        configuracion = JSON.parse(localStorage.getItem('configuracionJustificantes')) || configuracionDefault;
        cargarConfiguracion();
        renderizarTiposJustificante();
        mostrarNotificacion('Cambios descartados', 'info');
    }
}

function resetearConfiguracion() {
    if (confirm('⚠️ ¿Restablecer toda la configuración a los valores por defecto?')) {
        configuracion = JSON.parse(JSON.stringify(configuracionDefault));
        localStorage.setItem('configuracionJustificantes', JSON.stringify(configuracion));
        cargarConfiguracion();
        renderizarTiposJustificante();
        mostrarNotificacion('Configuración restablecida', 'success');
    }
}

function exportarDatos() {
    const datosExportar = { configuracion, fechaExportacion: new Date().toISOString(), version: '1.0' };
    const blob = new Blob([JSON.stringify(datosExportar, null, 2)], { type: 'application/json' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.setAttribute('download', `configuracion_justificantes_${new Date().toISOString().split('T')[0]}.json`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    mostrarNotificacion('📥 Datos exportados correctamente', 'success');
}

function restaurarLogo() {
    mostrarNotificacion('Logo restaurado al original', 'success');
}