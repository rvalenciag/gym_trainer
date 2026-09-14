<?php
// NCategoria: reglas del catálogo de grupos musculares (CU03).
require_once __DIR__ . '/../Datos/DCategoria.php';

class NCategoria {
    public function listar() {
        return (new DCategoria())->listar();
    }

    public function guardar($nombre, $descripcion) {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return ['ok' => false, 'msg' => 'El nombre de la categoría es obligatorio'];
        }
        $d = new DCategoria();
        if ($d->existeNombre($nombre)) {
            return ['ok' => false, 'msg' => 'Ya existe una categoría registrada con ese nombre'];
        }
        $id = $d->crear($nombre, trim($descripcion) === '' ? null : trim($descripcion));
        return $id > 0
            ? ['ok' => true, 'msg' => 'Categoría registrada exitosamente']
            : ['ok' => false, 'msg' => 'No se pudo registrar la categoría'];
    }

    public function actualizar($id, $nombre, $descripcion) {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return ['ok' => false, 'msg' => 'El nombre de la categoría es obligatorio'];
        }
        $d = new DCategoria();
        if ($d->existeNombre($nombre, (int)$id)) {
            return ['ok' => false, 'msg' => 'Ya existe una categoría registrada con ese nombre'];
        }
        $ok = $d->actualizar((int)$id, $nombre, trim($descripcion) === '' ? null : trim($descripcion));
        return ['ok' => $ok, 'msg' => $ok ? 'Categoría actualizada correctamente' : 'No se pudo actualizar'];
    }

    public function eliminar($id) {
        $d = new DCategoria();
        if ($d->tieneEjercicios((int)$id)) {
            return ['ok' => false, 'msg' => 'No se puede eliminar la categoría porque tiene ejercicios vinculados'];
        }
        $ok = $d->eliminar((int)$id);
        return ['ok' => $ok, 'msg' => $ok ? 'Categoría eliminada' : 'No se pudo eliminar'];
    }
}
