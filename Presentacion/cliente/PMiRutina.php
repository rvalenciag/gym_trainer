<?php
// PMiRutina: consulta de todos los planes asignados al cliente (CU07).
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NRutina.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/media.php';

class PMiRutina {
    public function listarRutinas($idCliente) {
        return (new NRutina())->listarPorCliente((int)$idCliente);
    }

    public function verRutina($idCliente, $idRutina, $dia) {
        return (new NRutina())->rutinaDelDia((int)$idCliente, (int)$idRutina, (int)$dia);
    }

    public function mostrar() {
        $rutinas = $this->listarRutinas($_SESSION['uid']);
        mostrarHeader('Mis Rutinas');

        if (count($rutinas) === 0) {
            echo '<section class="gym-card gym-empty-state">';
            echo '    <span class="gym-heading-icon">' . iconoGym('routine') . '</span>';
            echo '    <h2>Aún no tienes rutinas asignadas</h2>';
            echo '    <p>Ponte en contacto con tu instructor para que diseñe un plan para ti.</p>';
            echo '</section>';
            mostrarFooter();
            return;
        }

        $idRutina = isset($_GET['plan']) ? (int)$_GET['plan'] : (int)$rutinas[0]['id'];
        $dia = isset($_GET['dia']) ? (int)$_GET['dia'] : 1;
        if ($dia < 1 || $dia > 7) {
            $dia = 1;
        }

        $res = $this->verRutina($_SESSION['uid'], $idRutina, $dia);
        if ($res['rutina'] === null) {
            $idRutina = (int)$rutinas[0]['id'];
            $res = $this->verRutina($_SESSION['uid'], $idRutina, $dia);
        }

        $rutina = $res['rutina'];
        $dias = [
            1 => ['Día 1', 'Lun'], 2 => ['Día 2', 'Mar'], 3 => ['Día 3', 'Mié'],
            4 => ['Día 4', 'Jue'], 5 => ['Día 5', 'Vie'], 6 => ['Día 6', 'Sáb'],
            7 => ['Día 7', 'Dom'],
        ];
        $nombresDias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        ?>
        <section class="gym-card gym-plans-card">
            <div class="gym-card-heading">
                <span class="gym-heading-icon"><?php echo iconoGym('exercise'); ?></span>
                <div>
                    <h2>Rutinas asignadas</h2>
                    <p>Elige un plan para ver sus ejercicios</p>
                </div>
                <span class="gym-count-badge">
                    <?php echo count($rutinas); ?> rutina<?php echo count($rutinas) === 1 ? '' : 's'; ?>
                </span>
            </div>
            <div class="gym-plan-cards">
                <?php foreach ($rutinas as $plan): ?>
                    <a class="gym-plan-card<?php echo (int)$plan['id'] === (int)$rutina['id'] ? ' active' : ''; ?>"
                        href="index.php?r=mi-rutina&plan=<?php echo $plan['id']; ?>&dia=<?php echo $dia; ?>">
                        <span class="gym-heading-icon"><?php echo iconoGym('exercise'); ?></span>
                        <span>
                            <strong><?php echo htmlspecialchars($plan['nombre_plan']); ?></strong>
                            <small>Plan semanal de entrenamiento</small>
                        </span>
                        <?php if ((int)$plan['id'] === (int)$rutina['id']): ?>
                            <b>✓</b>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="gym-card gym-client-routine-card">
            <div class="gym-card-heading">
                <span class="gym-heading-icon"><?php echo iconoGym('routine'); ?></span>
                <div>
                    <h2><?php echo htmlspecialchars($rutina['nombre_plan']); ?></h2>
                    <p>Plan semanal · selecciona un día para consultar los ejercicios</p>
                </div>
            </div>
            <div class="gym-day-tabs gym-client-day-tabs">
                <?php foreach ($dias as $numero => $texto): ?>
                    <a class="gym-day-tab<?php echo $numero === $dia ? ' active' : ''; ?>"
                        href="index.php?r=mi-rutina&plan=<?php echo $rutina['id']; ?>&dia=<?php echo $numero; ?>">
                        <strong><?php echo $texto[0]; ?></strong>
                        <span><?php echo $texto[1]; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="gym-client-day-heading">
                <h3><?php echo $dias[$dia][0] . ' · ' . $nombresDias[$dia - 1]; ?></h3>
                <span>
                    <?php echo count($res['detalles']); ?> ejercicio<?php echo count($res['detalles']) === 1 ? '' : 's'; ?>
                    asignado<?php echo count($res['detalles']) === 1 ? '' : 's'; ?>
                </span>
            </div>
            <?php if (count($res['detalles']) === 0): ?>
                <div class="alert alert-info mb-0">Descanso: sin ejercicios este día.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table gym-table gym-client-table mb-0">
                        <thead>
                            <tr>
                                <th>Ejercicio</th><th>Series</th><th>Repeticiones</th><th>Peso</th>
                                <th>Descanso</th><th>Guía</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($res['detalles'] as $e): ?>
                                <?php $gid = 'g_' . $rutina['id'] . '_' . $e['id']; ?>
                                <tr>
                                    <td class="gym-strong-cell"><?php echo htmlspecialchars($e['ejercicio']); ?></td>
                                    <td><?php echo $e['series']; ?></td>
                                    <td><?php echo htmlspecialchars($e['repeticiones']); ?></td>
                                    <td><?php echo htmlspecialchars($e['peso_sugerido'] ?? '-'); ?><?php echo $e['peso_sugerido'] === null ? '' : ' kg'; ?></td>
                                    <td><?php echo htmlspecialchars($e['descanso'] ?? '-'); ?><?php echo $e['descanso'] === null ? '' : ' s'; ?></td>
                                    <td><button type="button" class="gym-guide-btn" data-guia-btn="<?php echo $gid; ?>">▣ Ver guía</button></td>
                                </tr>
                                <tr id="<?php echo $gid; ?>" class="gym-guide-row" style="display:none">
                                    <td colspan="6">
                                        <div class="gym-guide-card">
                                            <div class="gym-card-heading">
                                                <span class="gym-heading-icon"><?php echo iconoGym('exercise'); ?></span>
                                                <div>
                                                    <h2>Guía de ejecución · <?php echo htmlspecialchars($e['ejercicio']); ?></h2>
                                                    <p>Descripción y recurso multimedia asociados al ejercicio</p>
                                                </div>
                                            </div>
                                            <?php mostrarGuia($e); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <?php
        mostrarFooter();
    }
}
