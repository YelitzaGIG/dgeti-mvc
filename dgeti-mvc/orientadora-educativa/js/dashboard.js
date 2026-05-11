// Función para guardar justificante en MySQL
async function guardarJustificante() {
    const id = document.getElementById('editId').value;
    const nombre = document.getElementById('nombreAlumno').value.trim();
    const apellido = ''; // Si tienes campo apellido, cámbialo
    const matricula = document.getElementById('matriculaAlumno').value.trim();
    const grado = document.getElementById('gradoAlumno').value;
    const grupo = document.getElementById('grupoAlumno').value.trim();
    const email = document.getElementById('emailAlumno').value.trim();
    const tutor = document.getElementById('nombreTutor')?.value.trim() || 'No especificado';
    const motivo = document.getElementById('motivoTipo').value.trim();
    const descripcion = document.getElementById('descripcionJusti').value.trim();
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    
    if (!nombre || !matricula || !motivo) {
        mostrarNotificacion('Complete los campos obligatorios', 'error');
        return false;
    }
    
    // Calcular días
    let dias = 1;
    if (fechaInicio && fechaFin) {
        const diffTime = Math.abs(new Date(fechaFin) - new Date(fechaInicio));
        dias = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        if (dias < 1) dias = 1;
    }
    
    const datos = {
        nombre: nombre,
        apellido: apellido,
        matricula: matricula,
        grado: grado,
        grupo: grupo,
        email: email,
        tutor: tutor,
        motivo: motivo,
        descripcion: descripcion,
        fechaInicio: fechaInicio,
        fechaFin: fechaFin,
        dias: dias
    };
    
    try {
        const resultado = await API.guardarJustificante(datos);
        
        if (resultado.success) {
            mostrarNotificacion('✅ Justificante guardado en MySQL', 'success');
            document.getElementById('modalForm').style.display = 'none';
            limpiarFormulario();
            
            // Recargar datos desde MySQL
            const solicitudes = await API.getSolicitudes();
            renderizarTabla(solicitudes);
            
            const stats = await API.getEstadisticas();
            actualizarEstadisticas(stats);
            
            return true;
        } else {
            mostrarNotificacion('❌ Error al guardar', 'error');
            return false;
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('❌ Error de conexión con el servidor', 'error');
        return false;
    }
}