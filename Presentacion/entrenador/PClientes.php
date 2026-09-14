<?php
// PClientes: presentación de Gestión de Clientes (CU05). Formulario superior + tabla inferior.
// Al editar se oculta la contraseña (JS): la clave solo se define al registrar (guardar).
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NCliente.php';
require_once __DIR__ . '/../includes/header.php';

class PClientes {
    public function listar() {
        return (new NCliente())->listar();
    }

    public function guardar($nombre, $correo, $telefono, $clave, $observacion) {
        $res = (new NCliente())->registrar($nombre, $correo, $telefono, $clave, $observacion);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=clientes');
        exit;
    }

    public function actualizar($id, $nombre, $correo, $telefono, $observacion) {
        $res = (new NCliente())->actualizar($id, $nombre, $correo, $telefono, $observacion);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=clientes');
        exit;
    }

    public function eliminar($id) {
        $res = (new NCliente())->eliminar($id);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=clientes');
        exit;
    }

    public function mostrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = trim($_POST['id'] ?? '');
            $id === ''
                ? $this->guardar($_POST['nombre'] ?? '', $_POST['correo'] ?? '', $_POST['telefono'] ?? '',
                    $_POST['contrasena'] ?? '', $_POST['observacion'] ?? '')
                : $this->actualizar($id, $_POST['nombre'] ?? '', $_POST['correo'] ?? '',
                    $_POST['telefono'] ?? '', $_POST['observacion'] ?? '');
        }
        if (isset($_GET['del'])) {
            $this->eliminar($_GET['del']);
        }
        $lista = $this->listar();
        mostrarHeader('Gestión de Clientes / Alumnos');
        ?>
        <div class="tabla-form">
            <form method="post" action="index.php?r=clientes">
                <input type="hidden" name="id" id="f-id" value="">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre" id="f-nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Correo Electrónico *</label>
                        <input type="email" name="correo" id="f-correo" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3" id="f-pass-wrap">
                        <label class="form-label">Contraseña Inicial *</label>
                        <input type="password" name="contrasena" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="f-telefono" class="form-control">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Observaciones / Ficha Médica</label>
                        <input type="text" name="observacion" id="f-observacion" class="form-control">
                    </div>
                    <div class="col-md-12 d-flex gap-2">
                        <button type="submit" id="btn-save" data-orig="Registrar Cliente" data-alt="Actualizar Cliente" class="btn btn-success">Registrar Cliente</button>
                        <button type="button" id="btn-cancel" class="btn btn-secondary" style="display:none">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
        <table class="table table-striped table-bordered bg-white">
            <thead class="table-dark">
                <tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Observaciones</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($c['correo']); ?></td>
                        <td><?php echo htmlspecialchars($c['telefono'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($c['observacion'] ?? ''); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                data-edit="<?php echo $c['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($c['nombre'], ENT_QUOTES); ?>"
                                data-correo="<?php echo htmlspecialchars($c['correo'], ENT_QUOTES); ?>"
                                data-telefono="<?php echo htmlspecialchars($c['telefono'] ?? '', ENT_QUOTES); ?>"
                                data-observacion="<?php echo htmlspecialchars($c['observacion'] ?? '', ENT_QUOTES); ?>">Editar</button>
                            <a class="btn btn-sm btn-danger"
                                href="index.php?r=clientes&del=<?php echo $c['id']; ?>"
                                onclick="return confirm('¿Está seguro de eliminar al cliente <?php echo htmlspecialchars($c['nombre'], ENT_QUOTES); ?>? Se eliminarán permanentemente el usuario, su perfil y todas sus rutinas asociadas')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($lista) === 0): ?>
                    <tr><td colspan="5" class="text-center">Sin clientes registrados</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        mostrarFooter();
    }
}
