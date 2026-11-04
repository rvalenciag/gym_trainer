<?php
// DRutina: cabecera de los planes semanales. El detalle vive en DDetalleRutina.
require_once __DIR__ . '/Conexion.php';

class DRutina
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::get();
    }

    public function listarConCliente()
    {
        return $this->pdo->query(
            'SELECT R.*, U.nombre AS cliente FROM RUTINA R ' .
                'JOIN CLIENTE C ON C.id_usuario = R.id_cliente ' .
                'JOIN USUARIO U ON U.id = C.id_usuario ORDER BY R.id DESC'
        )->fetchAll();
    }

    public function listarPorCliente($idCliente)
    {
        $st = $this->pdo->prepare('SELECT * FROM RUTINA WHERE id_cliente = ? ORDER BY id DESC');
        $st->execute([(int)$idCliente]);
        return $st->fetchAll();
    }

    public function obtenerCabecera($id)
    {
        $st = $this->pdo->prepare('SELECT * FROM RUTINA WHERE id = ?');
        $st->execute([(int)$id]);
        $f = $st->fetch();
        return $f === false ? null : $f;
    }

    public function obtenerDeCliente($id, $idCliente)
    {
        $st = $this->pdo->prepare('SELECT * FROM RUTINA WHERE id = ? AND id_cliente = ?');
        $st->execute([(int)$id, (int)$idCliente]);
        $f = $st->fetch();
        return $f === false ? null : $f;
    }

    public function iniciarTransaccion()
    {
        $this->pdo->beginTransaction();
    }
    public function confirmar()
    {
        $this->pdo->commit();
    }
    public function revertir()
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function crearCabecera($cab)
    {
        error_log($cab['id_instructor']);
        error_log($cab['id_cliente']);
        error_log($cab['nombre_plan']);
        $st = $this->pdo->prepare(
            'INSERT INTO RUTINA(id_instructor, id_cliente, nombre_plan) VALUES(?, ?, ?)'
        );
        error_log("PASO CONSULTA");
        $st->execute([$cab['id_instructor'], $cab['id_cliente'], $cab['nombre_plan']]);
        error_log("PASO execute");
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizarCabecera($id, $cab)
    {
        $st = $this->pdo->prepare('UPDATE RUTINA SET id_cliente = ?, nombre_plan = ? WHERE id = ?');
        return $st->execute([$cab['id_cliente'], $cab['nombre_plan'], (int)$id]);
    }

    public function eliminar($id)
    {
        $st = $this->pdo->prepare('DELETE FROM RUTINA WHERE id = ?');
        return $st->execute([(int)$id]);
    }
}
