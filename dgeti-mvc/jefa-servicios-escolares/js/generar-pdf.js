// generar-pdf.js - Generar permiso oficial

const urlParams = new URLSearchParams(window.location.search);
const permisoId = urlParams.get('id');

let datosPermiso = null;

document.addEventListener('DOMContentLoaded', async () => {
    if (!permisoId) {
        // Intentar recuperar de sessionStorage
        const guardado = sessionStorage.getItem('permisoAprobado');
        if (guardado) {
            datosPermiso = JSON.parse(guardado);
            cargarDatosEnPermiso();
        } else {
            mostrarNotificacion('No hay datos del permiso', 'error');
            setTimeout(() => window.location.href = 'index.html', 1500);
        }
    } else {
        await cargarPermisoDesdeAPI();
    }
    
    inicializarEventos();
});

async function cargarPermisoDesdeAPI() {
    try {
        mostrarLoading(true);
        
        const respuesta = await peticionAutenticada(`/permisos/${permisoId}`);
        
        if (!respuesta.ok) {
            throw new Error('Error al cargar el permiso');
        }
        
        datosPermiso = await respuesta.json();
        cargarDatosEnPermiso();
        
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al cargar el permiso', 'error');
        setTimeout(() => window.location.href = 'index.html', 1500);
    } finally {
        mostrarLoading(false);
    }
}

function cargarDatosEnPermiso() {
    if (!datosPermiso) return;
    
    // Folio
    document.getElementById('folioNumero').textContent = datosPermiso.folio || `PER-${new Date().getFullYear()}-${String(datosPermiso.id_permiso).padStart(4, '0')}`;
    
    // Fecha de emisión
    const hoy = new Date();
    document.getElementById('fechaEmision').textContent = hoy.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Datos del solicitante
    const nombreCompleto = `${datosPermiso.nombre || ''} ${datosPermiso.apellido || ''}`.trim();
    document.getElementById('permisoNombre').textContent = nombreCompleto || '---';
    document.getElementById('permisoRol').textContent = datosPermiso.tipo_personal === 'docente' ? 'Docente' : 'Administrativo';
    document.getElementById('permisoDepto').textContent = datosPermiso.departamento || 'No especificado';
    
    // Período
    document.getElementById('periodoInicio').textContent = formatearFechaTexto(datosPermiso.fecha_inicio);
    document.getElementById('periodoFin').textContent = formatearFechaTexto(datosPermiso.fecha_fin);
    
    // Días autorizados
    const dias = datosPermiso.dias_autorizados || calcularDias(datosPermiso.fecha_inicio, datosPermiso.fecha_fin);
    document.getElementById('totalDias').textContent = dias;
    
    // Motivo
    const motivoMap = {
        'licencia_medica': 'Licencia médica',
        'permiso_particular': 'Permiso particular',
        'capacitacion': 'Capacitación',
        'comision_oficial': 'Comisión oficial'
    };
    const motivoTexto = motivoMap[datosPermiso.tipo_permiso] || datosPermiso.tipo_permiso;
    document.getElementById('permisoMotivo').textContent = `${motivoTexto}: ${datosPermiso.motivo || 'No especificado'}`;
    
    // Comentario oficial
    if (datosPermiso.observaciones) {
        const comentarioBox = document.getElementById('comentarioOficialBox');
        comentarioBox.style.display = 'block';
        document.getElementById('comentarioOficialTexto').textContent = datosPermiso.observaciones;
    }
}

function formatearFechaTexto(fecha) {
    if (!fecha) return '---';
    const f = new Date(fecha);
    return f.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function calcularDias(inicio, fin) {
    const fInicio = new Date(inicio);
    const fFin = new Date(fin);
    return Math.ceil((fFin - fInicio) / (1000 * 60 * 60 * 24)) + 1;
}

function inicializarEventos() {
    document.getElementById('closeModalBtn')?.addEventListener('click', volverAlDashboard);
    document.getElementById('volverBtn')?.addEventListener('click', volverAlDashboard);
    
    document.getElementById('modalOverlay')?.addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalOverlay')) {
            volverAlDashboard();
        }
    });
    
    document.getElementById('imprimirBtn')?.addEventListener('click', imprimirPermiso);
    document.getElementById('descargarBtn')?.addEventListener('click', descargarPDF);
    document.getElementById('enviarBtn')?.addEventListener('click', enviarPorCorreo);
}

function volverAlDashboard() {
    window.location.href = 'index.html';
}

function imprimirPermiso() {
    const elemento = document.getElementById('permisoDocumento');
    const ventanaImpresion = window.open('', '_blank');
    
    ventanaImpresion.document.write(`
        <html>
            <head>
                <title>Permiso Oficial ${document.getElementById('folioNumero').textContent}</title>
                <link rel="stylesheet" href="css/permiso-preview.css">
                <style>
                    body { padding: 2rem; margin: 0; font-family: 'Times New Roman', serif; }
                    .modal-overlay, .modal-header, .modal-footer { display: none; }
                    .permiso-body { padding: 0; }
                </style>
            </head>
            <body>
                ${elemento.outerHTML}
            </body>
        </html>
    `);
    
    ventanaImpresion.document.close();
    ventanaImpresion.print();
    ventanaImpresion.close();
}

async function descargarPDF() {
    const elemento = document.getElementById('permisoDocumento');
    const folio = document.getElementById('folioNumero').textContent;
    
    const opciones = {
        margin: [0.5, 0.5, 0.5, 0.5],
        filename: `Permiso_${folio}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, letterRendering: true },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    
    mostrarNotificacion('Generando PDF...', 'info');
    
    try {
        await html2pdf().set(opciones).from(elemento).save();
        mostrarNotificacion('✅ PDF generado correctamente', 'success');
        
        // Actualizar el backend con la URL del PDF (opcional)
        if (permisoId) {
            await peticionAutenticada(`/permisos/${permisoId}/pdf`, {
                method: 'PUT',
                body: JSON.stringify({ permiso_generado_url: `permisos/Permiso_${folio}.pdf` })
            });
        }
    } catch (error) {
        console.error('Error al generar PDF:', error);
        mostrarNotificacion('Error al generar el PDF', 'error');
    }
}

async function enviarPorCorreo() {
    const email = datosPermiso?.correo;
    
    if (!email) {
        mostrarNotificacion('No hay correo registrado para este solicitante', 'error');
        return;
    }
    
    mostrarNotificacion(`✉️ Enviando permiso a ${email}...`, 'info');
    
    try {
        // Aquí se integrará con el backend para envío real
        const respuesta = await peticionAutenticada(`/permisos/${permisoId}/enviar`, {
            method: 'POST',
            body: JSON.stringify({ email })
        });
        
        if (!respuesta.ok) throw new Error('Error al enviar');
        
        mostrarNotificacion(`✅ Permiso enviado a ${email}`, 'success');
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al enviar el correo', 'error');
    }
}

function mostrarLoading(mostrar) {
    const overlay = document.getElementById('modalOverlay');
    if (!overlay) return;
    
    if (mostrar) {
        if (!document.querySelector('.modal-loader')) {
            const loader = document.createElement('div');
            loader.className = 'modal-loader';
            loader.innerHTML = '<div class="loader"></div><p>Cargando permiso...</p>';
            overlay.appendChild(loader);
        }
    } else {
        const loader = document.querySelector('.modal-loader');
        if (loader) loader.remove();
    }
}