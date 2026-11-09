<?php
// DCategoria: acceso a la tabla CATEGORIA.
require_once __DIR__ . '/Conexion.php';

class DCategoria
{
    private $pdo;
    private $id;
    private $nombre;
    private $descripcion;

    public function __construct($datos = [])
    {
        $this->pdo = Conexion::get();
        $this->id = $datos['id'] ?? null;
        $this->nombre = $datos['nombre'] ?? null;
        $this->descripcion = $datos['descripcion'] ?? null;
    }

    public function listar()
    {
        return $this->pdo->query(
            'SELECT id, nombre, descripcion FROM CATEGORIA ORDER BY id'
        )->fetchAll();
    }

    public function obtenerPorId()
    {
        $st = $this->pdo->prepare(
            'SELECT id, nombre, descripcion FROM CATEGORIA WHERE id = ?'
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
            'INSERT INTO CATEGORIA (nombre, descripcion) VALUES (?, ?)'
        );
        $st->execute([$this->nombre, $this->descripcion]);

        $this->id = (int) $this->pdo->lastInsertId();
        return $this->id;
    }

    public function actualizar()
    {
        $st = $this->pdo->prepare(
            'UPDATE CATEGORIA SET nombre = ?, descripcion = ? WHERE id = ?'
        );

        return $st->execute([
            $this->nombre,
            $this->descripcion,
            (int) $this->id,
        ]);
    }

    public function eliminar()
    {
        $st = $this->pdo->prepare('DELETE FROM CATEGORIA WHERE id = ?');
        return $st->execute([(int) $this->id]);
    }

    public function existeNombre()
    {
        if ($this->id === null) {
            $st = $this->pdo->prepare(
                'SELECT COUNT(*) AS n FROM CATEGORIA WHERE nombre = ?'
            );
            $st->execute([$this->nombre]);
        } else {
            $st = $this->pdo->prepare(
                'SELECT COUNT(*) AS n FROM CATEGORIA WHERE nombre = ? AND id <> ?'
            );
            $st->execute([$this->nombre, (int) $this->id]);
        }

        $fila = $st->fetch();
        return (int) $fila['n'] > 0;
    }

    public function tieneEjercicios()
    {
        $st = $this->pdo->prepare(
            'SELECT COUNT(*) AS n FROM EJERCICIO WHERE id_categoria = ?'
        );
        $st->execute([(int) $this->id]);
        $fila = $st->fetch();

        return (int) $fila['n'] > 0;
    }

    private function cargarDatos($fila)
    {
        $this->id = (int) $fila['id'];
        $this->nombre = $fila['nombre'];
        $this->descripcion = $fila['descripcion'];
    }

    private function aArray()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
        ];
    }
}
