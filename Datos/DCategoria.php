<?php
// DCategoria: espejo de la tabla CATEGORIA (CU03).
require_once __DIR__ . '/Conexion.php';

class DCategoria
{
    private $pdo;
    // Atributos espejo de CATEGORIA:
    private $id;
    private $nombre;
    private $descripcion;

    public function __construct()
    {
        $this->pdo = Conexion::get();
    }

    public function listar()
    {
        return $this->pdo->query('SELECT * FROM CATEGORIA ORDER BY id')->fetchAll();
    }

    public function obtenerPorId($id)
    {
        $st = $this->pdo->prepare('SELECT * FROM CATEGORIA WHERE id = ?');
        $st->execute([$id]);
        $f = $st->fetch();
        return $f === false ? null : $f;
    }

    public function crear($nombre, $descripcion)
    {
        $st = $this->pdo->prepare('INSERT INTO CATEGORIA(nombre, descripcion) VALUES(?, ?)');
        $st->execute([$nombre, $descripcion]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar($id, $nombre, $descripcion)
    {
        $st = $this->pdo->prepare('UPDATE CATEGORIA SET nombre = ?, descripcion = ? WHERE id = ?');
        return $st->execute([$nombre, $descripcion, $id]);
    }

    public function eliminar($id)
    {
        $st = $this->pdo->prepare('DELETE FROM CATEGORIA WHERE id = ?');
        return $st->execute([$id]);
    }

    // $excluirId: al editar, la propia categoría no cuenta como duplicada.
    public function existeNombre($nombre, $excluirId = null)
    {
        if ($excluirId === null) {
            $st = $this->pdo->prepare('SELECT COUNT(*) AS n FROM CATEGORIA WHERE nombre = ?');
            $st->execute([$nombre]);
        } else {
            $st = $this->pdo->prepare('SELECT COUNT(*) AS n FROM CATEGORIA WHERE nombre = ? AND id <> ?');
            $st->execute([$nombre, $excluirId]);
        }
        $f = $st->fetch();
        return ((int)$f['n']) > 0;
    }

    // Para el mensaje amigable antes de que la BD frene con 1451 (RESTRICT).
    public function tieneEjercicios($id)
    {
        $st = $this->pdo->prepare('SELECT COUNT(*) AS n FROM EJERCICIO WHERE id_categoria = ?');
        $st->execute([$id]);
        $f = $st->fetch();
        return ((int)$f['n']) > 0;
    }
}
