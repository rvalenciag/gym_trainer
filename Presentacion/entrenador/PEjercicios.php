<?php
// PEjercicios: presentación del banco de ejercicios (CU04). Formulario superior + tabla inferior.
// Lee de 2 N desacoplados: ejercicios ← NEjercicio, categorías ← NCategoria (solo lectura).
// Las mutaciones van únicamente a NEjercicio, dueño del CU.
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NEjercicio.php';
require_once __DIR__ . '/../../Negocio/NCategoria.php';
require_once __DIR__ . '/../includes/header.php';

class PEjercicios
{
    private int $idEjercicio = 0;
    private string $nombre = '';
    private int $idCategoria = 0;
    private ?string $descripcion = null;
    private string $tipoMultimedia = 'IMAGEN';
    private ?string $textoMultimedia = null;
    private ?array $archivo = null;
    private array $categorias = [];
    private array $ejercicios = [];
    private bool $sinCategorias = false;

    private function listar()
    {
        return (new NEjercicio())->listar();
    }

    private function listarCategorias()
    {
        return (new NCategoria())->listar(); // desacoplado: solo lectura, sin reglas de por medio
    }

    public function guardar()
    {
        $res = (new NEjercicio())->guardar(
            $this->nombre,
            $this->idCategoria,
            $this->descripcion ?? '',
            $this->tipoMultimedia,
            $this->archivo,
            $this->textoMultimedia ?? ''
        );
        $this->archivo = null;

        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=ejercicios');
        exit;
    }

    public function actualizar()
    {
        $res = (new NEjercicio())->actualizar(
            $this->idEjercicio,
            $this->nombre,
            $this->idCategoria,
            $this->descripcion ?? '',
            $this->tipoMultimedia,
            $this->archivo,
            $this->textoMultimedia ?? ''
        );
        $this->archivo = null;

        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=ejercicios');
        exit;
    }

    public function eliminar()
    {
        $res = (new NEjercicio())->eliminar($this->idEjercicio);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=ejercicios');
        exit;
    }

    public function mostrar()
    {
        // Alta o edición (el hidden "id" decide). Ojo: el form usa enctype multipart por el archivo.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->idEjercicio = (int) ($_POST['id'] ?? 0);
            $this->nombre = $_POST['nombre'] ?? '';
            $this->idCategoria = (int) ($_POST['id_categoria'] ?? 0);
            $this->descripcion = $_POST['descripcion'] ?? null;
            $this->tipoMultimedia = $_POST['tipo'] ?? 'IMAGEN';
            $this->archivo = $_FILES['archivo'] ?? null;
            $this->textoMultimedia = $_POST['texto'] ?? null;

            $this->idEjercicio === 0
                ? $this->guardar()
                : $this->actualizar();
        }
        if (isset($_GET['del'])) {
            $this->idEjercicio = (int) $_GET['del'];
            $this->eliminar();
        }
        $this->categorias = $this->listarCategorias();
        $this->ejercicios = $this->listar();
        $this->sinCategorias = count($this->categorias) === 0;
        mostrarHeader('Gestión de Ejercicios');
        if ($this->sinCategorias) {
            echo '<div class="alert alert-warning">Debe crear categorías antes de registrar ejercicios</div>';
        }
?>
        <section class="gym-card gym-form-card">
            <div class="gym-card-heading">
                <span class="gym-heading-icon"><?php echo iconoGym('exercise'); ?></span>
                <div>
                    <h2>Registrar ejercicio</h2>
                    <p>Completa la información técnica y el recurso multimedia del ejercicio</p>
                </div>
            </div>
            <form method="post" action="index.php?r=ejercicios" enctype="multipart/form-data">
                <input type="hidden" name="id" id="f-id" value="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" id="f-nombre" class="form-control" placeholder="Ej. Press de banca" required <?php echo $this->sinCategorias ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Categoría Muscular *</label>
                        <select name="id_categoria" id="f-id_categoria" class="form-select" required <?php echo $this->sinCategorias ? 'disabled' : ''; ?>>
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($this->categorias as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Instrucciones / Técnica</label>
                    <textarea name="descripcion" id="f-descripcion" class="form-control" rows="3" placeholder="Instrucciones para realizar el ejercicio, técnica y recomendaciones"></textarea>
                </div>
                <div class="gym-media-section">
                    <div class="row align-items-end">
                        <div class="col-lg-6 mb-3">
                            <label class="form-label">Tipo de multimedia</label>
                            <select name="tipo" id="f-tipo" class="visually-hidden" <?php echo $this->sinCategorias ? 'disabled' : ''; ?>>
                                <option value="IMAGEN">Imagen</option>
                                <option value="VIDEO_URL">URL de video</option>
                                <option value="IFRAME_HTML">Iframe HTML</option>
                            </select>
                            <div class="gym-media-tabs" role="group" aria-label="Tipo de multimedia">
                                <button type="button" class="gym-media-tab active" data-media-type="IMAGEN" <?php echo $this->sinCategorias ? 'disabled' : ''; ?>><?php echo iconoGym('image'); ?>Imagen</button>
                                <button type="button" class="gym-media-tab" data-media-type="VIDEO_URL" <?php echo $this->sinCategorias ? 'disabled' : ''; ?>>▣ URL de video</button>
                                <button type="button" class="gym-media-tab" data-media-type="IFRAME_HTML" <?php echo $this->sinCategorias ? 'disabled' : ''; ?>>&lt;/&gt; Iframe HTML</button>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label class="form-label">Recurso multimedia</label>
                            <div id="w-archivo" class="gym-upload-box">
                                <?php echo iconoGym('image'); ?>
                                <div><strong>Seleccionar imagen del ejercicio</strong><span>JPG, PNG, WEBP, MP4 o WEBM · Máx. 16 MB</span></div>
                                <input type="file" name="archivo" class="form-control" accept=".jpg,.jpeg,.png,.webp,.mp4,.webm">
                            </div>
                            <div id="w-texto" style="display:none">
                                <label class="visually-hidden" id="lbl-texto">URL del video</label>
                                <input type="text" name="texto" id="f-texto" class="form-control" placeholder="https://...">
                            </div>
                            <div id="media-actual" class="mt-3" style="display:none" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-3 pt-2">
                    <button type="submit" id="btn-save" data-orig="Guardar" data-alt="Actualizar" class="gym-primary-btn" <?php echo $this->sinCategorias ? 'disabled' : ''; ?>><?php echo iconoGym('save'); ?><span>Guardar</span></button>
                    <button type="button" id="btn-cancel" class="gym-secondary-btn"><?php echo iconoGym('clear'); ?><span>Limpiar</span></button>
                </div>
            </form>
        </section>
        <section class="gym-card gym-table-card">
            <div class="gym-card-heading"><span class="gym-heading-icon"><?php echo iconoGym('category'); ?></span>
                <div>
                    <h2>Banco de ejercicios</h2>
                    <p>Consulta, edita o elimina los ejercicios existentes</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table gym-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ejercicio</th>
                            <th>Categoría</th>
                            <th>Multimedia</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->ejercicios as $e): ?>
                            <tr>
                                <td><?php echo str_pad((string)$e['id'], 2, '0', STR_PAD_LEFT); ?></td>
                                <td class="gym-strong-cell"><?php echo htmlspecialchars($e['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($e['categoria']); ?></td>
                                <td><span class="gym-media-label"><?php echo $e['tipo_multimedia'] === 'IMAGEN' ? iconoGym('image') : '▣'; ?><?php echo htmlspecialchars($e['tipo_multimedia'] === 'VIDEO_URL' ? 'URL de video' : ($e['tipo_multimedia'] === 'IFRAME_HTML' ? 'Iframe HTML' : 'Imagen')); ?></span></td>
                                <td class="gym-actions">
                                    <button class="gym-icon-btn gym-edit-btn" aria-label="Editar ejercicio"
                                        data-edit="<?php echo $e['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($e['nombre'], ENT_QUOTES); ?>"
                                        data-id_categoria="<?php echo $e['id_categoria']; ?>"
                                         data-descripcion="<?php echo htmlspecialchars($e['descripcion'] ?? '', ENT_QUOTES); ?>"
                                         data-tipo="<?php echo $e['tipo_multimedia']; ?>"
                                         data-url="<?php echo htmlspecialchars($e['url'] ?? '', ENT_QUOTES); ?>"
                                         data-texto="<?php echo $e['tipo_multimedia'] === 'IMAGEN' ? '' : htmlspecialchars($e['url'] ?? '', ENT_QUOTES); ?>"><?php echo iconoGym('edit'); ?></button>
                                    <a class="gym-icon-btn gym-delete-btn" aria-label="Eliminar ejercicio"
                                        href="index.php?r=ejercicios&del=<?php echo $e['id']; ?>"
                                        onclick="return confirm('¿Eliminar este ejercicio?')"><?php echo iconoGym('delete'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($this->ejercicios) === 0): ?>
                            <tr>
                                <td colspan="5" class="text-center">Sin ejercicios registrados</td>
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
