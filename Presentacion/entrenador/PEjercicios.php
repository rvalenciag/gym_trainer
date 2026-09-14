<?php
// PEjercicios: presentación del banco de ejercicios (CU04). Formulario superior + tabla inferior.
// Lee de 2 N desacoplados: ejercicios ← NEjercicio, categorías ← NCategoria (solo lectura).
// Las mutaciones van únicamente a NEjercicio, dueño del CU.
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NEjercicio.php';
require_once __DIR__ . '/../../Negocio/NCategoria.php';
require_once __DIR__ . '/../includes/header.php';

class PEjercicios {
    public function listar() {
        return (new NEjercicio())->listar();
    }

    public function listarCategorias() {
        return (new NCategoria())->listar(); // desacoplado: solo lectura, sin reglas de por medio
    }

    public function guardar($nombre, $idCat, $desc, $tipo, $file, $texto) {
        $res = (new NEjercicio())->guardar($nombre, $idCat, $desc, $tipo, $file, $texto);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=ejercicios');
        exit;
    }

    public function actualizar($id, $nombre, $idCat, $desc, $tipo, $file, $texto) {
        $res = (new NEjercicio())->actualizar($id, $nombre, $idCat, $desc, $tipo, $file, $texto);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=ejercicios');
        exit;
    }

    public function eliminar($id) {
        $res = (new NEjercicio())->eliminar($id);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=ejercicios');
        exit;
    }

    public function mostrar() {
        // Alta o edición (el hidden "id" decide). Ojo: el form usa enctype multipart por el archivo.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = trim($_POST['id'] ?? '');
            $file = $_FILES['archivo'] ?? null;
            $id === ''
                ? $this->guardar($_POST['nombre'] ?? '', $_POST['id_categoria'] ?? 0,
                    $_POST['descripcion'] ?? '', $_POST['tipo'] ?? '', $file, $_POST['texto'] ?? '')
                : $this->actualizar($id, $_POST['nombre'] ?? '', $_POST['id_categoria'] ?? 0,
                    $_POST['descripcion'] ?? '', $_POST['tipo'] ?? '', $file, $_POST['texto'] ?? '');
        }
        if (isset($_GET['del'])) {
            $this->eliminar($_GET['del']);
        }
        $cats = $this->listarCategorias();
        $lista = $this->listar();
        $sinCats = count($cats) === 0;
        mostrarHeader('Gestión de Ejercicios');
        if ($sinCats) {
            echo '<div class="alert alert-warning">Debe crear categorías antes de registrar ejercicios</div>';
        }
        ?>
        <div class="tabla-form">
            <form method="post" action="index.php?r=ejercicios" enctype="multipart/form-data">
                <input type="hidden" name="id" id="f-id" value="">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre del Ejercicio *</label>
                        <input type="text" name="nombre" id="f-nombre" class="form-control" required <?php echo $sinCats ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Categoría Muscular *</label>
                        <select name="id_categoria" id="f-id_categoria" class="form-select" required <?php echo $sinCats ? 'disabled' : ''; ?>>
                            <option value="">Seleccione...</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo de multimedia *</label>
                        <select name="tipo" id="f-tipo" class="form-select" <?php echo $sinCats ? 'disabled' : ''; ?>>
                            <option value="IMAGEN">Subir Archivo / Imagen</option>
                            <option value="VIDEO_URL">Enlace URL de Video</option>
                            <option value="IFRAME_HTML">Código IFRAME_HTML</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Instrucciones / Técnica</label>
                    <textarea name="descripcion" id="f-descripcion" class="form-control" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3" id="w-archivo">
                        <label class="form-label">Archivo (jpg/png/webp/mp4/webm, máx 16 MB)</label>
                        <input type="file" name="archivo" class="form-control" accept=".jpg,.jpeg,.png,.webp,.mp4,.webm">
                    </div>
                    <div class="col-md-6 mb-3" id="w-texto" style="display:none">
                        <label class="form-label" id="lbl-texto">URL del video</label>
                        <input type="text" name="texto" id="f-texto" class="form-control">
                    </div>
                    <div class="col-md-12 mb-3 d-flex gap-2">
                        <button type="submit" id="btn-save" data-orig="Guardar Ejercicio" data-alt="Actualizar Ejercicio" class="btn btn-success" <?php echo $sinCats ? 'disabled' : ''; ?>>Guardar Ejercicio</button>
                        <button type="button" id="btn-cancel" class="btn btn-secondary" style="display:none">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
        <table class="table table-striped table-bordered bg-white">
            <thead class="table-dark">
                <tr><th>Nombre</th><th>Categoría</th><th>Tipo</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $e): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($e['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($e['categoria']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo $e['tipo_multimedia']; ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                data-edit="<?php echo $e['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($e['nombre'], ENT_QUOTES); ?>"
                                data-id_categoria="<?php echo $e['id_categoria']; ?>"
                                data-descripcion="<?php echo htmlspecialchars($e['descripcion'] ?? '', ENT_QUOTES); ?>"
                                data-tipo="<?php echo $e['tipo_multimedia']; ?>"
                                data-texto="<?php echo $e['tipo_multimedia'] === 'IMAGEN' ? '' : htmlspecialchars($e['url'] ?? '', ENT_QUOTES); ?>">Editar</button>
                            <a class="btn btn-sm btn-danger"
                                href="index.php?r=ejercicios&del=<?php echo $e['id']; ?>"
                                onclick="return confirm('¿Eliminar este ejercicio?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($lista) === 0): ?>
                    <tr><td colspan="4" class="text-center">Sin ejercicios registrados</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        mostrarFooter();
    }
}
