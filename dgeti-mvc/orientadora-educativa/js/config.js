// Configuración global de la aplicación

const CONFIG = {
    // URL del backend (cambiar cuando tengas el servidor)
    API_URL: 'http://localhost:5000/api',
    
    // Datos de la jefa (simulados, después vendrán del login)
    JEFA_DATA: {
        id: 1,
        nombre: 'María González',
        rol: 'jefa_servicios',
        departamento: 'Servicios Escolares'
    },
    
    // Configuración de tipos de justificante
    TIPOS_JUSTIFICANTE: [
        { id: 'medica', nombre: 'Justificante médico', maxDias: 15 },
        { id: 'particular', nombre: 'Permiso particular', maxDias: 3 },
        { id: 'comision', nombre: 'Actividad escolar', maxDias: 5 },
        { id: 'deporte', nombre: 'Representación deportiva', maxDias: 10 }
    ]
};

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    if (tipo === 'success') toast.classList.add('success');
    if (tipo === 'error') toast.classList.add('error');
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Función para formatear fecha
function formatearFecha(fecha) {
    const f = new Date(fecha);
    return `${f.getDate().toString().padStart(2,'0')}/${(f.getMonth()+1).toString().padStart(2,'0')}/${f.getFullYear()}`;
}

// Función para formatear fecha larga (ej: 16 de abril de 2026)
function formatearFechaLarga(fecha) {
    const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    const f = new Date(fecha);
    return `${f.getDate()} de ${meses[f.getMonth()]} de ${f.getFullYear()}`;
}