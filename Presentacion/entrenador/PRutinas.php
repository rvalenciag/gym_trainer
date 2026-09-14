<?php
// PRutinas: presentación de la tabla de rutinas asignadas (CU06 lista).
// El diseño (cabecera + constructor) vive en PCrearRutina; aquí solo lista + eliminar.
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NRutina.php';
require_once __DIR__ . '/../includes/header.php';

class PRutinas {
    public function listar() {
        return (new NRutina())->listar();
    }

    public function eliminar($id) {
        $res = (new NRutina())->eliminar($id);
        flash($res['ok'] ? 'success' : 'danger', $res['msg']);
        header('Location: index.php?r=rutinas');
        exit;
    }

    public function mostrar() {
        if (isset($_GET['del'])) {
            $this->eliminar($_GET['del']);
        }
        $lista = $this->listar();
        mostrarHeader('Gestión de Rutinas');
        ?>
        <div class="mb-3">
            <a class="btn btn-success" href="index.php?r=crear_rutina">Crear Nueva Rutina</a>
        </div>
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
                            <a class="btn btn-sm btn-primary" href="index.php?r=crear_rutina&id=<?php echo $r['id']; ?>">Editar</a>
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
        <?php
        mostrarFooter();
    }
}
