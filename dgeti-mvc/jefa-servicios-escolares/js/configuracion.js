// configuracion.js - Lógica del panel de configuración

// Datos por defecto
const configuracionDefault = {
    tiposPermiso: [
        { id: 1, nombre: 'Licencia médica', activo: true, maxDias: 15 },
        { id: 2, nombre: 'Permiso particular', activo: true, maxDias: 3 },
        { id: 3, nombre: 'Capacitación', activo: true, maxDias: 5 },
        { id: 4, nombre: 'Comisión oficial', activo: true, maxDias: 10 }
    ],
    notificaciones: {
        alAprobar: true,
        alRechazar: true,
        aRH: true,
        aJefe: false,
        correoCopia: ''
    },
    formato: {
        institucion: 'CENTRO DE BACHILLERATO TECNOLÓGICO INDUSTRIAL Y DE SERVICIOS No. 199',
        encabezado: 'SERVICIOS ESCOLARES - PERMISO OFICIAL',
        textoFirma: 'Jefa de Servicios Escolares'
    },
    seguridad: {
        tiempoSesion: 480
    }
};

// Cargar configuración guardada o usar default
let configuracion = JSON.parse(localStorage.getItem('configuracionSistema')) || configuracionDefault;

document.addEventListener('DOMContentLoaded', () => {
    inicializarEventos();
    cargarConfiguracion();
    renderizarTiposPermiso();
});

function inicializarEventos() {
    // Navegación
    document.getElementById('volverBtn')?.addEventListener('click', () => {
        window.location.href = 'index.html';
    });
    
    document.getElementById('logoutBtn')?.addEventListener('click', () => {
        mostrarNotificacion('Sesión cerrada correctamente');
    });
    
    // Pestañas
    document.querySelectorAll('.config-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.dataset.tab;
            cambiarPestaña(tabId);
        });
    });
    
    // Agregar tipo de permiso
    document.getElementById('agregarTipoBtn')?.addEventListener('click', agregarNuevoTipo);
    
    // Guardar configuración
    document.getElementById('guardarBtn')?.addEventListener('click', guardarConfiguracion);
    document.getElementById('cancelarBtn')?.addEventListener('click', cancelarCambios);
    
    // Botón peligroso - resetear
    document.getElementById('resetearConfigBtn')?.addEventListener('click', resetearConfiguracion);
    
    // Exportar datos
    document.getElementById('exportarDatosBtn')?.addEventListener('click', exportarDatos);
    
    // Restaurar logo
    document.getElementById('restaurarLogoBtn')?.addEventListener('click', restaurarLogo);
    
    // Vista previa
    document.getElementById('vistaPreviaBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        mostrarNotificacion('Vista previa del permiso generada', 'info');
    });
}

function cambiarPestaña(tabId) {
    // Cambiar pestañas visualmente
    document.querySelectorAll('.config-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`.config-tab[data-tab="${tabId}"]`).classList.add('active');
    
    // Cambiar paneles
    document.querySelectorAll('.config-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    document.getElementById(`panel-${tabId}`).classList.add('active');
}

function cargarConfiguracion() {
    // Cargar notificaciones
    document.getElementById('notificarAprobacion').checked = configuracion.notificaciones.alAprobar;
    document.getElementById('notificarRechazo').checked = configuracion.notificaciones.alRechazar;
    document.getElementById('notificarRH').checked = configuracion.notificaciones.aRH;
    document.getElementById('notificarJefe').checked = configuracion.notificaciones.aJefe;
    document.getElementById('correoCopia').value = configuracion.notificaciones.correoCopia || '';
    
    // Cargar formato
    document.getElementById('institucionNombre').value = configuracion.formato.institucion;
    document.getElementById('encabezadoTexto').value = configuracion.formato.encabezado;
    document.getElementById('textoFirma').value = configuracion.formato.textoFirma;
    
    // Cargar seguridad
    document.getElementById('tiempoSesion').value = configuracion.seguridad.tiempoSesion;
}

function renderizarTiposPermiso() {
    const container = document.getElementById('tiposLista');
    if (!container) return;
    
    container.innerHTML = configuracion.tiposPermiso.map(tipo => `
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
    
    // Asignar eventos a los nuevos elementos
    document.querySelectorAll('.tipo-editar').forEach((btn, index) => {
        btn.addEventListener('click', () => editarNombreTipo(index));
    });
    
    document.querySelectorAll('.tipo-eliminar').forEach((btn, index) => {
        btn.addEventListener('click', () => eliminarTipo(index));
    });
}

function agregarNuevoTipo() {
    const nuevoId = Date.now();
    const nuevoTipo = {
        id: nuevoId,
        nombre: 'Nuevo tipo',
        activo: true,
        maxDias: 1
    };
    configuracion.tiposPermiso.push(nuevoTipo);
    renderizarTiposPermiso();
    mostrarNotificacion('Nuevo tipo de permiso agregado', 'success');
}

function editarNombreTipo(index) {
    const nuevoNombre = prompt('Ingrese el nuevo nombre del tipo de permiso:', configuracion.tiposPermiso[index].nombre);
    if (nuevoNombre && nuevoNombre.trim()) {
        configuracion.tiposPermiso[index].nombre = nuevoNombre.trim();
        renderizarTiposPermiso();
        mostrarNotificacion('Tipo de permiso actualizado', 'success');
    }
}

function eliminarTipo(index) {
    if (confirm(`¿Eliminar "${configuracion.tiposPermiso[index].nombre}"?`)) {
        configuracion.tiposPermiso.splice(index, 1);
        renderizarTiposPermiso();
        mostrarNotificacion('Tipo de permiso eliminado', 'success');
    }
}

function guardarConfiguracion() {
    // Guardar tipos de permiso desde los inputs actuales
    const tiposActualizados = [];
    document.querySelectorAll('.tipo-item').forEach(item => {
        const id = parseInt(item.dataset.id);
        const activo = item.querySelector('.tipo-activo').checked;
        const maxDias = parseInt(item.querySelector('.tipo-maxdias').value);
        const nombre = item.querySelector('.tipo-nombre').textContent;
        
        tiposActualizados.push({ id, nombre, activo, maxDias });
    });
    configuracion.tiposPermiso = tiposActualizados;
    
    // Guardar notificaciones
    configuracion.notificaciones = {
        alAprobar: document.getElementById('notificarAprobacion').checked,
        alRechazar: document.getElementById('notificarRechazo').checked,
        aRH: document.getElementById('notificarRH').checked,
        aJefe: document.getElementById('notificarJefe').checked,
        correoCopia: document.getElementById('correoCopia').value
    };
    
    // Guardar formato
    configuracion.formato = {
        institucion: document.getElementById('institucionNombre').value,
        encabezado: document.getElementById('encabezadoTexto').value,
        textoFirma: document.getElementById('textoFirma').value
    };
    
    // Guardar seguridad
    configuracion.seguridad = {
        tiempoSesion: parseInt(document.getElementById('tiempoSesion').value)
    };
    
    // Guardar en localStorage
    localStorage.setItem('configuracionSistema', JSON.stringify(configuracion));
    
    mostrarNotificacion('✅ Configuración guardada correctamente', 'success');
}

function cancelarCambios() {
    if (confirm('¿Cancelar cambios? Se perderán las modificaciones no guardadas.')) {
        configuracion = JSON.parse(localStorage.getItem('configuracionSistema')) || configuracionDefault;
        cargarConfiguracion();
        renderizarTiposPermiso();
        mostrarNotificacion('Cambios descartados', 'info');
    }
}

function resetearConfiguracion() {
    if (confirm('⚠️ ¿Restablecer toda la configuración a los valores por defecto? Esta acción no se puede deshacer.')) {
        configuracion = JSON.parse(JSON.stringify(configuracionDefault));
        localStorage.setItem('configuracionSistema', JSON.stringify(configuracion));
        cargarConfiguracion();
        renderizarTiposPermiso();
        mostrarNotificacion('Configuración restablecida a valores por defecto', 'success');
    }
}

function exportarDatos() {
    const datosExportar = {
        configuracion: configuracion,
        fechaExportacion: new Date().toISOString(),
        version: '1.0'
    };
    
    const blob = new Blob([JSON.stringify(datosExportar, null, 2)], { type: 'application/json' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.setAttribute('download', `configuracion_sistema_${new Date().toISOString().split('T')[0]}.json`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    mostrarNotificacion('📥 Datos exportados correctamente', 'success');
}

function restaurarLogo() {
    mostrarNotificacion('Logo restaurado al original', 'success');
}