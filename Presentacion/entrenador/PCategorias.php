<?php
// PCategorias: presentación de Gestión de Categorías (CU03). Formulario superior + tabla inferior.
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NCategoria.php';
require_once __DIR__ . '/../includes/header.php';

class PCategorias
{
    private int $idCategoria = 0;
    private string $nombre = '';
    private ?string $descripcion = null;
    private array $categorias = [];

    public function listar()
    {
        return (new NCategoria())->listar();
    }

    public function guardar()
    {
        $res = (new NCategoria())->guardar($this->nombre, $this->descripcion ?? '');
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=categorias');
        exit;
    }

    public function actualizar()
    {
        $res = (new NCategoria())->actualizar(
            $this->idCategoria,
            $this->nombre,
            $this->descripcion ?? ''
        );
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=categorias');
        exit;
    }

    public function eliminar()
    {
        $res = (new NCategoria())->eliminar($this->idCategoria);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=categorias');
        exit;
    }

    public function mostrar()
    {
        // Alta o edición (el hidden "id" decide).
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->idCategoria = (int) ($_POST['id'] ?? 0);
            $this->nombre = $_POST['nombre'] ?? '';
            $this->descripcion = $_POST['descripcion'] ?? null;

            $this->idCategoria === 0
                ? $this->guardar()
                : $this->actualizar();
        }
        // Baja con confirmación previa en el navegador.
        if (isset($_GET['del'])) {
            $this->idCategoria = (int) $_GET['del'];
            $this->eliminar();
        }
        $this->categorias = $this->listar();
        mostrarHeader('Gestión de Categorías');
?>
        <section class="gym-card gym-form-card">
            <div class="gym-card-heading">
                <span class="gym-heading-icon"><?php echo iconoGym('category'); ?></span>
                <div>
                    <h2>Formulario de categoría</h2>
                </div>
            </div>
            <form method="post" action="index.php?r=categorias">
                <input type="hidden" name="id" id="f-id" value="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" id="f-nombre" class="form-control" placeholder="Ej. Pecho" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" id="f-descripcion" class="form-control" placeholder="Ej. Ejercicios enfocados en el músculo pectoral">
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-3 pt-2">
                        <button type="submit" id="btn-save" data-orig="Guardar" data-alt="Actualizar" class="gym-primary-btn">
                            <?php echo iconoGym('save'); ?><span>Guardar</span>
                        </button>
                        <button type="button" id="btn-cancel" class="gym-secondary-btn">
                            <?php echo iconoGym('clear'); ?><span>Limpiar</span>
                        </button>
                    </div>
                </div>
            </form>
        </section>
        <section class="gym-card gym-table-card">
            <div class="gym-card-heading">
                <span class="gym-heading-icon"><?php echo iconoGym('category'); ?></span>
                <div>
                    <h2>Categorías registradas</h2>
                    <p>Selecciona editar para cargar sus datos en el formulario</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table gym-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->categorias as $c): ?>
                            <tr>
                                <td><?php echo str_pad((string)$c['id'], 2, '0', STR_PAD_LEFT); ?></td>
                                <td class="gym-strong-cell"><?php echo htmlspecialchars($c['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($c['descripcion'] ?? ''); ?></td>
                                <td class="gym-actions">
                                    <button class="gym-icon-btn gym-edit-btn" aria-label="Editar categoría"
                                        data-edit="<?php echo $c['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($c['nombre'], ENT_QUOTES); ?>"
                                        data-descripcion="<?php echo htmlspecialchars($c['descripcion'] ?? '', ENT_QUOTES); ?>"><?php echo iconoGym('edit'); ?></button>
                                    <a class="gym-icon-btn gym-delete-btn" aria-label="Eliminar categoría"
                                        href="index.php?r=categorias&del=<?php echo $c['id']; ?>"
                                        onclick="return confirm('¿Desea eliminar la categoría <?php echo htmlspecialchars($c['nombre'], ENT_QUOTES); ?>?')"><?php echo iconoGym('delete'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($this->categorias) === 0): ?>
                            <tr>
                                <td colspan="4" class="text-center">Sin categorías registradas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
<?php
        mostrarFooter();
    }
}
