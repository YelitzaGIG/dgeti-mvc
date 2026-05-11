// historial.js - Conectado a la API real

const API_URL = 'http://localhost/Integrador/jefa-servicios-escolares/backend/api';

let datosPermisos = [];
let paginaActual = 1;
const registrosPorPagina = 10;

document.addEventListener('DOMContentLoaded', () => {
    inicializarEventos();
    cargarPermisos();
});

async function cargarPermisos() {
    try {
        mostrarLoading(true);
        
        const search = document.getElementById('searchInput')?.value || '';
        const fechaDesde = document.getElementById('fechaDesde')?.value || '';
        const fechaHasta = document.getElementById('fechaHasta')?.value || '';
        const rol = document.getElementById('rolFiltro')?.value || 'todos';
        
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (fechaDesde) params.append('fecha_desde', fechaDesde);
        if (fechaHasta) params.append('fecha_hasta', fechaHasta);
        if (rol && rol !== 'todos') params.append('rol', rol);
        
        const url = `${API_URL}/historial/listar.php?${params.toString()}`;
        const respuesta = await fetch(url);
        
        if (!respuesta.ok) throw new Error('Error al cargar datos');
        
        datosPermisos = await respuesta.json();
        
        actualizarResumen();
        renderizarTabla();
        renderizarPaginacion();
        
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al cargar el historial', 'error');
        document.getElementById('historialBody').innerHTML = `<tr><td colspan="9" class="empty-state">❌ Error al cargar datos</td></tr>`;
    } finally {
        mostrarLoading(false);
    }
}

function actualizarResumen() {
    const total = datosPermisos.length;
    const hoy = new Date();
    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    const inicioSemana = new Date(hoy);
    inicioSemana.setDate(hoy.getDate() - hoy.getDay());
    
    const esteMes = datosPermisos.filter(p => new Date(p.fecha_resolucion) >= inicioMes).length;
    const estaSemana = datosPermisos.filter(p => new Date(p.fecha_resolucion) >= inicioSemana).length;
    const docentes = datosPermisos.filter(p => p.tipo_personal === 'docente').length;
    const administrativos = datosPermisos.filter(p => p.tipo_personal === 'administrativo').length;
    
    document.getElementById('totalPermisos').textContent = total;
    document.getElementById('permisosMes').textContent = esteMes;
    document.getElementById('permisosSemana').textContent = estaSemana;
    document.getElementById('permisosDocentes').textContent = docentes;
    document.getElementById('permisosAdmin').textContent = administrativos;
}

function renderizarTabla() {
    const inicio = (paginaActual - 1) * registrosPorPagina;
    const fin = inicio + registrosPorPagina;
    const datosPagina = datosPermisos.slice(inicio, fin);
    
    const tbody = document.getElementById('historialBody');
    
    if (datosPagina.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="empty-state">📭 No hay permisos firmados registrados</td></tr>`;
        return;
    }
    
    tbody.innerHTML = datosPagina.map(p => `
        <tr>
            <td><a href="#" class="folio-link" data-id="${p.id_permiso}">${p.folio || '---'}</a></td>
            <td>${formatearFecha(p.fecha_resolucion)}</td>
            <td>${p.nombre} ${p.apellido || ''}</td>
            <td>${p.tipo_personal === 'docente' ? '👩‍🏫 Docente' : '📋 Administrativo'}</td>
            <td>${p.departamento || 'No especificado'}</td>
            <td>${formatearFecha(p.fecha_inicio)} - ${formatearFecha(p.fecha_fin)}</td>
            <td>${p.dias_autorizados || '—'}</td>
            <td><a href="#" class="documento-link ver-documento" data-doc="${p.archivo_url || ''}">📄 Ver PDF</a></td>
            <td class="acciones-historial">
                <button class="btn-icon ver" data-id="${p.id_permiso}" title="Ver detalle">👁️</button>
                <button class="btn-icon descargar" data-id="${p.id_permiso}" title="Descargar PDF">📥</button>
                <button class="btn-icon email" data-id="${p.id_permiso}" title="Reenviar por correo">✉️</button>
            </td>
         `
    ).join('');
    
    // Eventos
    document.querySelectorAll('.btn-icon.ver').forEach(btn => {
        btn.addEventListener('click', () => verDetalle(btn.dataset.id));
    });
    document.querySelectorAll('.btn-icon.descargar').forEach(btn => {
        btn.addEventListener('click', () => descargarPermiso(btn.dataset.id));
    });
    document.querySelectorAll('.btn-icon.email').forEach(btn => {
        btn.addEventListener('click', () => reenviarPorCorreo(btn.dataset.id));
    });
    document.querySelectorAll('.folio-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            verDetalle(link.dataset.id);
        });
    });
}

function renderizarPaginacion() {
    const totalPaginas = Math.ceil(datosPermisos.length / registrosPorPagina);
    const paginacionDiv = document.getElementById('paginacion');
    
    if (totalPaginas <= 1) {
        paginacionDiv.innerHTML = '';
        return;
    }
    
    let html = '';
    for (let i = 1; i <= totalPaginas; i++) {
        html += `<button class="pag-btn ${i === paginaActual ? 'active' : ''}" data-pagina="${i}">${i}</button>`;
    }
    
    paginacionDiv.innerHTML = html;
    
    document.querySelectorAll('.pag-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            paginaActual = parseInt(btn.dataset.pagina);
            renderizarTabla();
        });
    });
}

function inicializarEventos() {
    document.getElementById('volverBtn')?.addEventListener('click', () => {
        window.location.href = 'index.html';
    });
    
    document.getElementById('logoutBtn')?.addEventListener('click', () => {
        mostrarNotificacion('Sesión cerrada');
        // window.location.href = 'login.html';
    });
    
    document.getElementById('buscarBtn')?.addEventListener('click', () => {
        paginaActual = 1;
        cargarPermisos();
    });
    
    document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            paginaActual = 1;
            cargarPermisos();
        }
    });
    
    document.getElementById('exportarBtn')?.addEventListener('click', exportarAExcel);
}

function verDetalle(id) {
    window.location.href = `revisar-solicitud.html?id=${id}`;
}

function descargarPermiso(id) {
    const permiso = datosPermisos.find(p => p.id_permiso == id);
    if (permiso) {
        mostrarNotificacion(`📥 Descargando ${permiso.folio || 'permiso'}...`, 'success');
        // Aquí se integrará la descarga real del PDF
    }
}

function reenviarPorCorreo(id) {
    const permiso = datosPermisos.find(p => p.id_permiso == id);
    if (permiso) {
        mostrarNotificacion(`✉️ Reenviando a ${permiso.correo}...`, 'info');
        setTimeout(() => {
            mostrarNotificacion(`✅ Enviado a ${permiso.correo}`, 'success');
        }, 1500);
    }
}

function exportarAExcel() {
    mostrarNotificacion('📊 Generando reporte...', 'info');
    
    const headers = ['Folio', 'Fecha Emisión', 'Solicitante', 'Rol', 'Departamento', 'Fecha Inicio', 'Fecha Fin', 'Días'];
    const rows = datosPermisos.map(p => [
        p.folio || '---',
        formatearFecha(p.fecha_resolucion),
        `${p.nombre} ${p.apellido || ''}`,
        p.tipo_personal === 'docente' ? 'Docente' : 'Administrativo',
        p.departamento || 'No especificado',
        formatearFecha(p.fecha_inicio),
        formatearFecha(p.fecha_fin),
        p.dias_autorizados || '—'
    ]);
    
    const csvContent = [headers, ...rows].map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.setAttribute('download', `historial_permisos_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    setTimeout(() => mostrarNotificacion('✅ Reporte exportado', 'success'), 500);
}

function formatearFecha(fecha) {
    if (!fecha) return '---';
    const f = new Date(fecha);
    return `${f.getDate().toString().padStart(2, '0')}/${(f.getMonth() + 1).toString().padStart(2, '0')}/${f.getFullYear()}`;
}

function mostrarLoading(mostrar) {
    const loader = document.getElementById('loadingOverlay');
    if (loader) {
        loader.style.display = mostrar ? 'flex' : 'none';
    }
}

function mostrarNotificacion(mensaje, tipo) {
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