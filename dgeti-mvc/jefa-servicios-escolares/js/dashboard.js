// js/dashboard.js - VERSIÓN LIMPIA Y SIMPLIFICADA

const API_URL = 'http://localhost/Integrador/jefa-servicios-escolares/backend/api';

document.addEventListener('DOMContentLoaded', function() {
    iniciarEventos();
    cargarEstadisticas();
    cargarSolicitudes();
});

function iniciarEventos() {
    const buscarBtn = document.getElementById('buscarBtn');
    if (buscarBtn) buscarBtn.addEventListener('click', cargarSolicitudes);
    
    const nuevoBtn = document.getElementById('nuevoPermisoBtn');
    if (nuevoBtn) nuevoBtn.addEventListener('click', function() {
        window.location.href = 'nuevo-permiso.html';
    });
    
    const searchInput = document.getElementById('searchText');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') cargarSolicitudes();
        });
    }
    
    const vistaBtns = document.querySelectorAll('.vista-btn');
    vistaBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const vista = e.target.dataset.vista;
            aplicarVistaRapida(vista);
        });
    });
    
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            tabBtns.forEach(function(b) { b.classList.remove('active'); });
            e.target.classList.add('active');
            
            let estadoFiltro = 'todos';
            const tab = e.target.dataset.tab;
            if (tab === 'pendientes') estadoFiltro = 'pendiente';
            if (tab === 'aprobados') estadoFiltro = 'aprobado';
            if (tab === 'rechazados') estadoFiltro = 'rechazado';
            if (tab === 'firmados') estadoFiltro = 'aprobado';
            
            const estadoSelect = document.getElementById('estadoFiltro');
            if (estadoSelect) estadoSelect.value = estadoFiltro;
            cargarSolicitudes();
        });
    });
    
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            mostrarNotificacion('Sesión cerrada correctamente', 'info');
            setTimeout(function() {
                window.location.href = 'login.html';
            }, 1000);
        });
    }
    
    const configBtn = document.getElementById('irConfigBtn');
    if (configBtn) {
        configBtn.addEventListener('click', function() {
            window.location.href = 'configuracion.html';
        });
    }
}

function aplicarVistaRapida(vista) {
    const hoy = new Date();
    let fechaDesde = null;
    let fechaHasta = new Date();
    fechaHasta.setHours(23, 59, 59);
    
    if (vista === 'hoy') {
        fechaDesde = hoy;
    } else if (vista === 'semana') {
        fechaDesde = new Date();
        fechaDesde.setDate(hoy.getDate() - 7);
    } else if (vista === 'mes') {
        fechaDesde = new Date();
        fechaDesde.setMonth(hoy.getMonth() - 1);
    } else {
        return;
    }
    
    if (fechaDesde) {
        const fechaDesdeInput = document.getElementById('fechaDesde');
        const fechaHastaInput = document.getElementById('fechaHasta');
        if (fechaDesdeInput) fechaDesdeInput.value = fechaDesde.toISOString().split('T')[0];
        if (fechaHastaInput) fechaHastaInput.value = fechaHasta.toISOString().split('T')[0];
        cargarSolicitudes();
    }
}

async function cargarEstadisticas() {
    try {
        const respuesta = await fetch(API_URL + '/dashboard/estadisticas.php');
        if (!respuesta.ok) throw new Error('HTTP ' + respuesta.status);
        const stats = await respuesta.json();
        
        const pendientesSpan = document.getElementById('pendientesCount');
        const aprobadosSpan = document.getElementById('aprobadosCount');
        const rechazadosSpan = document.getElementById('rechazadosCount');
        const generadosSpan = document.getElementById('generadosCount');
        
        if (pendientesSpan) pendientesSpan.textContent = stats.pendientes || 0;
        if (aprobadosSpan) aprobadosSpan.textContent = stats.aprobados || 0;
        if (rechazadosSpan) rechazadosSpan.textContent = stats.rechazados || 0;
        if (generadosSpan) generadosSpan.textContent = stats.firmados || 0;
    } catch (error) {
        console.error('Error en estadísticas:', error);
        mostrarNotificacion('Error al cargar estadísticas', 'error');
    }
}

async function cargarSolicitudes() {
    try {
        const tbody = document.getElementById('tablaBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="loading-text">⏳ Cargando solicitudes...</td></tr>';
        }
        
        const searchText = document.getElementById('searchText')?.value || '';
        const estado = document.getElementById('estadoFiltro')?.value || 'todos';
        const fechaDesde = document.getElementById('fechaDesde')?.value || '';
        const fechaHasta = document.getElementById('fechaHasta')?.value || '';
        
        let url = API_URL + '/dashboard/solicitudes.php?';
        const params = [];
        if (searchText) params.push('search=' + encodeURIComponent(searchText));
        if (estado && estado !== 'todos') params.push('estado=' + estado);
        if (fechaDesde) params.push('fecha_desde=' + fechaDesde);
        if (fechaHasta) params.push('fecha_hasta=' + fechaHasta);
        url = url + params.join('&');
        
        console.log('Consultando URL:', url);
        const respuesta = await fetch(url);
        
        if (!respuesta.ok) throw new Error('HTTP ' + respuesta.status);
        
        const solicitudes = await respuesta.json();
        console.log('Solicitudes recibidas:', solicitudes.length);
        renderizarTabla(solicitudes);
    } catch (error) {
        console.error('Error en cargarSolicitudes:', error);
        mostrarNotificacion('Error al cargar solicitudes: ' + error.message, 'error');
        const tbody = document.getElementById('tablaBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="empty-state">❌ Error al cargar datos</td></tr>';
        }
    }
}

function renderizarTabla(solicitudes) {
    const tbody = document.getElementById('tablaBody');
    
    if (!solicitudes || solicitudes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="empty-state">📭 No hay solicitudes para mostrar</td></tr>';
        return;
    }
    
    let html = '';
    
    for (let i = 0; i < solicitudes.length; i++) {
        const fila = solicitudes[i];
        
        const fechaInicio = formatearFecha(fila.fecha_inicio);
        const fechaFin = formatearFecha(fila.fecha_fin);
        const fechaSolicitud = formatearFecha(fila.fecha_solicitud);
        const nombreCompleto = (fila.nombre || '') + ' ' + (fila.apellido || '');
        
        let tipoTexto = fila.tipo_permiso;
        if (tipoTexto === 'licencia_medica') tipoTexto = 'Licencia médica';
        if (tipoTexto === 'permiso_particular') tipoTexto = 'Permiso particular';
        if (tipoTexto === 'capacitacion') tipoTexto = 'Capacitación';
        if (tipoTexto === 'comision_oficial') tipoTexto = 'Comisión oficial';
        
        let estadoBadge = '';
        if (fila.estado === 'pendiente') estadoBadge = '<span class="badge-pendiente">⏳ Pendiente</span>';
        if (fila.estado === 'aprobado') estadoBadge = '<span class="badge-aprobado">✅ Aprobado</span>';
        if (fila.estado === 'rechazado') estadoBadge = '<span class="badge-rechazado">❌ Rechazado</span>';
        
        let comprobanteHtml = '—';
        if (fila.archivo_url && fila.archivo_url !== '') {
            const archivoUrl = 'http://localhost/Integrador/jefa-servicios-escolares/uploads/' + fila.archivo_url;
            comprobanteHtml = '<a href="' + archivoUrl + '" target="_blank" class="documento-link">📎 Ver archivo</a>';
        }
        
        html += '<tr>';
        html += '<td>' + fila.id_permiso + '</td>';
        html += '<td>' + fechaSolicitud + '</td>';
        html += '<td>' + nombreCompleto + '<br><small>' + (fila.tipo_personal === 'docente' ? '👩‍🏫 Docente' : '📋 Administrativo') + '</small></td>';
        html += '<td>' + tipoTexto + '</td>';
        html += '<td>' + fechaInicio + ' - ' + fechaFin + '</td>';
        html += '<td>' + estadoBadge + '</td>';
        html += '<td>' + comprobanteHtml + '</td>';
        html += '<td class="acciones"><button class="btn-ver" data-id="' + fila.id_permiso + '" title="Ver detalle">👁️ Ver</button></td>';
        html += '</tr>';
    }
    
    tbody.innerHTML = html;
    
    const botonesVer = document.querySelectorAll('.btn-ver');
    for (let i = 0; i < botonesVer.length; i++) {
        botonesVer[i].addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            window.location.href = 'revisar-solicitud.html?id=' + id;
        });
    }
}

function formatearFecha(fecha) {
    if (!fecha) return '---';
    const f = new Date(fecha);
    const dia = f.getDate().toString().padStart(2, '0');
    const mes = (f.getMonth() + 1).toString().padStart(2, '0');
    const anio = f.getFullYear();
    return dia + '/' + mes + '/' + anio;
}

function mostrarNotificacion(mensaje, tipo) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification ' + tipo;
    toast.textContent = mensaje;
    toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:' + (tipo === 'success' ? '#27ae60' : tipo === 'error' ? '#e74c3c' : '#4E232E') + ';color:white;padding:0.8rem 1.2rem;border-radius:8px;z-index:2000;';
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}