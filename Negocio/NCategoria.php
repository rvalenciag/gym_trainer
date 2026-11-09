<?php
// NCategoria: reglas del catálogo de grupos musculares.
require_once __DIR__ . '/../Datos/DCategoria.php';

class NCategoria
{
    public function listar()
    {
        return (new DCategoria())->listar();
    }

    public function guardar($nombre, $descripcion)
    {
        $nombre = trim($nombre);
        $descripcion = trim($descripcion) === '' ? null : trim($descripcion);

        if ($nombre === '') {
            return [
                'ok' => false,
                'msg' => 'El nombre de la categoría es obligatorio',
            ];
        }

        $categoria = new DCategoria([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
        ]);

        if ($categoria->existeNombre()) {
            return [
                'ok' => false,
                'msg' => 'Ya existe una categoría registrada con ese nombre',
            ];
        }

        $id = $categoria->crear();

        return $id > 0
            ? ['ok' => true, 'msg' => 'Categoría registrada exitosamente']
            : ['ok' => false, 'msg' => 'No se pudo registrar la categoría'];
    }

    public function actualizar($id, $nombre, $descripcion)
    {
        $nombre = trim($nombre);
        $descripcion = trim($descripcion) === '' ? null : trim($descripcion);

        if ($nombre === '') {
            return [
                'ok' => false,
                'msg' => 'El nombre de la categoría es obligatorio',
            ];
        }

        $categoria = new DCategoria([
            'id' => (int) $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
        ]);

        if ($categoria->existeNombre()) {
            return [
                'ok' => false,
                'msg' => 'Ya existe una categoría registrada con ese nombre',
            ];
        }

        $ok = $categoria->actualizar();

        return [
            'ok' => $ok,
            'msg' => $ok ? 'Categoría actualizada correctamente' : 'No se pudo actualizar',
        ];
    }

    public function eliminar($id)
    {
        $categoria = new DCategoria([
            'id' => (int) $id,
        ]);

        if ($categoria->tieneEjercicios()) {
            return [
                'ok' => false,
                'msg' => 'No se puede eliminar la categoría porque tiene ejercicios vinculados',
            ];
        }

        $ok = $categoria->eliminar();

        return [
            'ok' => $ok,
            'msg' => $ok ? 'Categoría eliminada' : 'No se pudo eliminar',
        ];
    }
}
