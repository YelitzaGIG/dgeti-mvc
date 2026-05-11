<?php /* app/views/justificantes/create.php — Página informativa presencial */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Solicitar Justificante</h1>
    <p class="page-subtitle">Información sobre el proceso de justificantes de ausencia</p>
  </div>
  <a href="<?= APP_URL ?>/public/dashboard" class="btn-outline">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Regresar
  </a>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['type'] ?> animate-fadein">
  <?= $flash['msg'] ?>
</div>
<?php endif; ?>

<!-- Banner de aviso principal -->
<div style="
  background: var(--color-warning-bg);
  border: 1.5px solid #F5D97A;
  border-left: 5px solid var(--color-warning);
  border-radius: var(--radius-md);
  padding: var(--space-5) var(--space-6);
  display: flex;
  gap: var(--space-4);
  align-items: flex-start;
  margin-bottom: var(--space-6);
">
  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#8A5C00" stroke-width="2" style="flex-shrink:0;margin-top:2px">
    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
  </svg>
  <div>
    <p style="font-family:var(--font-sans);font-weight:800;font-size:1rem;color:#8A5C00;margin-bottom:.3rem;">
      Los justificantes se tramitan de forma presencial
    </p>
    <p style="font-family:var(--font-sans);font-size:.88rem;color:#8A5C00;line-height:1.6;">
      Este trámite <strong>no puede realizarse en línea</strong>. El tutor del alumno debe presentarse
      personalmente en el área de <strong>Orientadoras Educativas / Servicios Escolares</strong>
      con los documentos requeridos.
    </p>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6);align-items:start;">

  <!-- Columna izquierda: proceso paso a paso -->
  <div>
    <div class="section-card">
      <h2 class="section-title" style="margin-bottom:var(--space-5);">¿Cómo solicitar tu justificante?</h2>

      <!-- Paso 1 -->
      <div style="display:flex;gap:var(--space-4);margin-bottom:var(--space-5);">
        <div style="
          flex-shrink:0;width:36px;height:36px;
          background:var(--color-primary);color:var(--color-white);
          border-radius:50%;display:flex;align-items:center;justify-content:center;
          font-family:var(--font-sans);font-weight:800;font-size:.9rem;
        ">1</div>
        <div>
          <p style="font-family:var(--font-sans);font-weight:700;font-size:.9rem;color:var(--color-text);margin-bottom:.25rem;">
            Reúne los documentos necesarios
          </p>
          <p style="font-family:var(--font-sans);font-size:.83rem;color:var(--color-text-muted);line-height:1.55;">
            Prepara la identificación del tutor (INE, credencial vigente) y el comprobante
            del motivo de la ausencia (nota médica, citatorio, etc.).
          </p>
        </div>
      </div>

      <!-- Paso 2 -->
      <div style="display:flex;gap:var(--space-4);margin-bottom:var(--space-5);">
        <div style="
          flex-shrink:0;width:36px;height:36px;
          background:var(--color-primary);color:var(--color-white);
          border-radius:50%;display:flex;align-items:center;justify-content:center;
          font-family:var(--font-sans);font-weight:800;font-size:.9rem;
        ">2</div>
        <div>
          <p style="font-family:var(--font-sans);font-weight:700;font-size:.9rem;color:var(--color-text);margin-bottom:.25rem;">
            El tutor acude a Servicios Escolares
          </p>
          <p style="font-family:var(--font-sans);font-size:.83rem;color:var(--color-text-muted);line-height:1.55;">
            El tutor o padre de familia se presenta personalmente en la ventanilla
            de <strong style="color:var(--color-primary);">Orientadoras Educativas — Servicios Escolares</strong>,
            identificado con credencial oficial vigente.
          </p>
        </div>
      </div>

      <!-- Paso 3 -->
      <div style="display:flex;gap:var(--space-4);margin-bottom:var(--space-5);">
        <div style="
          flex-shrink:0;width:36px;height:36px;
          background:var(--color-primary);color:var(--color-white);
          border-radius:50%;display:flex;align-items:center;justify-content:center;
          font-family:var(--font-sans);font-weight:800;font-size:.9rem;
        ">3</div>
        <div>
          <p style="font-family:var(--font-sans);font-weight:700;font-size:.9rem;color:var(--color-text);margin-bottom:.25rem;">
            La orientadora analiza la solicitud
          </p>
          <p style="font-family:var(--font-sans);font-size:.83rem;color:var(--color-text-muted);line-height:1.55;">
            La orientadora educativa revisa la información y los comprobantes presentados,
            y determina si la solicitud es <strong style="color:var(--color-success);">aprobada</strong>
            o <strong style="color:var(--color-error);">rechazada</strong>.
          </p>
        </div>
      </div>

      <!-- Paso 4 -->
      <div style="display:flex;gap:var(--space-4);">
        <div style="
          flex-shrink:0;width:36px;height:36px;
          background:var(--color-primary);color:var(--color-white);
          border-radius:50%;display:flex;align-items:center;justify-content:center;
          font-family:var(--font-sans);font-weight:800;font-size:.9rem;
        ">4</div>
        <div>
          <p style="font-family:var(--font-sans);font-weight:700;font-size:.9rem;color:var(--color-text);margin-bottom:.25rem;">
            Consulta el estado en este sistema
          </p>
          <p style="font-family:var(--font-sans);font-size:.83rem;color:var(--color-text-muted);line-height:1.55;">
            Una vez registrado por la orientadora, podrás ver el estado de tu justificante
            en el apartado <strong>"Mis Justificantes"</strong> de esta plataforma.
          </p>
        </div>
      </div>
    </div>

    <!-- Documentos requeridos -->
    <div class="section-card">
      <h2 class="section-title" style="margin-bottom:var(--space-4);">Documentos requeridos</h2>
      <div style="display:flex;flex-direction:column;gap:var(--space-3);">

        <div style="display:flex;align-items:flex-start;gap:var(--space-3);padding:var(--space-3);background:var(--color-surface);border-radius:var(--radius-sm);border:1px solid var(--color-border);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" style="flex-shrink:0;margin-top:1px">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          <div>
            <p style="font-family:var(--font-sans);font-weight:700;font-size:.83rem;color:var(--color-text);">Identificación oficial del tutor</p>
            <p style="font-family:var(--font-sans);font-size:.78rem;color:var(--color-text-muted);">INE, pasaporte o credencial vigente con fotografía</p>
          </div>
        </div>

        <div style="display:flex;align-items:flex-start;gap:var(--space-3);padding:var(--space-3);background:var(--color-surface);border-radius:var(--radius-sm);border:1px solid var(--color-border);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" style="flex-shrink:0;margin-top:1px">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
          </svg>
          <div>
            <p style="font-family:var(--font-sans);font-weight:700;font-size:.83rem;color:var(--color-text);">Comprobante del motivo</p>
            <p style="font-family:var(--font-sans);font-size:.78rem;color:var(--color-text-muted);">Nota médica, citatorio, constancia o documento oficial según el caso</p>
          </div>
        </div>

        <div style="display:flex;align-items:flex-start;gap:var(--space-3);padding:var(--space-3);background:var(--color-surface);border-radius:var(--radius-sm);border:1px solid var(--color-border);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" style="flex-shrink:0;margin-top:1px">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
          <div>
            <p style="font-family:var(--font-sans);font-weight:700;font-size:.83rem;color:var(--color-text);">Datos del alumno</p>
            <p style="font-family:var(--font-sans);font-size:.78rem;color:var(--color-text-muted);">Matrícula, nombre completo y grupo para agilizar el registro</p>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Columna derecha -->
  <div>

    <!-- Tarjeta de dónde acudir -->
    <div class="section-card" style="border:1.5px solid rgba(98,17,50,.2);">
      <div style="display:flex;align-items:center;gap:var(--space-3);margin-bottom:var(--space-5);">
        <div style="
          width:44px;height:44px;border-radius:var(--radius-sm);
          background:rgba(98,17,50,.1);display:flex;align-items:center;justify-content:center;
          flex-shrink:0;
        ">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
          </svg>
        </div>
        <div>
          <p style="font-family:var(--font-sans);font-weight:800;font-size:.95rem;color:var(--color-primary);">¿Dónde acudir?</p>
          <p style="font-family:var(--font-sans);font-size:.78rem;color:var(--color-text-muted);">Área de atención presencial</p>
        </div>
      </div>

      <div style="background:var(--color-surface);border-radius:var(--radius-sm);padding:var(--space-4);border:1px solid var(--color-border);margin-bottom:var(--space-4);">
        <p style="font-family:var(--font-sans);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--color-text-muted);margin-bottom:.3rem;">Área</p>
        <p style="font-family:var(--font-sans);font-weight:800;font-size:1rem;color:var(--color-primary);line-height:1.3;">
          Orientadoras Educativas<br>
          <span style="font-weight:600;font-size:.88rem;color:var(--color-text-mid);">(Servicios Escolares)</span>
        </p>
      </div>

      <div style="background:var(--color-surface);border-radius:var(--radius-sm);padding:var(--space-4);border:1px solid var(--color-border);margin-bottom:var(--space-4);">
        <p style="font-family:var(--font-sans);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--color-text-muted);margin-bottom:.3rem;">Función del área</p>
        <p style="font-family:var(--font-sans);font-size:.83rem;color:var(--color-text);line-height:1.55;">
          Revisa las solicitudes de justificantes de los alumnos, analiza la información
          y los comprobantes presentados, y decide si la solicitud es
          <strong style="color:var(--color-success);">aprobada</strong> o
          <strong style="color:var(--color-error);">rechazada</strong>.
        </p>
      </div>

      <div style="background:var(--color-surface);border-radius:var(--radius-sm);padding:var(--space-4);border:1px solid var(--color-border);">
        <p style="font-family:var(--font-sans);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--color-text-muted);margin-bottom:.3rem;">Institución</p>
        <p style="font-family:var(--font-sans);font-weight:700;font-size:.88rem;color:var(--color-text);">CBTIS 199 · Jaguares</p>
      </div>
    </div>

    <!-- Quién debe presentarse -->
    <div class="section-card" style="background:rgba(98,17,50,.04);border-color:rgba(98,17,50,.15);">
      <h2 class="section-title" style="margin-bottom:var(--space-4);">¿Quién debe presentarse?</h2>
      <div style="display:flex;gap:var(--space-3);align-items:flex-start;margin-bottom:var(--space-3);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" style="flex-shrink:0;margin-top:2px">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        <p style="font-family:var(--font-sans);font-size:.85rem;color:var(--color-text);line-height:1.55;">
          El <strong>tutor o padre de familia</strong> del alumno, de manera presencial.
        </p>
      </div>
      <div style="display:flex;gap:var(--space-3);align-items:flex-start;margin-bottom:var(--space-3);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" style="flex-shrink:0;margin-top:2px">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        <p style="font-family:var(--font-sans);font-size:.85rem;color:var(--color-text);line-height:1.55;">
          Debe traer <strong>credencial o identificación oficial vigente</strong>
          (INE u otro documento con fotografía).
        </p>
      </div>
      <div style="display:flex;gap:var(--space-3);align-items:flex-start;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-error)" stroke-width="2" style="flex-shrink:0;margin-top:2px">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
        <p style="font-family:var(--font-sans);font-size:.85rem;color:var(--color-text);line-height:1.55;">
          <strong style="color:var(--color-error);">No se aceptan solicitudes</strong> realizadas únicamente por el alumno
          sin la presencia del tutor.
        </p>
      </div>
    </div>

    <!-- Ver mis justificantes -->
    <div class="section-card" style="text-align:center;">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="1.5" style="margin:0 auto var(--space-3);">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
      </svg>
      <p style="font-family:var(--font-sans);font-weight:700;font-size:.9rem;color:var(--color-text);margin-bottom:var(--space-2);">
        ¿Ya tienes un justificante en proceso?
      </p>
      <p style="font-family:var(--font-sans);font-size:.82rem;color:var(--color-text-muted);margin-bottom:var(--space-4);">
        Consulta el estado de tus justificantes registrados en el sistema.
      </p>
      <a href="<?= APP_URL ?>/public/justificantes" class="btn-primary btn-full">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        Ver mis justificantes
      </a>
    </div>

  </div>
</div>

<style>
@media(max-width:768px){
  .page-header{flex-direction:column;}
  div[style*="grid-template-columns:1fr 1fr"]{
    grid-template-columns:1fr !important;
  }
}
</style>