<?php
// PRutinas: constructor visual de planes semanales (CU06).
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NRutina.php';
require_once __DIR__ . '/../../Negocio/NCliente.php';
require_once __DIR__ . '/../../Negocio/NEjercicio.php';
require_once __DIR__ . '/../includes/header.php';

class PRutinas {
    public function listar() { return (new NRutina())->listar(); }
    public function listarClientes() { return (new NCliente())->listar(); }
    public function listarEjercicios() { return (new NEjercicio())->listar(); }

    public function guardar($idInstructor, $idCliente, $nombre, $dets) {
        $res = (new NRutina())->crear($idInstructor, $idCliente, $nombre, $dets);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=rutinas');
        exit;
    }

    public function actualizar($id, $idCliente, $nombre, $dets, $idInstructor) {
        $res = (new NRutina())->actualizar($id, $idCliente, $nombre, $dets, $idInstructor);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=' . ($res['ok'] ? 'rutinas' : 'rutinas&id=' . (int)$id));
        exit;
    }

    public function eliminar($id) {
        $res = (new NRutina())->eliminar($id);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=rutinas');
        exit;
    }

    public function mostrar() {
        $n = new NRutina();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = trim($_POST['id'] ?? '');
            $id === ''
                ? $this->guardar($_SESSION['uid'], $_POST['id_cliente'] ?? 0, $_POST['nombre_plan'] ?? '', $_POST['det'] ?? [])
                : $this->actualizar($id, $_POST['id_cliente'] ?? 0, $_POST['nombre_plan'] ?? '', $_POST['det'] ?? [], $_SESSION['uid']);
        }
        if (isset($_GET['del'])) { $this->eliminar($_GET['del']); }

        $editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $cab = $editId > 0 ? $n->obtenerCabecera($editId) : null;
        $dets = $cab ? $n->listarDetalles($editId) : [];
        $clientes = $this->listarClientes();
        $ejercicios = $this->listarEjercicios();
        $sinBase = count($clientes) === 0 || count($ejercicios) === 0;
        $lista = $this->listar();
        $dias = [1 => ['Día 1', 'Lun'], 2 => ['Día 2', 'Mar'], 3 => ['Día 3', 'Mié'],
            4 => ['Día 4', 'Jue'], 5 => ['Día 5', 'Vie'], 6 => ['Día 6', 'Sáb'], 7 => ['Día 7', 'Dom']];
        mostrarHeader('Gestión de Rutinas');
        if ($sinBase) { echo '<div class="alert alert-warning">Debe registrar clientes y ejercicios antes de diseñar rutinas</div>'; }
        ?>
        <form method="post" action="index.php?r=rutinas<?php echo $cab ? '&id=' . $cab['id'] : ''; ?>">
            <input type="hidden" name="id" value="<?php echo $cab ? $cab['id'] : ''; ?>">
            <div class="gym-routine-grid">
                <section class="gym-card gym-routine-data">
                    <div class="gym-card-heading"><span class="gym-heading-icon"><?php echo iconoGym('routine'); ?></span><div><h2>Datos de la rutina</h2><p>Configura los datos del plan</p></div></div>
                    <div class="mb-3">
                        <label class="form-label">Cliente *</label>
                        <select name="id_cliente" class="form-select" required <?php echo $sinBase ? 'disabled' : ''; ?>>
                            <option value="">Seleccionar cliente</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($cab && $cab['id_cliente'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de la rutina *</label>
                        <input type="text" name="nombre_plan" class="form-control" placeholder="Ej. Fuerza general" required value="<?php echo htmlspecialchars($cab['nombre_plan'] ?? ''); ?>">
                    </div>
                    <div class="gym-plan-note"><strong>PLAN SEMANAL</strong><span>Lunes a Domingo</span></div>
                </section>
                <section class="gym-card gym-routine-builder">
                    <div class="gym-card-heading"><span class="gym-heading-icon"><?php echo iconoGym('exercise'); ?></span><div><h2>Ejercicios por día</h2><p>Distribuye los ejercicios de lunes a domingo</p></div></div>
                    <div class="gym-day-tabs" id="routine-day-tabs" role="tablist">
                        <?php foreach ($dias as $dia => $texto): ?>
                            <button type="button" class="gym-day-tab<?php echo $dia === 1 ? ' active' : ''; ?>" data-routine-day="<?php echo $dia; ?>"><strong><?php echo $texto[0]; ?></strong><span><?php echo $texto[1]; ?></span></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="gym-routine-day-title" id="routine-day-title">Día 1 · Lunes</div>
                    <div class="table-responsive"><table class="table gym-table gym-routine-table mb-0">
                        <thead><tr><th>Ejercicio</th><th>Series</th><th>Reps</th><th>Peso kg</th><th>Descanso s</th><th>Quitar</th></tr></thead>
                        <tbody id="det-body" data-next="<?php echo count($dets); ?>" data-active-day="1">
                            <?php foreach ($dets as $i => $d): ?>
                                <tr data-routine-day-row="<?php echo $d['dia_semana']; ?>">
                                    <td><input type="hidden" name="det[<?php echo $i; ?>][dia]" value="<?php echo $d['dia_semana']; ?>"><select name="det[<?php echo $i; ?>][ejercicio]" class="form-select"><?php foreach ($ejercicios as $e): ?><option value="<?php echo $e['id']; ?>" <?php echo $d['id_ejercicio'] == $e['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($e['nombre']); ?></option><?php endforeach; ?></select></td>
                                    <td><input type="number" name="det[<?php echo $i; ?>][series]" class="form-control" min="1" value="<?php echo $d['series']; ?>"></td>
                                    <td><input type="text" name="det[<?php echo $i; ?>][repeticiones]" class="form-control" value="<?php echo htmlspecialchars($d['repeticiones']); ?>"></td>
                                    <td><input type="number" step="0.5" min="0" name="det[<?php echo $i; ?>][peso]" class="form-control" value="<?php echo htmlspecialchars($d['peso_sugerido'] ?? ''); ?>"></td>
                                    <td><input type="number" min="0" name="det[<?php echo $i; ?>][descanso]" class="form-control" value="<?php echo htmlspecialchars($d['descanso'] ?? ''); ?>"></td>
                                    <td><button type="button" class="gym-icon-btn gym-delete-btn btn-del-det" aria-label="Quitar ejercicio"><?php echo iconoGym('delete'); ?></button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                    <template id="det-row-tpl"><tr data-routine-day-row="__DAY__"><td><input type="hidden" name="det[__IDX__][dia]" value="__DAY__"><select name="det[__IDX__][ejercicio]" class="form-select"><?php foreach ($ejercicios as $e): ?><option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option><?php endforeach; ?></select></td><td><input type="number" name="det[__IDX__][series]" class="form-control" min="1" value="3"></td><td><input type="text" name="det[__IDX__][repeticiones]" class="form-control" value="10"></td><td><input type="number" step="0.5" min="0" name="det[__IDX__][peso]" class="form-control"></td><td><input type="number" min="0" name="det[__IDX__][descanso]" class="form-control" value="60"></td><td><button type="button" class="gym-icon-btn gym-delete-btn btn-del-det" aria-label="Quitar ejercicio"><?php echo iconoGym('delete'); ?></button></td></tr></template>
                    <div class="gym-routine-actions"><button type="button" id="btn-add-det" class="gym-blue-btn" <?php echo $sinBase ? 'disabled' : ''; ?>>＋ Agregar ejercicio</button><span id="routine-add-hint">Se añadirá al lunes (Día 1)</span></div>
                </section>
            </div>
            <div class="d-flex flex-wrap gap-3 mb-4"><button type="submit" class="gym-primary-btn" <?php echo $sinBase ? 'disabled' : ''; ?>><?php echo iconoGym('save'); ?><span><?php echo $cab ? 'Actualizar rutina' : 'Guardar y asignar rutina'; ?></span></button><?php if ($cab): ?><a class="gym-secondary-btn" href="index.php?r=rutinas">Cancelar edición</a><?php endif; ?></div>
        </form>
        <section class="gym-card gym-table-card">
            <div class="gym-card-heading"><span class="gym-heading-icon"><?php echo iconoGym('category'); ?></span><div><h2>Rutinas asignadas</h2><p>Selecciona una rutina semanal para editarla o eliminarla</p></div></div>
            <div class="table-responsive"><table class="table gym-table mb-0"><thead><tr><th>#</th><th>Cliente</th><th>Plan</th><th>Acciones</th></tr></thead><tbody>
                <?php foreach ($lista as $r): ?><tr><td><?php echo str_pad((string)$r['id'], 2, '0', STR_PAD_LEFT); ?></td><td class="gym-strong-cell"><?php echo htmlspecialchars($r['cliente']); ?></td><td><?php echo htmlspecialchars($r['nombre_plan']); ?></td><td class="gym-actions"><a class="gym-icon-btn gym-edit-btn" aria-label="Editar rutina" href="index.php?r=rutinas&id=<?php echo $r['id']; ?>"><?php echo iconoGym('edit'); ?></a><a class="gym-icon-btn gym-delete-btn" aria-label="Eliminar rutina" href="index.php?r=rutinas&del=<?php echo $r['id']; ?>" onclick="return confirm('¿Está seguro de eliminar esta rutina? Se borrará el plan y todo su detalle asignado')"><?php echo iconoGym('delete'); ?></a></td></tr><?php endforeach; ?>
                <?php if (count($lista) === 0): ?><tr><td colspan="4" class="text-center">Sin rutinas asignadas</td></tr><?php endif; ?>
            </tbody></table></div>
        </section>
        <?php mostrarFooter();
    }
}
