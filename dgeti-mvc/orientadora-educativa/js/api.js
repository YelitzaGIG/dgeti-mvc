// =============================================
// API.JS - CONEXIÓN A MYSQL CON FALLBACK A LOCALSTORAGE
// CBTIS 199 - Paleta institucional
// =============================================

// Configuración del API
const API_URL = '/orientadora-educativa/api/';
const USE_API = false; // Cambiar a true cuando tengas el backend

// API para conexión a MySQL
const API = {
    async getSolicitudes() {
        if (!USE_API) {
            // Usar localStorage como fallback
            const stored = localStorage.getItem('sistemaJustificantes');
            return stored ? JSON.parse(stored) : [];
        }
        
        try {
            const response = await fetch(API_URL + 'get_solicitudes.php');
            if (!response.ok) throw new Error('HTTP error ' + response.status);
            const data = await response.json();
            return Array.isArray(data) ? data : [];
        } catch (error) {
            console.error('Error en getSolicitudes:', error);
            const stored = localStorage.getItem('sistemaJustificantes');
            return stored ? JSON.parse(stored) : [];
        }
    },

    async getSolicitudById(id) {
        if (!USE_API) {
            const stored = localStorage.getItem('sistemaJustificantes');
            const solicitudes = stored ? JSON.parse(stored) : [];
            return solicitudes.find(s => s.id == id) || null;
        }
        
        try {
            const response = await fetch(API_URL + `get_solicitud.php?id=${id}`);
            if (!response.ok) throw new Error('HTTP error ' + response.status);
            return await response.json();
        } catch (error) {
            console.error('Error en getSolicitudById:', error);
            const stored = localStorage.getItem('sistemaJustificantes');
            const solicitudes = stored ? JSON.parse(stored) : [];
            return solicitudes.find(s => s.id == id) || null;
        }
    },

    async getEstadisticas() {
        if (!USE_API) {
            const stored = localStorage.getItem('sistemaJustificantes');
            const solicitudes = stored ? JSON.parse(stored) : [];
            return {
                pendientes: solicitudes.filter(s => s.estado === 'pendiente').length,
                aprobados: solicitudes.filter(s => s.estado === 'aprobado').length,
                rechazados: solicitudes.filter(s => s.estado === 'rechazado').length,
                total: solicitudes.length
            };
        }
        
        try {
            const response = await fetch(API_URL + 'get_estadisticas.php');
            if (!response.ok) throw new Error('HTTP error ' + response.status);
            return await response.json();
        } catch (error) {
            console.error('Error en getEstadisticas:', error);
            return { pendientes: 0, aprobados: 0, rechazados: 0, total: 0 };
        }
    },

    async aprobarSolicitud(id, dias, comentario) {
        if (!USE_API) {
            const stored = localStorage.getItem('sistemaJustificantes');
            let solicitudes = stored ? JSON.parse(stored) : [];
            const index = solicitudes.findIndex(s => s.id == id);
            if (index !== -1) {
                solicitudes[index].estado = 'aprobado';
                solicitudes[index].diasAutorizados = dias;
                solicitudes[index].comentarioAprobacion = comentario;
                localStorage.setItem('sistemaJustificantes', JSON.stringify(solicitudes));
                return { success: true, folio: solicitudes[index].folio };
            }
            return { success: false, error: 'No encontrado' };
        }
        
        try {
            const response = await fetch(API_URL + 'aprobar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, dias, comentario })
            });
            return await response.json();
        } catch (error) {
            console.error('Error en aprobarSolicitud:', error);
            return { success: false, error: error.message };
        }
    },

    async rechazarSolicitud(id, comentario) {
        if (!USE_API) {
            const stored = localStorage.getItem('sistemaJustificantes');
            let solicitudes = stored ? JSON.parse(stored) : [];
            const index = solicitudes.findIndex(s => s.id == id);
            if (index !== -1) {
                solicitudes[index].estado = 'rechazado';
                solicitudes[index].comentarioRechazo = comentario;
                localStorage.setItem('sistemaJustificantes', JSON.stringify(solicitudes));
                return { success: true };
            }
            return { success: false, error: 'No encontrado' };
        }
        
        try {
            const response = await fetch(API_URL + 'rechazar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, comentario })
            });
            return await response.json();
        } catch (error) {
            console.error('Error en rechazarSolicitud:', error);
            return { success: false, error: error.message };
        }
    },

    async guardarJustificante(datos) {
        if (!USE_API) {
            const stored = localStorage.getItem('sistemaJustificantes');
            let solicitudes = stored ? JSON.parse(stored) : [];
            const newId = Date.now();
            const nuevo = {
                id: newId,
                folio: `JUS-${newId.toString().slice(-6)}`,
                ...datos,
                estado: 'pendiente',
                fechaRegistro: new Date().toISOString().slice(0,10)
            };
            solicitudes.push(nuevo);
            localStorage.setItem('sistemaJustificantes', JSON.stringify(solicitudes));
            return { success: true, id: newId };
        }
        
        try {
            const response = await fetch(API_URL + 'guardar_justificante.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            return await response.json();
        } catch (error) {
            console.error('Error en guardarJustificante:', error);
            return { success: false, error: error.message };
        }
    }
};

// Configuración global
const CONFIG = {
    API_URL: USE_API ? API_URL : null,
    JEFA_DATA: {
        id: 1,
        nombre: 'Mtra. Fabiola Guadalupe Gamboa Pérez',
        rol: 'Jefa de Servicios Escolares',
        departamento: 'Servicios Escolares'
    },
    TIPOS_JUSTIFICANTE: [
        { id: 'medica', nombre: 'Justificante médico', maxDias: 15 },
        { id: 'particular', nombre: 'Permiso particular', maxDias: 3 },
        { id: 'comision', nombre: 'Actividad escolar', maxDias: 5 },
        { id: 'deporte', nombre: 'Representación deportiva', maxDias: 10 }
    ]
};

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo = 'info') {
    let toast = document.querySelector('.toast-notification');
    if (toast) toast.remove();
    
    toast = document.createElement('div');
    toast.className = 'toast-notification';
    if (tipo === 'success') toast.classList.add('success');
    if (tipo === 'error') toast.classList.add('error');
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Función para formatear fecha
function formatearFecha(fecha) {
    if (!fecha) return '-';
    const f = new Date(fecha);
    return `${f.getDate().toString().padStart(2,'0')}/${(f.getMonth()+1).toString().padStart(2,'0')}/${f.getFullYear()}`;
}

// Función para formatear fecha larga
function formatearFechaLarga(fecha) {
    if (!fecha) return '-';
    const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    const f = new Date(fecha);
    return `${f.getDate()} de ${meses[f.getMonth()]} de ${f.getFullYear()}`;
}