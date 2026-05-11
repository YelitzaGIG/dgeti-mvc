// revisar.js - CONECTADO A MYSQL REAL

const urlParams = new URLSearchParams(window.location.search);
const solicitudId = urlParams.get('id');

let solicitudActual = null;

document.addEventListener('DOMContentLoaded', async () => {
    await cargarDatosEnModal();
    inicializarEventos();
});

// 🔥 CARGAR DATOS DESDE MYSQL (YA SIN DATOS FAKE)
async function cargarDatosEnModal() {
    try {

        if (!solicitudId) {
            mostrarNotificacion('❌ ID no encontrado', 'error');
            return;
        }

        solicitudActual = await API.getSolicitudById(solicitudId);

        if (!solicitudActual) {
            mostrarNotificacion('❌ No se encontró la solicitud', 'error');
            return;
        }

        console.log("DATOS DESDE MYSQL:", solicitudActual);

        // 🧠 LLENAR DATOS
        document.getElementById('solicitudId').textContent = solicitudActual.id;
        document.getElementById('alumnoNombre').textContent = solicitudActual.alumno.nombre;
        document.getElementById('alumnoMatricula').textContent = solicitudActual.alumno.matricula;
        document.getElementById('alumnoGrupo').textContent = solicitudActual.alumno.grupo;
        document.getElementById('alumnoTutor').textContent = solicitudActual.alumno.tutor || 'No especificado';
        document.getElementById('alumnoEmail').textContent = solicitudActual.alumno.email || '-';

        document.getElementById('solicitudTipo').textContent = solicitudActual.tipo || '-';
        document.getElementById('solicitudFecha').textContent = formatearFechaLarga(solicitudActual.fecha_inicio);

        const inicioFormateado = formatearFechaLarga(solicitudActual.fecha_inicio);
        const finFormateado = formatearFechaLarga(solicitudActual.fecha_fin);

        document.getElementById('solicitudPeriodo').textContent = `${inicioFormateado} al ${finFormateado}`;
        document.getElementById('solicitudDias').textContent = `${solicitudActual.dias} días`;
        document.getElementById('solicitudMotivo').textContent = solicitudActual.descripcion;

        // documento opcional
        const docLink = document.getElementById('documentoLink');
        if (docLink) {
            docLink.textContent = '📄 Sin documento';
        }

        // input días
        const inputDias = document.getElementById('diasAutorizados');
        if (inputDias) {
            inputDias.value = solicitudActual.dias;
        }

    } catch (error) {
        console.error('Error cargando datos:', error);
        mostrarNotificacion('❌ Error al cargar datos desde MySQL', 'error');
    }
}

// 🎯 EVENTOS
function inicializarEventos() {

    document.getElementById('closeModalBtn')?.addEventListener('click', cerrarModal);
    document.getElementById('cancelarBtn')?.addEventListener('click', cerrarModal);

    document.getElementById('modalOverlay')?.addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalOverlay')) cerrarModal();
    });

    document.getElementById('aprobarBtn')?.addEventListener('click', async () => {
        const dias = document.getElementById('diasAutorizados').value;
        const comentario = document.getElementById('comentarioOficial').value;
        await aprobarSolicitud(dias, comentario);
    });

    document.getElementById('rechazarBtn')?.addEventListener('click', async () => {
        const comentario = document.getElementById('comentarioOficial').value;
        await rechazarSolicitud(comentario);
    });
}

// ❌ CERRAR
function cerrarModal() {
    window.location.href = 'index.html';
}

// ✅ APROBAR (MYSQL REAL)
async function aprobarSolicitud(dias, comentario) {
    try {

        const resultado = await API.aprobarSolicitud(solicitudActual.id, dias, comentario);

        console.log("APROBADO:", resultado);

        const datosAprobacion = {
            id: solicitudActual.id,
            alumno: solicitudActual.alumno,
            tipo: solicitudActual.tipo,
            periodoAusencia: {
                inicio: solicitudActual.fecha_inicio,
                fin: solicitudActual.fecha_fin
            },
            diasAutorizados: dias,
            comentario: comentario,
            folio: resultado.folio || "SIN-FOLIO",
            fechaAprobacion: new Date().toISOString()
        };

        sessionStorage.setItem('justificanteAprobado', JSON.stringify(datosAprobacion));

        mostrarNotificacion(`✅ Justificante aprobado`, 'success');

        setTimeout(() => {
            window.location.href = 'generar-permiso.html';
        }, 1000);

    } catch (error) {
        console.error('Error al aprobar:', error);
        mostrarNotificacion('❌ Error al aprobar', 'error');
    }
}

// ❌ RECHAZAR (MYSQL REAL)
async function rechazarSolicitud(comentario) {
    try {

        await API.rechazarSolicitud(solicitudActual.id, comentario);

        mostrarNotificacion(`❌ Justificante rechazado`, 'error');

        setTimeout(() => {
            window.location.href = 'index.html';
        }, 1500);

    } catch (error) {
        console.error('Error al rechazar:', error);
        mostrarNotificacion('❌ Error al rechazar', 'error');
    }
}