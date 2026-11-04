<?php
// PMiRutina: consulta de todos los planes asignados al cliente (CU07).
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NRutina.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/media.php';

class PMiRutina
{
    private const DIAS = [
        1 => ['Día 1', 'Lun'],
        2 => ['Día 2', 'Mar'],
        3 => ['Día 3', 'Mié'],
        4 => ['Día 4', 'Jue'],
        5 => ['Día 5', 'Vie'],
        6 => ['Día 6', 'Sáb'],
        7 => ['Día 7', 'Dom'],
    ];

    private const NOMBRES_DIAS = [
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
        'Domingo',
    ];

    private int $idCliente = 0;
    private int $idRutina = 0;
    private int $dia = 1;
    private array $rutinas = [];
    private ?array $rutina = null;
    private array $detallesRutina = [];

    public function listarRutinas($idCliente)
    {
        return (new NRutina())->listarPorCliente((int)$idCliente);
    }

    public function verRutina($idCliente, $idRutina, $dia)
    {
        return (new NRutina())->rutinaDelDia((int)$idCliente, (int)$idRutina, (int)$dia);
    }

    public function mostrar()
    {
        $this->idCliente = (int) $_SESSION['uid'];
        $this->rutinas = $this->listarRutinas($this->idCliente);
        mostrarHeader('Mis Rutinas');

        if (count($this->rutinas) === 0) {
            echo '<section class="gym-card gym-empty-state">';
            echo '    <span class="gym-heading-icon">' . iconoGym('routine') . '</span>';
            echo '    <h2>Aún no tienes rutinas asignadas</h2>';
            echo '    <p>Ponte en contacto con tu instructor para que diseñe un plan para ti.</p>';
            echo '</section>';
            mostrarFooter();
            return;
        }

        $this->idRutina = isset($_GET['plan'])
            ? (int) $_GET['plan']
            : (int) $this->rutinas[0]['id'];
        $this->dia = isset($_GET['dia']) ? (int) $_GET['dia'] : 1;
        if ($this->dia < 1 || $this->dia > 7) {
            $this->dia = 1;
        }

        $res = $this->verRutina($this->idCliente, $this->idRutina, $this->dia);
        if ($res['rutina'] === null) {
            $this->idRutina = (int) $this->rutinas[0]['id'];
            $res = $this->verRutina($this->idCliente, $this->idRutina, $this->dia);
        }

        $this->rutina = $res['rutina'];
        $this->detallesRutina = $res['detalles'];
?>
        <section class="gym-card gym-plans-card">
            <div class="gym-card-heading">
                <span class="gym-heading-icon"><?php echo iconoGym('exercise'); ?></span>
                <div>
                    <h2>Rutinas asignadas</h2>
                    <p>Elige un plan para ver sus ejercicios</p>
                </div>
                <span class="gym-count-badge">
                    <?php echo count($this->rutinas); ?> rutina<?php echo count($this->rutinas) === 1 ? '' : 's'; ?>
                </span>
            </div>
            <div class="gym-plan-cards">
                <?php foreach ($this->rutinas as $plan): ?>
                    <a class="gym-plan-card<?php echo (int)$plan['id'] === (int)$this->rutina['id'] ? ' active' : ''; ?>"
                        href="index.php?r=mi-rutina&plan=<?php echo $plan['id']; ?>&dia=<?php echo $this->dia; ?>">
                        <span class="gym-heading-icon"><?php echo iconoGym('exercise'); ?></span>
                        <span>
                            <strong><?php echo htmlspecialchars($plan['nombre_plan']); ?></strong>
                            <small>Plan semanal de entrenamiento</small>
                        </span>
                        <?php if ((int)$plan['id'] === (int)$this->rutina['id']): ?>
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
                    <h2><?php echo htmlspecialchars($this->rutina['nombre_plan']); ?></h2>
                    <p>Plan semanal · selecciona un día para consultar los ejercicios</p>
                </div>
            </div>
            <div class="gym-day-tabs gym-client-day-tabs">
                <?php foreach (self::DIAS as $numero => $texto): ?>
                    <a class="gym-day-tab<?php echo $numero === $this->dia ? ' active' : ''; ?>"
                        href="index.php?r=mi-rutina&plan=<?php echo $this->rutina['id']; ?>&dia=<?php echo $numero; ?>">
                        <strong><?php echo $texto[0]; ?></strong>
                        <span><?php echo $texto[1]; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="gym-client-day-heading">
                <h3><?php echo self::DIAS[$this->dia][0] . ' · ' . self::NOMBRES_DIAS[$this->dia - 1]; ?></h3>
                <span>
                    <?php echo count($this->detallesRutina); ?> ejercicio<?php echo count($this->detallesRutina) === 1 ? '' : 's'; ?>
                    asignado<?php echo count($this->detallesRutina) === 1 ? '' : 's'; ?>
                </span>
            </div>
            <?php if (count($this->detallesRutina) === 0): ?>
                <div class="alert alert-info mb-0">Descanso: sin ejercicios este día.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table gym-table gym-client-table mb-0">
                        <thead>
                            <tr>
                                <th>Ejercicio</th>
                                <th>Series</th>
                                <th>Repeticiones</th>
                                <th>Peso</th>
                                <th>Descanso</th>
                                <th>Guía</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->detallesRutina as $e): ?>
                                <?php $gid = 'g_' . $this->rutina['id'] . '_' . $e['id']; ?>
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
