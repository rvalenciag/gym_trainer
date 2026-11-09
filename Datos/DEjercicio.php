<?php
// DEjercicio: acceso a la tabla EJERCICIO.
require_once __DIR__ . '/Conexion.php';

class DEjercicio
{
    private $pdo;
    private $id;
    private $nombre;
    private $descripcion;
    private $tipoMultimedia;
    private $url;
    private $idCategoria;

    public function __construct($datos = [])
    {
        $this->pdo = Conexion::get();
        $this->id = $datos['id'] ?? null;
        $this->nombre = $datos['nombre'] ?? null;
        $this->descripcion = $datos['descripcion'] ?? null;
        $this->tipoMultimedia = $datos['tipo_multimedia'] ?? null;
        $this->url = $datos['url'] ?? null;
        $this->idCategoria = $datos['id_categoria'] ?? null;
    }

    public function listarConCategoria()
    {
        return $this->pdo->query(
            'SELECT E.*, C.nombre AS categoria
             FROM EJERCICIO E
             INNER JOIN CATEGORIA C ON C.id = E.id_categoria
             ORDER BY E.id'
        )->fetchAll();
    }

    public function obtenerPorId()
    {
        $st = $this->pdo->prepare(
            'SELECT id, nombre, descripcion, tipo_multimedia, url, id_categoria
             FROM EJERCICIO
             WHERE id = ?'
        );
        $st->execute([(int) $this->id]);
        $fila = $st->fetch();

        if ($fila === false) {
            return null;
        }

        $this->cargarDatos($fila);
        return $this->aArray();
    }

    public function crear()
    {
        $st = $this->pdo->prepare(
            'INSERT INTO EJERCICIO
                (nombre, descripcion, tipo_multimedia, url, id_categoria)
             VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([
            $this->nombre,
            $this->descripcion,
            $this->tipoMultimedia,
            $this->url,
            (int) $this->idCategoria,
        ]);

        $this->id = (int) $this->pdo->lastInsertId();
        return $this->id;
    }

    public function actualizar()
    {
        $st = $this->pdo->prepare(
            'UPDATE EJERCICIO
             SET nombre = ?,
                 descripcion = ?,
                 tipo_multimedia = ?,
                 url = ?,
                 id_categoria = ?
             WHERE id = ?'
        );

        return $st->execute([
            $this->nombre,
            $this->descripcion,
            $this->tipoMultimedia,
            $this->url,
            (int) $this->idCategoria,
            (int) $this->id,
        ]);
    }

    public function eliminar()
    {
        $st = $this->pdo->prepare('DELETE FROM EJERCICIO WHERE id = ?');
        return $st->execute([(int) $this->id]);
    }

    private function cargarDatos($fila)
    {
        $this->id = (int) $fila['id'];
        $this->nombre = $fila['nombre'];
        $this->descripcion = $fila['descripcion'];
        $this->tipoMultimedia = $fila['tipo_multimedia'];
        $this->url = $fila['url'];
        $this->idCategoria = (int) $fila['id_categoria'];
    }

    private function aArray()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo_multimedia' => $this->tipoMultimedia,
            'url' => $this->url,
            'id_categoria' => $this->idCategoria,
        ];
    }
}
