<?php
// Vista/historial.php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Historial por Grupo — CBTIS 199</title>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/estilos.css"/>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css"/>
    </head>
    <body class="panel-body">

        <!-- BARRA MÓVIL -->
        <div class="mobile-topbar">
            <button class="mobile-topbar-btn" id="btnOpenSidebar">&#9776;</button>
            <span class="mobile-topbar-title">Historial por Grupo</span>
        </div>

        <div class="with-sidebar">

            <!-- SIDEBAR -->
            <?php require_once __DIR__ . '/layout/sidebar.php'; ?>

            <!-- CONTENIDO -->
            <div class="sidebar-content">
                <div class="container">

                    <!-- HEADER -->
                    <div class="header">
                        <div class="header-left">
                            <div class="avatar"><?= strtoupper(substr($docente['nombre'], 0, 1)) ?></div>
                            <div>
                                <div class="header-name">Historial de Asistencia</div>
                                <div class="header-sub">Consulta el registro por grupo y materia</div>
                            </div>
                        </div>
                        <span class="badge badge-primary">Reportes</span>
                    </div>

                    <div class="main">

                        <!-- FILTROS -->
                        <form method="GET" action="<?= BASE_URL ?>/Controlador/HistorialControlador.php" id="formFiltros">
                            <input type="hidden" name="accion" value="historial"/>

                            <div class="selectors" style="grid-template-columns: 1fr 1fr 1fr 1fr; gap:12px; margin-bottom:20px;">
                                <div class="sel-card">
                                    <div class="sel-label">Materia</div>
                                    <select name="materia" id="sel_materia">
                                        <?php foreach ($materias as $m): ?>
                                            <option value="<?= $m['id'] ?>" <?= ($m['id'] == $materia_sel) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="sel-card">
                                    <div class="sel-label">Grupo</div>
                                    <select name="grupo" id="sel_grupo">
                                        <?php foreach ($grupos as $g): ?>
                                            <option value="<?= $g['id'] ?>" <?= ($g['id'] == $grupo_sel) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($g['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="sel-card">
                                    <div class="sel-label">Desde</div>
                                    <input type="date" name="fecha_ini" value="<?= htmlspecialchars($fecha_ini) ?>"
                                           style="background:transparent;border:none;outline:none;font-size:13px;font-weight:600;width:100%;"/>
                                </div>
                                <div class="sel-card">
                                    <div class="sel-label">Hasta</div>
                                    <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>"
                                           style="background:transparent;border:none;outline:none;font-size:13px;font-weight:600;width:100%;"/>
                                </div>
                            </div>

                            <div style="margin-bottom:20px; display:flex; gap:10px; align-items:center;">
                                <button type="submit" class="primary">Consultar</button>
                                <span style="font-size:13px; color:var(--color-muted);">
                                    Asistencia promedio del grupo:
                                    <strong style="color:<?= $porcentaje >= 80 ? 'var(--color-present-txt)' : 'var(--color-absent-txt)' ?>;">
                                        <?= $porcentaje ?>%
                                    </strong>
                                </span>
                            </div>
                        </form>

                        <!-- TABLA DE HISTORIAL -->
                        <?php if (empty($historial['fechas'])): ?>
                            <div style="background:#f9f6f2; border-radius:10px; padding:28px; text-align:center; color:var(--color-muted); font-size:14px; border:1px solid var(--color-border);">
                                No hay registros de asistencia para el período seleccionado.
                            </div>

                        <?php else: ?>

                            <div class="section-title">
                                Registro del <?= date('d/m/Y', strtotime($fecha_ini)) ?>
                                al <?= date('d/m/Y', strtotime($fecha_fin)) ?>
                                — <?= count($historial['alumnos']) ?> alumno(s)
                            </div>

                            <div class="table-wrap" style="overflow-x:auto;">
                                <table style="min-width: max-content;">
                                    <thead>
                                        <tr>
                                            <th style="position:sticky;left:0;background:var(--color-primary);z-index:2;">Matrícula</th>
                                            <th style="position:sticky;left:80px;background:var(--color-primary);z-index:2;">Alumno</th>
                                            <?php foreach ($historial['fechas'] as $f): ?>
                                                <th style="text-align:center; min-width:80px;">
                                                    <?= date('d/m', strtotime($f)) ?>
                                                </th>
                                            <?php endforeach; ?>
                                            <th style="text-align:center; background:#4a0e21;">✅</th>
                                            <th style="text-align:center; background:#4a0e21;">❌</th>
                                            <th style="text-align:center; background:#4a0e21;">⏰</th>
                                            <th style="text-align:center; background:#4a0e21;">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($historial['alumnos'] as $id => $al):
                                            $p = $au = $re = $ju = 0;
                                            foreach ($historial['fechas'] as $f) {
                                                $est = $al['asistencias'][$f] ?? null;
                                                if ($est === 'presente')
                                                    $p++;
                                                elseif ($est === 'ausente')
                                                    $au++;
                                                elseif ($est === 'retardo')
                                                    $re++;
                                                elseif ($est === 'justificada')
                                                    $ju++;
                                            }
                                            $total = $p + $au + $re + $ju;
                                            $pct = $total > 0 ? round(($p / $total) * 100) : 0;
                                            ?>
                                            <tr>
                                                <td style="color:var(--color-muted); position:sticky; left:0; background:#fff; z-index:1;">
                                                <?= htmlspecialchars($al['matricula']) ?>
                                                </td>
                                                <td style="font-weight:500; position:sticky; left:80px; background:#fff; z-index:1; min-width:160px;">
                                                <?= htmlspecialchars($al['alumno']) ?>
                                                </td>
                                                <?php
                                                foreach ($historial['fechas'] as $f):
                                                    $est = $al['asistencias'][$f] ?? null;
                                                    $icono = match ($est) {
                                                        'presente' => ['🟢', 'badge-green', 'P'],
                                                        'ausente' => ['🔴', 'badge-red', 'A'],
                                                        'retardo' => ['🟡', 'badge-amber', 'R'],
                                                        'justificada' => ['🟣', 'badge-purple', 'J'],
                                                        default => ['⬜', 'badge-pending', '-'],
                                                    };
                                                    ?>
                                                    <td style="text-align:center;">
                                                        <span class="badge <?= $icono[1] ?>" title="<?= ucfirst($est ?? 'sin registro') ?>">
            <?= $icono[2] ?>
                                                        </span>
                                                    </td>
        <?php endforeach; ?>
                                                <td style="text-align:center; font-weight:600; color:var(--color-present-txt);"><?= $p ?></td>
                                                <td style="text-align:center; font-weight:600; color:var(--color-absent-txt);"><?= $au ?></td>
                                                <td style="text-align:center; font-weight:600; color:var(--color-late-txt);"><?= $re ?></td>
                                                <td style="text-align:center; font-weight:700; color:<?= $pct >= 80 ? 'var(--color-present-txt)' : 'var(--color-absent-txt)' ?>;">
        <?= $pct ?>%
                                                </td>
                                            </tr>
    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- LEYENDA -->
                            <div style="margin-top:14px; display:flex; gap:12px; flex-wrap:wrap; font-size:12px; font-family:Arial,sans-serif; color:var(--color-muted);">
                                <span><span class="badge badge-green">P</span> Presente</span>
                                <span><span class="badge badge-red">A</span> Ausente</span>
                                <span><span class="badge badge-amber">R</span> Retardo</span>
                                <span><span class="badge badge-purple">J</span> Justificada</span>
                                <span><span class="badge badge-pending">-</span> Sin registro</span>
                            </div>

<?php endif; ?>

                    </div><!-- /main -->
                </div><!-- /container -->
            </div><!-- /sidebar-content -->
        </div><!-- /with-sidebar -->

        <script src="<?= BASE_URL ?>/js/historial.js"></script>
    </body>
</html>