let justificantesData = [];
let paginaActual = 1;
const registrosPorPagina = 10;
let datosFiltrados = [];

document.addEventListener('DOMContentLoaded', async () => {
    inicializarEventos();
    await cargarDatosReales();
});

function inicializarEventos() {
    document.getElementById('volverBtn')?.addEventListener('click', () => window.location.href = 'index.html');
    document.getElementById('buscarBtn')?.addEventListener('click', () => {
        paginaActual = 1;
        aplicarFiltros();
    });
}

async function cargarDatosReales() {
    try {
        const data = await API.getSolicitudes();

        // Convertir datos de MySQL al formato de tu tabla
        justificantesData = data.map(j => ({
            id: j.id_justificante,
            folio: j.folio,
            fechaEmisionFormateada: formatearFecha(j.fecha_solicitud),
            alumno: {
                nombre: j.nombre || 'Sin nombre',
                matricula: j.matricula || '-',
                grupo: j.grupo || '-'
            },
            periodo: {
                inicioFormateado: formatearFecha(j.fecha_inicio_ausencia),
                finFormateado: formatearFecha(j.fecha_fin_ausencia),
                dias: j.dias_solicitados
            }
        }));

        datosFiltrados = [...justificantesData];

        actualizarResumen();
        renderizarTabla();

    } catch (error) {
        console.error("Error cargando MySQL:", error);
    }
}

function aplicarFiltros() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';

    datosFiltrados = justificantesData.filter(j =>
        j.folio.toLowerCase().includes(searchTerm) ||
        j.alumno.nombre.toLowerCase().includes(searchTerm) ||
        j.alumno.matricula.toLowerCase().includes(searchTerm)
    );

    renderizarTabla();
    actualizarResumen();
}

function actualizarResumen() {
    document.getElementById('totalPermisos').textContent = datosFiltrados.length;
}

function renderizarTabla() {
    const tbody = document.getElementById('historialBody');
    if (!tbody) return;

    if (datosFiltrados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9">📭 No hay datos en MySQL</td></tr>';
        return;
    }

    tbody.innerHTML = datosFiltrados.map(j => `
        <tr>
            <td>${j.folio}</td>
            <td>${j.fechaEmisionFormateada}</td>
            <td>${j.alumno.nombre}</td>
            <td>${j.alumno.matricula}</td>
            <td>${j.alumno.grupo}</td>
            <td>${j.periodo.inicioFormateado} - ${j.periodo.finFormateado}</td>
            <td>${j.periodo.dias}</td>
        </tr>
    `).join('');
}