// revisar.js - Lógica para la revisión de solicitudes

const API_URL = 'http://localhost/Integrador/jefa-servicios-escolares/backend/api';

// Obtener ID de la solicitud desde la URL
const urlParams = new URLSearchParams(window.location.search);
const solicitudId = urlParams.get('id');
const accion = urlParams.get('accion');

const ID_JEFA_SERVICIOS = 1;

let datosSolicitud = null;

document.addEventListener('DOMContentLoaded', async () => {
    console.log('Página cargada, ID:', solicitudId);
    
    if (!solicitudId) {
        mostrarNotificacion('No se especificó una solicitud', 'error');
        setTimeout(() => window.location.href = 'index.html', 1500);
        return;
    }
    
    await cargarDatosSolicitud();
    inicializarEventos();
    
    if (accion === 'aprobar') {
        document.getElementById('aprobarBtn')?.focus();
    } else if (accion === 'rechazar') {
        document.getElementById('rechazarBtn')?.focus();
    }
});

async function cargarDatosSolicitud() {
    try {
        mostrarLoading(true);
        
        const url = `${API_URL}/permisos/detalle.php?id=${solicitudId}`;
        console.log('Consultando:', url);
        
        const respuesta = await fetch(url);
        
        if (!respuesta.ok) {
            throw new Error(`HTTP ${respuesta.status}`);
        }
        
        datosSolicitud = await respuesta.json();
        console.log('Datos recibidos:', datosSolicitud);
        
        if (datosSolicitud.error) {
            throw new Error(datosSolicitud.error);
        }
        
        llenarDatosEnFormulario();
        configurarMaximoDias();
        
        if (datosSolicitud.estado !== 'pendiente') {
            deshabilitarBotones();
        }
        
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al cargar la solicitud: ' + error.message, 'error');
        setTimeout(() => window.location.href = 'index.html', 2000);
    } finally {
        mostrarLoading(false);
    }
}

function llenarDatosEnFormulario() {
    if (!datosSolicitud) return;
    
    // Mostrar ID
    document.getElementById('solicitudId').textContent = datosSolicitud.id_permiso;
    
    // Datos del solicitante
    const nombreCompleto = `${datosSolicitud.nombre || ''} ${datosSolicitud.apellido || ''}`.trim();
    document.getElementById('solicitanteNombre').textContent = nombreCompleto || '---';
    document.getElementById('solicitanteRol').textContent = datosSolicitud.nombre_rol || (datosSolicitud.tipo_personal === 'docente' ? 'Docente' : 'Administrativo');
    document.getElementById('solicitanteDepto').textContent = datosSolicitud.departamento || 'No especificado';
    document.getElementById('solicitanteAntiguedad').textContent = datosSolicitud.antiguedad || '---';
    document.getElementById('solicitanteEmail').textContent = datosSolicitud.correo || '---';
    
    // Detalle de la solicitud
    const tipoPermisoMap = {
        'licencia_medica': 'Licencia médica',
        'licencia_media': 'Licencia médica',
        'permiso_particular': 'Permiso particular',
        'capacitacion': 'Capacitación',
        'comision_oficial': 'Comisión oficial'
    };
    document.getElementById('solicitudTipo').textContent = tipoPermisoMap[datosSolicitud.tipo_permiso] || datosSolicitud.tipo_permiso;
    document.getElementById('solicitudFecha').textContent = formatearFecha(datosSolicitud.fecha_solicitud);
    document.getElementById('solicitudPeriodo').textContent = `${formatearFecha(datosSolicitud.fecha_inicio)} al ${formatearFecha(datosSolicitud.fecha_fin)}`;
    
    // Calcular días solicitados
    const fechaInicio = new Date(datosSolicitud.fecha_inicio);
    const fechaFin = new Date(datosSolicitud.fecha_fin);
    const diasSolicitados = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1;
    document.getElementById('solicitudDias').textContent = `${diasSolicitados} días`;
    
    document.getElementById('solicitudMotivo').textContent = datosSolicitud.motivo || '---';
    document.getElementById('solicitudObservaciones').textContent = datosSolicitud.observaciones || 'Sin observaciones adicionales';
    
    // ===== ARCHIVO ADJUNTO =====
    const docLink = document.getElementById('documentoLink');
    const sinDocumento = document.getElementById('sinDocumento');
    
    if (datosSolicitud.archivo_url && datosSolicitud.archivo_url !== '') {
        // Construir URL completa para el archivo
        const archivoUrl = `http://localhost/Integrador/jefa-servicios-escolares/${datosSolicitud.archivo_url}`;
        docLink.href = archivoUrl;
        docLink.textContent = `📄 ${datosSolicitud.archivo_url.split('/').pop()}`;
        docLink.style.display = 'inline';
        if (sinDocumento) sinDocumento.style.display = 'none';
    } else {
        if (docLink) docLink.style.display = 'none';
        if (sinDocumento) sinDocumento.style.display = 'inline';
    }
    
    // Configurar días autorizados
    const inputDias = document.getElementById('diasAutorizados');
    if (inputDias) {
        inputDias.value = diasSolicitados;
        inputDias.max = getMaxDiasPorTipo(datosSolicitud.tipo_permiso);
    }
    
    // Mostrar/ocultar según estado
    const zonaPendiente = document.getElementById('zonaPendiente');
    const zonaAprobado = document.getElementById('zonaAprobado');
    const zonaRechazado = document.getElementById('zonaRechazado');
    const aprobarBtn = document.getElementById('aprobarBtn');
    const rechazarBtn = document.getElementById('rechazarBtn');
    const cancelarBtn = document.getElementById('cancelarBtn');
    
    if (zonaPendiente) zonaPendiente.style.display = 'none';
    if (zonaAprobado) zonaAprobado.style.display = 'none';
    if (zonaRechazado) zonaRechazado.style.display = 'none';
    if (aprobarBtn) aprobarBtn.style.display = 'none';
    if (rechazarBtn) rechazarBtn.style.display = 'none';
    
    if (datosSolicitud.estado === 'pendiente') {
        if (zonaPendiente) zonaPendiente.style.display = 'block';
        if (aprobarBtn) aprobarBtn.style.display = 'inline-block';
        if (rechazarBtn) rechazarBtn.style.display = 'inline-block';
        if (cancelarBtn) cancelarBtn.style.display = 'inline-block';
        
    } else if (datosSolicitud.estado === 'aprobado') {
        if (zonaAprobado) zonaAprobado.style.display = 'block';
        if (cancelarBtn) cancelarBtn.style.display = 'inline-block';
        
        document.getElementById('fechaResolucion').textContent = formatearFecha(datosSolicitud.fecha_resolucion);
        document.getElementById('diasResueltos').innerHTML = `<strong>📅 Días autorizados:</strong> ${datosSolicitud.dias_autorizados || 'No especificado'}`;
        document.getElementById('folioResuelto').innerHTML = `<strong>📄 Folio:</strong> ${datosSolicitud.folio || 'No generado'}`;
        
        if (datosSolicitud.observaciones) {
            document.getElementById('comentarioResuelto').innerHTML = `<strong>💬 Comentario oficial:</strong><br>${datosSolicitud.observaciones}`;
        } else {
            document.getElementById('comentarioResuelto').innerHTML = '<em>Sin comentarios</em>';
        }
        
        const generarBtn = document.getElementById('generarPermisoBtn');
        if (generarBtn) {
            const nuevoBtn = generarBtn.cloneNode(true);
            generarBtn.parentNode.replaceChild(nuevoBtn, generarBtn);
            nuevoBtn.addEventListener('click', () => {
                window.location.href = `generar-permiso.html?id=${datosSolicitud.id_permiso}`;
            });
        }
        
    } else if (datosSolicitud.estado === 'rechazado') {
        if (zonaRechazado) zonaRechazado.style.display = 'block';
        if (cancelarBtn) cancelarBtn.style.display = 'inline-block';
        
        document.getElementById('fechaRechazo').textContent = formatearFecha(datosSolicitud.fecha_resolucion);
        
        if (datosSolicitud.observaciones) {
            document.getElementById('comentarioRechazo').innerHTML = `<strong>💬 Motivo del rechazo:</strong><br>${datosSolicitud.observaciones}`;
        } else {
            document.getElementById('comentarioRechazo').innerHTML = '<em>No se proporcionó motivo</em>';
        }
    }
}

function getMaxDiasPorTipo(tipo) {
    const limites = {
        'licencia_medica': 15,
        'licencia_media': 15,
        'permiso_particular': 3,
        'capacitacion': 5,
        'comision_oficial': 10
    };
    return limites[tipo] || 5;
}

function configurarMaximoDias() {
    if (!datosSolicitud) return;
    const maxDias = getMaxDiasPorTipo(datosSolicitud.tipo_permiso);
    const inputDias = document.getElementById('diasAutorizados');
    if (inputDias) {
        inputDias.max = maxDias;
        const maxSpan = document.querySelector('.dias-max');
        if (maxSpan) maxSpan.textContent = `(máx: ${maxDias} días)`;
    }
}

function deshabilitarBotones() {
    const aprobarBtn = document.getElementById('aprobarBtn');
    const rechazarBtn = document.getElementById('rechazarBtn');
    const inputDias = document.getElementById('diasAutorizados');
    const generarBtn = document.getElementById('generarPermisoBtn');
    
    if (aprobarBtn) aprobarBtn.disabled = true;
    if (rechazarBtn) rechazarBtn.disabled = true;
    if (inputDias) inputDias.disabled = true;
    if (generarBtn) {
        generarBtn.disabled = false; // El botón de generar permiso se mantiene activo
    }
}

function inicializarEventos() {
    document.getElementById('closeModalBtn')?.addEventListener('click', cerrarModal);
    document.getElementById('cancelarBtn')?.addEventListener('click', cerrarModal);
    document.getElementById('volverBtn')?.addEventListener('click', cerrarModal);
    
    document.getElementById('modalOverlay')?.addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalOverlay')) {
            cerrarModal();
        }
    });
    
    document.getElementById('aprobarBtn')?.addEventListener('click', confirmarAprobacion);
    document.getElementById('rechazarBtn')?.addEventListener('click', confirmarRechazo);
}

function confirmarAprobacion() {
    const dias = document.getElementById('diasAutorizados')?.value || 1;
    const comentario = document.getElementById('comentarioOficial')?.value || '';
    
    if (confirm(`¿Aprobar esta solicitud con ${dias} día(s) autorizado(s)?`)) {
        aprobarSolicitud(dias, comentario);
    }
}

function confirmarRechazo() {
    const comentario = document.getElementById('comentarioOficial')?.value || '';
    
    if (confirm('¿Rechazar esta solicitud?')) {
        rechazarSolicitud(comentario);
    }
}

async function aprobarSolicitud(dias, comentario) {
    try {
        mostrarLoading(true);
        
        const respuesta = await fetch(`${API_URL}/permisos/aprobar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: parseInt(solicitudId),
                dias_autorizados: parseInt(dias),
                observaciones: comentario || 'Permiso aprobado',
                id_jefa_servicios: ID_JEFA_SERVICIOS
            })
        });
        
        const data = await respuesta.json();
        
        if (!respuesta.ok || data.error) {
            throw new Error(data.error || 'Error al aprobar');
        }
        
        mostrarNotificacion(`✅ Solicitud aprobada. Folio: ${data.folio}`, 'success');
        
        sessionStorage.setItem('permisoAprobado', JSON.stringify(data.permiso));
        
        setTimeout(() => {
            window.location.href = `generar-permiso.html?id=${solicitudId}`;
        }, 1500);
        
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion(error.message || 'Error al aprobar la solicitud', 'error');
    } finally {
        mostrarLoading(false);
    }
}

async function rechazarSolicitud(comentario) {
    try {
        mostrarLoading(true);
        
        const respuesta = await fetch(`${API_URL}/permisos/rechazar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: parseInt(solicitudId),
                observaciones: comentario || 'Solicitud rechazada',
                id_jefa_servicios: ID_JEFA_SERVICIOS
            })
        });
        
        const data = await respuesta.json();
        
        if (!respuesta.ok || data.error) {
            throw new Error(data.error || 'Error al rechazar');
        }
        
        mostrarNotificacion('❌ Solicitud rechazada', 'success');
        
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 1500);
        
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion(error.message || 'Error al rechazar la solicitud', 'error');
    } finally {
        mostrarLoading(false);
    }
}

function cerrarModal() {
    window.location.href = 'index.html';
}

function mostrarLoading(mostrar) {
    const overlay = document.getElementById('modalOverlay');
    if (!overlay) return;
    
    let loader = document.querySelector('.modal-loader');
    if (mostrar) {
        if (!loader) {
            loader = document.createElement('div');
            loader.className = 'modal-loader';
            loader.innerHTML = '<div class="loader"></div><p>Procesando...</p>';
            overlay.style.position = 'relative';
            overlay.appendChild(loader);
        }
    } else {
        if (loader) loader.remove();
    }
}

function formatearFecha(fecha) {
    if (!fecha) return '---';
    const f = new Date(fecha);
    return `${f.getDate().toString().padStart(2, '0')}/${(f.getMonth() + 1).toString().padStart(2, '0')}/${f.getFullYear()}`;
}

function mostrarNotificacion(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${tipo}`;
    toast.textContent = mensaje;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${tipo === 'success' ? '#27ae60' : tipo === 'error' ? '#e74c3c' : '#4E232E'};
        color: white;
        padding: 0.8rem 1.2rem;
        border-radius: 8px;
        z-index: 2000;
        animation: fadeInOut 3s ease;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}