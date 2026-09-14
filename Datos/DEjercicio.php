<?php
// DEjercicio: espejo de la tabla EJERCICIO (CU04).
require_once __DIR__ . '/Conexion.php';

class DEjercicio {
    private $pdo;
    // Atributos espejo de EJERCICIO:
    private $id;
    private $nombre;
    private $descripcion;
    private $tipo_multimedia;
    private $url;
    private $id_categoria;

    public function __construct() {
        $this->pdo = Conexion::get();
    }

    public function listarConCategoria() {
        return $this->pdo->query(
            'SELECT E.*, C.nombre AS categoria FROM EJERCICIO E ' .
            'JOIN CATEGORIA C ON C.id = E.id_categoria ORDER BY E.id'
        )->fetchAll();
    }

    public function obtenerPorId($id) {
        $st = $this->pdo->prepare('SELECT * FROM EJERCICIO WHERE id = ?');
        $st->execute([$id]);
        $f = $st->fetch();
        return $f === false ? null : $f;
    }

    public function crear($nombre, $descripcion, $tipo, $url, $idCategoria) {
        $st = $this->pdo->prepare(
            'INSERT INTO EJERCICIO(nombre, descripcion, tipo_multimedia, url, id_categoria) ' .
            'VALUES(?, ?, ?, ?, ?)'
        );
        $st->execute([$nombre, $descripcion, $tipo, $url, $idCategoria]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar($id, $nombre, $descripcion, $tipo, $url, $idCategoria) {
        $st = $this->pdo->prepare(
            'UPDATE EJERCICIO SET nombre = ?, descripcion = ?, tipo_multimedia = ?, url = ?, ' .
            'id_categoria = ? WHERE id = ?'
        );
        return $st->execute([$nombre, $descripcion, $tipo, $url, $idCategoria, $id]);
    }

    public function eliminar($id) {
        $st = $this->pdo->prepare('DELETE FROM EJERCICIO WHERE id = ?');
        return $st->execute([$id]);
    }
}
