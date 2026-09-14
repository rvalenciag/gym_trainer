<?php
// PDetalleEjercicio: presentación de la guía completa de un ejercicio (CU07, solo lectura).
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NEjercicio.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/media.php';

class PDetalleEjercicio {
    public function verEjercicio($id) {
        return (new NEjercicio())->obtener($id);
    }

    public function mostrar() {
        $e = $this->verEjercicio($_GET['id'] ?? 0);
        if ($e === null) {
            flash('warning', 'El ejercicio no existe');
            header('Location: index.php?r=mi-rutina');
            exit;
        }
        mostrarHeader($e['nombre']);
        mostrarGuia($e);
        echo '<div class="mt-3">';
        echo '    <a class="btn btn-secondary" href="index.php?r=mi-rutina">Volver a Mi Rutina</a>';
        echo '</div>';
        mostrarFooter();
    }
}
