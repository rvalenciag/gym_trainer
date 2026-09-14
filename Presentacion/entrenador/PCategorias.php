<?php
// PCategorias: presentación de Gestión de Categorías (CU03). Formulario superior + tabla inferior.
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NCategoria.php';
require_once __DIR__ . '/../includes/header.php';

class PCategorias {
    public function listar() {
        return (new NCategoria())->listar();
    }

    public function guardar($nombre, $descripcion) {
        $res = (new NCategoria())->guardar($nombre, $descripcion);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=categorias');
        exit;
    }

    public function actualizar($id, $nombre, $descripcion) {
        $res = (new NCategoria())->actualizar($id, $nombre, $descripcion);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=categorias');
        exit;
    }

    public function eliminar($id) {
        $res = (new NCategoria())->eliminar($id);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=categorias');
        exit;
    }

    public function mostrar() {
        // Alta o edición (el hidden "id" decide).
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = trim($_POST['id'] ?? '');
            $id === ''
                ? $this->guardar($_POST['nombre'] ?? '', $_POST['descripcion'] ?? '')
                : $this->actualizar($id, $_POST['nombre'] ?? '', $_POST['descripcion'] ?? '');
        }
        // Baja con confirmación previa en el navegador.
        if (isset($_GET['del'])) {
            $this->eliminar($_GET['del']);
        }
        $lista = $this->listar();
        mostrarHeader('Gestión de Categorías Musculares');
        ?>
        <div class="tabla-form">
            <form method="post" action="index.php?r=categorias">
                <input type="hidden" name="id" id="f-id" value="">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre de la Categoría *</label>
                        <input type="text" name="nombre" id="f-nombre" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Descripción (opcional)</label>
                        <input type="text" name="descripcion" id="f-descripcion" class="form-control">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end gap-2">
                        <button type="submit" id="btn-save" data-orig="Guardar Categoría" data-alt="Actualizar Categoría" class="btn btn-success">Guardar Categoría</button>
                        <button type="button" id="btn-cancel" class="btn btn-secondary" style="display:none">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
        <table class="table table-striped table-bordered bg-white">
            <thead class="table-dark">
                <tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $c): ?>
                    <tr>
                        <td><?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($c['descripcion'] ?? ''); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                data-edit="<?php echo $c['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($c['nombre'], ENT_QUOTES); ?>"
                                data-descripcion="<?php echo htmlspecialchars($c['descripcion'] ?? '', ENT_QUOTES); ?>">Editar</button>
                            <a class="btn btn-sm btn-danger"
                                href="index.php?r=categorias&del=<?php echo $c['id']; ?>"
                                onclick="return confirm('¿Desea eliminar la categoría <?php echo htmlspecialchars($c['nombre'], ENT_QUOTES); ?>?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($lista) === 0): ?>
                    <tr><td colspan="4" class="text-center">Sin categorías registradas</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        mostrarFooter();
    }
}
