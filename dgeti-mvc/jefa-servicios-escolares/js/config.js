/*
// js/config.js
const CONFIG = {
    API_URL: 'http://localhost:5000/api',
    TOKEN_KEY: 'token_permisos'
};

// Guardar token en localStorage
function guardarToken(token) {
    localStorage.setItem(CONFIG.TOKEN_KEY, token);
}

// Obtener token
function obtenerToken() {
    return localStorage.getItem(CONFIG.TOKEN_KEY);
}

// Función para peticiones autenticadas
async function peticionAutenticada(url, options = {}) {
    const token = obtenerToken();
    
    const headers = {
        'Content-Type': 'application/json',
        ...options.headers
    };
    
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    
    const respuesta = await fetch(`${CONFIG.API_URL}${url}`, {
        ...options,
        headers
    });
    
    if (respuesta.status === 401) {
        localStorage.removeItem(CONFIG.TOKEN_KEY);
        mostrarNotificacion('Sesión expirada. Inicia sesión nuevamente.', 'error');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 1500);
    }
    
    return respuesta;
}*/
/*
async function peticionAutenticada(url, options = {}) {
    // Por ahora, omitimos la verificación de token para pruebas
    const respuesta = await fetch(`${CONFIG.API_URL}${url}`, {
        ...options,
        headers: { 'Content-Type': 'application/json' }
    });
    return respuesta;
}//

// Mostrar notificación
function mostrarNotificacion(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${tipo}`;
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Formatear fecha
function formatearFecha(fecha) {
    if (!fecha) return '—';
    const f = new Date(fecha);
    return `${f.getDate().toString().padStart(2, '0')}/${(f.getMonth() + 1).toString().padStart(2, '0')}/${f.getFullYear()}`;
}

// Formatear fecha para input date (YYYY-MM-DD)
function formatearFechaInput(fecha) {
    if (!fecha) return '';
    const f = new Date(fecha);
    return f.toISOString().split('T')[0];
}
*/

// js/config.js
const CONFIG = {
    API_URL: 'http://localhost/Integrador/jefa-servicios-escolares/backend/api',
    TOKEN_KEY: 'token_permisos'
};

// Guardar token en localStorage
function guardarToken(token) {
    localStorage.setItem(CONFIG.TOKEN_KEY, token);
}

// Obtener token
function obtenerToken() {
    return localStorage.getItem(CONFIG.TOKEN_KEY);
}

// Función para peticiones autenticadas (VERSIÓN CORREGIDA)
async function peticionAutenticada(url, options = {}) {
    // Por ahora, omitimos la verificación de token para pruebas
    const respuesta = await fetch(`${CONFIG.API_URL}${url}`, {
        ...options,
        headers: { 
            'Content-Type': 'application/json'
        }
    });
    return respuesta;
}

// Mostrar notificación
function mostrarNotificacion(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${tipo}`;
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Formatear fecha
function formatearFecha(fecha) {
    if (!fecha) return '—';
    const f = new Date(fecha);
    return `${f.getDate().toString().padStart(2, '0')}/${(f.getMonth() + 1).toString().padStart(2, '0')}/${f.getFullYear()}`;
}

// Formatear fecha para input date (YYYY-MM-DD)
function formatearFechaInput(fecha) {
    if (!fecha) return '';
    const f = new Date(fecha);
    return f.toISOString().split('T')[0];
}