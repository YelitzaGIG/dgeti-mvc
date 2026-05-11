// generar-pdf.js - Lógica para generar el justificante oficial PDF

let datosJustificante = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarDatosAprobados();
    inicializarEventos();
});

function cargarDatosAprobados() {
    const datosGuardados = sessionStorage.getItem('justificanteAprobado');
    
    if (datosGuardados) {
        datosJustificante = JSON.parse(datosGuardados);
    } else {
        // Datos de ejemplo
        datosJustificante = {
            folio: 'JUS-2026-0124',
            fechaAprobacion: new Date().toISOString(),
            alumno: {
                nombre: 'Ana López García',
                matricula: 'MAT-2024-001',
                grupo: '4° Semestre "A"'
            },
            tipo: 'Justificante médico',
            periodoAusencia: { inicio: '2026-04-20', fin: '2026-04-22' },
            diasAutorizados: 3,
            comentario: 'Se autoriza justificante por motivos médicos.'
        };
    }
    
    // Asignar valores a los elementos del DOM
    document.getElementById('folioNumero').textContent = datosJustificante.folio || `JUS-${Date.now()}`;
    document.getElementById('fechaEmision').textContent = formatearFechaLarga(datosJustificante.fechaAprobacion || new Date());
    document.getElementById('permisoNombre').textContent = datosJustificante.alumno?.nombre || '-';
    document.getElementById('permisoMatricula').textContent = datosJustificante.alumno?.matricula || '-';
    document.getElementById('permisoGrupo').textContent = datosJustificante.alumno?.grupo || '-';
    document.getElementById('periodoInicio').textContent = formatearFechaLarga(datosJustificante.periodoAusencia?.inicio || new Date());
    document.getElementById('periodoFin').textContent = formatearFechaLarga(datosJustificante.periodoAusencia?.fin || new Date());
    document.getElementById('totalDias').textContent = datosJustificante.diasAutorizados || 0;
    document.getElementById('permisoMotivo').textContent = datosJustificante.tipo || '-';
    
    // Asignar datos al concentrado semestral
    document.getElementById('printGrupo').textContent = datosJustificante.alumno?.grupo || '-';
    document.getElementById('printDias').textContent = datosJustificante.diasAutorizados || '-';
    document.getElementById('printMotivo').textContent = datosJustificante.tipo || '-';
    document.getElementById('printFecha').textContent = formatearFecha(new Date());
    document.getElementById('printNumero').textContent = datosJustificante.folio || '-';
    document.getElementById('printModelo').textContent = datosJustificante.alumno?.matricula || '-';
    
    // Asignar datos a la solicitud
    document.getElementById('solicitudNombre').textContent = datosJustificante.alumno?.nombre || '-';
    document.getElementById('solicitudMatricula').textContent = datosJustificante.alumno?.matricula || '-';
    document.getElementById('solicitudGrupo').textContent = datosJustificante.alumno?.grupo || '-';
    document.getElementById('solicitudFecha').textContent = formatearFechaLarga(new Date());
    document.getElementById('solicitudTutor').textContent = datosJustificante.alumno?.tutor || 'No especificado';
    
    if (datosJustificante.comentario) {
        document.getElementById('comentarioOficialBox').style.display = 'block';
        document.getElementById('comentarioOficialTexto').textContent = datosJustificante.comentario;
    }
}

function inicializarEventos() {
    document.getElementById('closeModalBtn')?.addEventListener('click', volverAlDashboard);
    document.getElementById('volverBtn')?.addEventListener('click', volverAlDashboard);
    document.getElementById('imprimirBtn')?.addEventListener('click', imprimirJustificante);
    document.getElementById('descargarBtn')?.addEventListener('click', descargarPDF);
    document.getElementById('enviarBtn')?.addEventListener('click', enviarPorCorreo);
}

function volverAlDashboard() {
    window.location.href = 'index.html';
}

function imprimirJustificante() {
    window.print();
}

function descargarPDF() {
    const elemento = document.getElementById('documentoParaImprimir');
    
    const opciones = {
        margin: [0.5, 0.5, 0.5, 0.5],
        filename: `Justificante_${datosJustificante.folio || 'oficial'}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, letterRendering: true },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    
    mostrarNotificacion('Generando PDF, espere...', 'info');
    html2pdf().set(opciones).from(elemento).save();
    setTimeout(() => mostrarNotificacion('✅ PDF generado', 'success'), 1500);
}

function enviarPorCorreo() {
    const email = datosJustificante.alumno?.email || 'tutor@example.com';
    mostrarNotificacion(`✉️ Enviando a ${email}...`, 'info');
    setTimeout(() => mostrarNotificacion(`✅ Enviado a ${email}`, 'success'), 1500);
}