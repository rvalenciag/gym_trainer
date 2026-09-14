<?php
// PRutinas: presentación de Gestión de Rutinas en UNA sola vista (CU06).
// Sección superior: cabecera del plan. Sección intermedia: constructor con tabla dinámica
// (nada se guarda hasta Guardar/Actualizar: todo vive en el navegador, filas det[0], det[1]...).
// Sección inferior: tabla de rutinas asignadas con Editar/Eliminar.
// Lee de 2 N desacoplados: clientes ← NCliente, ejercicios ← NEjercicio (solo lectura).
// Las mutaciones van únicamente a NRutina, dueño del CU.
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NRutina.php';
require_once __DIR__ . '/../../Negocio/NCliente.php';
require_once __DIR__ . '/../../Negocio/NEjercicio.php';
require_once __DIR__ . '/../includes/header.php';

class PRutinas {
    public function listar() {
        return (new NRutina())->listar();
    }

    public function listarClientes() {
        return (new NCliente())->listar(); // desacoplado: solo lectura
    }

    public function listarEjercicios() {
        return (new NEjercicio())->listar(); // desacoplado: solo lectura
    }

    public function guardar($idInstructor, $idCliente, $nombre, $ini, $fin, $dets) {
        $res = (new NRutina())->crear($idInstructor, $idCliente, $nombre, $ini, $fin, $dets);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=rutinas');
        exit;
    }

    public function actualizar($id, $idCliente, $nombre, $ini, $fin, $dets, $idInstructor) {
        $res = (new NRutina())->actualizar($id, $idCliente, $nombre, $ini, $fin, $dets, $idInstructor);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=' . ($res['ok'] ? 'rutinas' : 'rutinas&id=' . $id));
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
        // Alta o edición (el hidden "id" decide).
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = trim($_POST['id'] ?? '');
            $id === ''
                ? $this->guardar($_SESSION['uid'], $_POST['id_cliente'] ?? 0, $_POST['nombre_plan'] ?? '',
                    $_POST['fecha_inicio'] ?? '', $_POST['fecha_fin'] ?? '', $_POST['det'] ?? [])
                : $this->actualizar($id, $_POST['id_cliente'] ?? 0, $_POST['nombre_plan'] ?? '',
                    $_POST['fecha_inicio'] ?? '', $_POST['fecha_fin'] ?? '', $_POST['det'] ?? [], $_SESSION['uid']);
        }
        // Baja con confirmación previa en el navegador.
        if (isset($_GET['del'])) {
            $this->eliminar($_GET['del']);
        }
        // Edición: ?id= precarga cabecera + detalles en el form superior.
        $editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $cab = $editId > 0 ? $n->obtenerCabecera($editId) : null;
        $dets = $editId > 0 && $cab ? $n->listarDetalles($editId) : [];
        $clientes = $this->listarClientes();
        $ejercicios = $this->listarEjercicios();
        $sinBase = count($clientes) === 0 || count($ejercicios) === 0;
        $lista = $this->listar();
        $dias = [1 => 'Día 1 (Lun)', 2 => 'Día 2 (Mar)', 3 => 'Día 3 (Mié)', 4 => 'Día 4 (Jue)',
            5 => 'Día 5 (Vie)', 6 => 'Día 6 (Sáb)', 7 => 'Día 7 (Dom)'];
        mostrarHeader('Gestión de Rutinas');
        if ($sinBase) {
            echo '<div class="alert alert-warning">Debe registrar clientes y ejercicios antes de diseñar rutinas</div>';
        }
        ?>
        <form method="post" action="index.php?r=rutinas<?php echo $cab ? '&id=' . $cab['id'] : ''; ?>">
            <input type="hidden" name="id" value="<?php echo $cab ? $cab['id'] : ''; ?>">
            <!-- ============ SECCIÓN SUPERIOR: cabecera del plan ============ -->
            <div class="tabla-form">
                <h5><?php echo $cab ? 'Editar plan' : 'Nuevo plan'; ?></h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Cliente Destino *</label>
                        <select name="id_cliente" class="form-select" required <?php echo $sinBase ? 'disabled' : ''; ?>>
                            <option value="">Seleccione...</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($cab && $cab['id_cliente'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nombre del Plan *</label>
                        <input type="text" name="nombre_plan" class="form-control" required
                            value="<?php echo htmlspecialchars($cab['nombre_plan'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Fecha de Inicio *</label>
                        <input type="date" name="fecha_inicio" class="form-control" required
                            value="<?php echo htmlspecialchars($cab['fecha_inicio'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Fecha de Fin *</label>
                        <input type="date" name="fecha_fin" class="form-control" required
                            value="<?php echo htmlspecialchars($cab['fecha_fin'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <!-- ============ SECCIÓN INTERMEDIA: constructor de detalle ============ -->
            <div class="tabla-form">
                <h5>Constructor de detalle <small class="text-muted">(se guarda todo junto al final)</small></h5>
                <table class="table table-bordered bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th>Día</th><th>Ejercicio</th><th>Series</th><th>Repeticiones</th>
                            <th>Peso (kg)</th><th>Descanso (seg)</th><th></th>
                        </tr>
                    </thead>
                    <tbody id="det-body" data-next="<?php echo count($dets); ?>">
                        <?php foreach ($dets as $i => $d): ?>
                            <tr>
                                <td>
                                    <select name="det[<?php echo $i; ?>][dia]" class="form-select">
                                        <?php foreach ($dias as $k => $v): ?>
                                            <option value="<?php echo $k; ?>" <?php echo $d['dia_semana'] == $k ? 'selected' : ''; ?>>
                                                <?php echo $v; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="det[<?php echo $i; ?>][ejercicio]" class="form-select">
                                        <?php foreach ($ejercicios as $e): ?>
                                            <option value="<?php echo $e['id']; ?>" <?php echo $d['id_ejercicio'] == $e['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($e['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="det[<?php echo $i; ?>][series]" class="form-control" min="1" value="<?php echo $d['series']; ?>">
                                </td>
                                <td>
                                    <input type="text" name="det[<?php echo $i; ?>][repeticiones]" class="form-control" value="<?php echo htmlspecialchars($d['repeticiones']); ?>">
                                </td>
                                <td>
                                    <input type="number" step="0.5" min="0" name="det[<?php echo $i; ?>][peso]" class="form-control" value="<?php echo htmlspecialchars($d['peso_sugerido'] ?? ''); ?>">
                                </td>
                                <td>
                                    <input type="number" min="0" name="det[<?php echo $i; ?>][descanso]" class="form-control" value="<?php echo htmlspecialchars($d['descanso'] ?? ''); ?>">
                                </td>
                                <td><button type="button" class="btn btn-sm btn-danger btn-del-det">X</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <template id="det-row-tpl">
                    <tr>
                        <td>
                            <select name="det[__IDX__][dia]" class="form-select">
                                <?php foreach ($dias as $k => $v): ?>
                                    <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="det[__IDX__][ejercicio]" class="form-select">
                                <?php foreach ($ejercicios as $e): ?>
                                    <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="det[__IDX__][series]" class="form-control" min="1" value="3"></td>
                        <td><input type="text" name="det[__IDX__][repeticiones]" class="form-control" value="10"></td>
                        <td><input type="number" step="0.5" min="0" name="det[__IDX__][peso]" class="form-control"></td>
                        <td><input type="number" min="0" name="det[__IDX__][descanso]" class="form-control" value="60"></td>
                        <td><button type="button" class="btn btn-sm btn-danger btn-del-det">X</button></td>
                    </tr>
                </template>
                <div class="d-flex gap-2">
                    <button type="button" id="btn-add-det" class="btn btn-outline-success" <?php echo $sinBase ? 'disabled' : ''; ?>>+ Agregar Ejercicio a la Rutina</button>
                    <button type="submit" class="btn btn-success" <?php echo $sinBase ? 'disabled' : ''; ?>><?php echo $cab ? 'Actualizar Rutina' : 'Guardar y Asignar Rutina'; ?></button>
                    <?php if ($cab): ?>
                        <a class="btn btn-secondary" href="index.php?r=rutinas">Cancelar edición</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <!-- ============ SECCIÓN INFERIOR: tabla de rutinas asignadas ============ -->
        <div class="tabla-form">
            <h5>Rutinas asignadas</h5>
            <table class="table table-striped table-bordered bg-white">
                <thead class="table-dark">
                    <tr><th>Cliente</th><th>Nombre de la Rutina</th><th>Vigencia</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['cliente']); ?></td>
                            <td><?php echo htmlspecialchars($r['nombre_plan']); ?></td>
                            <td><?php echo $r['fecha_inicio']; ?> al <?php echo $r['fecha_fin']; ?></td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="index.php?r=rutinas&id=<?php echo $r['id']; ?>">Editar</a>
                                <a class="btn btn-sm btn-danger"
                                    href="index.php?r=rutinas&del=<?php echo $r['id']; ?>"
                                    onclick="return confirm('¿Está seguro de eliminar esta rutina? Se borrará el plan y todo su detalle asignado')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($lista) === 0): ?>
                        <tr><td colspan="4" class="text-center">Sin rutinas asignadas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        mostrarFooter();
    }
}
