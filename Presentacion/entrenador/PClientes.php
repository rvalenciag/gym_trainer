<?php
// PClientes: presentación de Gestión de Clientes (CU05). Formulario superior + tabla inferior.
// Al editar se oculta la contraseña (JS): la clave solo se define al registrar (guardar).
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NCliente.php';
require_once __DIR__ . '/../includes/header.php';

class PClientes
{
    private int $idCliente = 0;
    private string $nombre = '';
    private string $correo = '';
    private ?string $telefono = null;
    private ?string $contrasena = null;
    private ?string $observacion = null;
    private array $clientes = [];

    public function listar()
    {
        return (new NCliente())->listar();
    }

    public function guardar()
    {
        $res = (new NCliente())->registrar(
            $this->nombre,
            $this->correo,
            $this->telefono ?? '',
            $this->contrasena ?? '',
            $this->observacion ?? ''
        );
        $this->contrasena = null;

        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=clientes');
        exit;
    }

    public function actualizar()
    {
        $res = (new NCliente())->actualizar(
            $this->idCliente,
            $this->nombre,
            $this->correo,
            $this->telefono ?? '',
            $this->observacion ?? ''
        );
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=clientes');
        exit;
    }

    public function eliminar()
    {
        $res = (new NCliente())->eliminar($this->idCliente);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=clientes');
        exit;
    }

    public function mostrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->idCliente = (int) ($_POST['id'] ?? 0);
            $this->nombre = $_POST['nombre'] ?? '';
            $this->correo = $_POST['correo'] ?? '';
            $this->telefono = $_POST['telefono'] ?? null;
            $this->contrasena = $_POST['contrasena'] ?? null;
            $this->observacion = $_POST['observacion'] ?? null;

            $this->idCliente === 0
                ? $this->guardar()
                : $this->actualizar();
        }
        if (isset($_GET['del'])) {
            $this->idCliente = (int) $_GET['del'];
            $this->eliminar();
        }
        $this->clientes = $this->listar();
        mostrarHeader('Gestión de Clientes');
?>
        <section class="gym-card gym-form-card">
            <div class="gym-card-heading">
                <span class="gym-heading-icon"><?php echo iconoGym('clients'); ?></span>
                <div>
                    <h2>Registrar cliente</h2>
                    <p>Completa los datos personales y observaciones del cliente</p>
                </div>
            </div>
            <form method="post" action="index.php?r=clientes">
                <input type="hidden" name="id" id="f-id" value="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre" id="f-nombre" class="form-control" placeholder="Ej. Carlos Mendoza" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo Electrónico *</label>
                        <input type="email" name="correo" id="f-correo" class="form-control" placeholder="Ej. carlos@correo.com" required>
                    </div>
                    <div class="col-md-6 mb-3" id="f-pass-wrap">
                        <label class="form-label">Contraseña * <span class="gym-muted-label">(solo al registrar)</span></label>
                        <div class="gym-inline-password">
                            <input type="password" name="contrasena" id="f-contrasena" class="form-control" placeholder="Contraseña inicial">
                            <button type="button" class="gym-password-toggle" data-password-toggle="f-contrasena" aria-label="Mostrar contraseña">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                                    <circle cx="12" cy="12" r="2.5" />
                                    <path d="M4 4 20 20" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="f-telefono" class="form-control" placeholder="Ej. 70012345">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Observaciones / Ficha Médica</label>
                        <textarea name="observacion" id="f-observacion" class="form-control" rows="3" placeholder="Notas médicas y observaciones generales del cliente"></textarea>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-3 pt-2">
                        <button type="submit" id="btn-save" data-orig="Guardar" data-alt="Actualizar" class="gym-primary-btn"><?php echo iconoGym('save'); ?><span>Guardar</span></button>
                        <button type="button" id="btn-cancel" class="gym-secondary-btn"><?php echo iconoGym('clear'); ?><span>Limpiar</span></button>
                    </div>
                </div>
            </form>
        </section>
        <section class="gym-card gym-table-card">
            <div class="gym-card-heading"><span class="gym-heading-icon"><?php echo iconoGym('category'); ?></span>
                <div>
                    <h2>Clientes registrados</h2>
                    <p>Selecciona editar para cargar los datos del cliente en el formulario</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table gym-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->clientes as $c): ?>
                            <tr>
                                <td><?php echo str_pad((string)$c['id'], 2, '0', STR_PAD_LEFT); ?></td>
                                <td class="gym-strong-cell"><?php echo htmlspecialchars($c['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($c['correo']); ?></td>
                                <td><?php echo htmlspecialchars($c['telefono'] ?? ''); ?></td>
                                <td class="gym-actions">
                                    <button class="gym-icon-btn gym-edit-btn" aria-label="Editar cliente"
                                        data-edit="<?php echo $c['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($c['nombre'], ENT_QUOTES); ?>"
                                        data-correo="<?php echo htmlspecialchars($c['correo'], ENT_QUOTES); ?>"
                                        data-telefono="<?php echo htmlspecialchars($c['telefono'] ?? '', ENT_QUOTES); ?>"
                                        data-observacion="<?php echo htmlspecialchars($c['observacion'] ?? '', ENT_QUOTES); ?>"><?php echo iconoGym('edit'); ?></button>
                                    <a class="gym-icon-btn gym-delete-btn" aria-label="Eliminar cliente"
                                        href="index.php?r=clientes&del=<?php echo $c['id']; ?>"
                                        onclick="return confirm('¿Está seguro de eliminar al cliente <?php echo htmlspecialchars($c['nombre'], ENT_QUOTES); ?>? Se eliminarán permanentemente el usuario, su perfil y todas sus rutinas asociadas')"><?php echo iconoGym('delete'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($this->clientes) === 0): ?>
                            <tr>
                                <td colspan="5" class="text-center">Sin clientes registrados</td>
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
